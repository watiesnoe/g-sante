<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MedicamentsSeederCustom extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Récupérer ou créer les familles
        $familles = DB::table('familles')->pluck('id', 'nom')->toArray();

        $getFamille = function (string $nom) use (&$familles, $now): int {
            if (!isset($familles[$nom])) {
                $id = DB::table('familles')->insertGetId([
                    'nom' => $nom,
                    'uuid' => (string) Str::uuid(),
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
                $familles[$nom] = $id;
            }
            return $familles[$nom];
        };

        // Données des médicaments
        $medicaments = [
            'Amoxicilline 500mg' => [
                'famille' => 'Antibiotiques',
                'description' => 'Aminopénicilline à large spectre, voie orale.',
                'stock' => 500, 'stock_min' => 50,
                'unites' => [
                    ['nom' => 'Gélule', 'symbole' => 'gél', 'facteur' => 1, 'prix_achat' => 25, 'prix_vente' => 40, 'is_default' => true],
                    ['nom' => 'Boite 12 gélules', 'symbole' => 'bt12', 'facteur' => 12, 'prix_achat' => 280, 'prix_vente' => 450, 'is_default' => false],
                ],
            ],
            'Amoxicilline 250mg/5ml Sirop' => [
                'famille' => 'Antibiotiques',
                'description' => 'Suspension buvable pour enfants.',
                'stock' => 100, 'stock_min' => 15,
                'unites' => [
                    ['nom' => 'Flacon 60ml', 'symbole' => 'fl', 'facteur' => 1, 'prix_achat' => 850, 'prix_vente' => 1200, 'is_default' => true],
                ],
            ],
            'Paracétamol 500mg' => [
                'famille' => 'Analgésiques',
                'description' => 'Antalgique et antipyritique de premier choix.',
                'stock' => 1000, 'stock_min' => 100,
                'unites' => [
                    ['nom' => 'Comprimé', 'symbole' => 'cp', 'facteur' => 1, 'prix_achat' => 5, 'prix_vente' => 10, 'is_default' => true],
                    ['nom' => 'Boite 20 comprimés', 'symbole' => 'bt20', 'facteur' => 20, 'prix_achat' => 80, 'prix_vente' => 150, 'is_default' => false],
                ],
            ],
            'Ibuprofène 400mg' => [
                'famille' => 'Anti-inflammatoires',
                'description' => 'Anti-inflammatoire non stéroïdien (AINS).',
                'stock' => 400, 'stock_min' => 40,
                'unites' => [
                    ['nom' => 'Comprimé', 'symbole' => 'cp', 'facteur' => 1, 'prix_achat' => 15, 'prix_vente' => 25, 'is_default' => true],
                    ['nom' => 'Boite 10 comprimés', 'symbole' => 'bt10', 'facteur' => 10, 'prix_achat' => 130, 'prix_vente' => 200, 'is_default' => false],
                ],
            ],
            'Ceftriaxone 1g Injectable' => [
                'famille' => 'Antibiotiques',
                'description' => 'Céphalosporine de 3e génération pour infections sévères.',
                'stock' => 150, 'stock_min' => 20,
                'unites' => [
                    ['nom' => 'Flacon poudre + solvant', 'symbole' => 'amp', 'facteur' => 1, 'prix_achat' => 2700, 'prix_vente' => 4500, 'is_default' => true],
                ],
            ],
            'Artesunate 60mg Injectable' => [
                'famille' => 'Antipaludiques',
                'description' => 'Traitement de première ligne du paludisme grave.',
                'stock' => 120, 'stock_min' => 10,
                'unites' => [
                    ['nom' => 'Flacon', 'symbole' => 'fl', 'facteur' => 1, 'prix_achat' => 1500, 'prix_vente' => 2500, 'is_default' => true],
                ],
            ],
            'Artéméther/Luméfantrine 20/120mg' => [
                'famille' => 'Antipaludiques',
                'description' => 'Traitement combiné à base d’artémisinine (ACT).',
                'stock' => 600, 'stock_min' => 50,
                'unites' => [
                    ['nom' => 'Comprimé', 'symbole' => 'cp', 'facteur' => 1, 'prix_achat' => 50, 'prix_vente' => 80, 'is_default' => true],
                    ['nom' => 'Boite 24 comprimés', 'symbole' => 'bt24', 'facteur' => 24, 'prix_achat' => 1000, 'prix_vente' => 1500, 'is_default' => false],
                ],
            ],
            'Salbutamol 100mcg/dose' => [
                'famille' => 'Bronchodilatateurs',
                'description' => 'Bronchodilatateur d’action rapide inhalé.',
                'stock' => 80, 'stock_min' => 10,
                'unites' => [
                    ['nom' => 'Inhalateur 200 doses', 'symbole' => 'inh', 'facteur' => 1, 'prix_achat' => 1200, 'prix_vente' => 1800, 'is_default' => true],
                ],
            ],
        ];

        foreach ($medicaments as $nom => $data) {
            $familleId = $getFamille($data['famille']);
            $medicamentId = DB::table('medicaments')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'nom' => $nom,
                'description' => $data['description'],
                'stock' => $data['stock'],
                'stock_min' => $data['stock_min'],
                'famille_id' => $familleId,
                'created_at' => $now,
                'updated_at' => $now
            ]);

            foreach ($data['unites'] as $u) {
                DB::table('unites')->insert([
                    'nom' => $u['nom'],
                    'symbole' => $u['symbole'],
                    'facteur' => $u['facteur'],
                    'prix_achat' => $u['prix_achat'],
                    'prix_vente' => $u['prix_vente'],
                    'is_default' => $u['is_default'],
                    'medicament_id' => $medicamentId,
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
            }
        }
    }
}
