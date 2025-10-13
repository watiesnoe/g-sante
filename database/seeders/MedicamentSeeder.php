<?php

namespace Database\Seeders;

use App\Models\Etablissement;
use App\Models\Famille;
use App\Models\Medicament;
use App\Models\User;
use App\Services\Referentiel\RapprochementStock;
use App\Services\StockService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Catalogue de médicaments de démonstration (database/data/medicaments.json :
 * 1337 produits, unités et prix), créé dans chaque établissement. Le stock initial
 * n'est donné qu'au premier établissement, par une entrée de stock tracée (lot
 * « STOCK-INITIAL »). Les produits sont ensuite reliés au guide des médicaments.
 *
 * Relançable : un produit déjà présent (même nom dans l'établissement) est ignoré.
 */
class MedicamentSeeder extends Seeder
{
    public const FICHIER = 'database/data/medicaments.json';

    public function run(StockService $stock, RapprochementStock $rapprochement): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('Catalogue de médicaments de démonstration : jamais en production.');

            return;
        }
        $catalogue = json_decode((string) @file_get_contents(base_path(self::FICHIER)), true);
        if (!$catalogue) {
            $this->command?->warn('Catalogue absent ou illisible : ' . self::FICHIER);

            return;
        }

        // Les entrées de stock sont attribuées à un utilisateur (audit) : on agit comme le super-admin
        $superAdmin = User::whereHas('roles', fn($q) => $q->where('name', 'super_admin'))->orderBy('id')->first();
        if ($superAdmin) {
            Auth::setUser($superAdmin);
        }

        $familles = collect($catalogue)->pluck('famille')->unique()
            ->mapWithKeys(fn($nom) => [$nom => Famille::firstOrCreate(['nom' => $nom])->id]);

        foreach (Etablissement::orderBy('id')->get() as $rang => $etablissement) {
            app()->instance('etablissement.courant', $etablissement);
            try {
                $existants = Medicament::pluck('nom')->flip();
                $crees = 0;
                DB::transaction(function () use ($catalogue, $familles, $existants, $etablissement, $rang, $stock, &$crees) {
                    foreach ($catalogue as $m) {
                        if (isset($existants[$m['nom']])) {
                            continue;
                        }
                        $produit = (new Medicament)->forceFill([
                            'nom' => $m['nom'], 'description' => $m['description'], 'code_barre' => $m['code_barre'],
                            'stock' => 0, 'stock_min' => $m['stock_min'], 'famille_id' => $familles[$m['famille']],
                        ]);
                        $produit->save();
                        foreach ($m['unites'] as $u) {
                            DB::table('unites')->insert([
                                'etablissement_id' => $etablissement->id, 'medicament_id' => $produit->id,
                                'nom' => $u['nom'], 'symbole' => $u['symbole'], 'facteur' => $u['facteur'],
                                'prix_achat' => $u['prix_achat'], 'prix_vente' => $u['prix_vente'], 'is_default' => $u['defaut'],
                                'created_at' => now(), 'updated_at' => now(),
                            ]);
                        }
                        if ($rang === 0 && $m['stock_initial'] > 0) {
                            $stock->entree($produit, $m['stock_initial'], 'STOCK-INITIAL', null, null, 'Stock initial de démonstration');
                        }
                        $crees++;
                    }
                });
                $this->command?->line("  {$etablissement->nom} : {$crees} médicament(s) créé(s).");
            } finally {
                Etablissement::oublierCourant();
            }
        }

        $classeur = base_path(ReferentielMedicalSeeder::FICHIER);
        if (is_file($classeur)) {
            $r = $rapprochement->relier($classeur);
            $this->command?->line("  Guide des médicaments : {$r['relies']} produit(s) relié(s) à leur fiche.");
        }
    }
}
