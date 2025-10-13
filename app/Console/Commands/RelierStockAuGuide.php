<?php

namespace App\Console\Commands;

use App\Services\Referentiel\RapprochementStock;
use Illuminate\Console\Command;

/** Relie les produits du stock de tous les établissements aux fiches du guide des médicaments. */
class RelierStockAuGuide extends Command
{
    protected $signature = 'referentiel:relier-stock
        {--fichier=database/data/guide_clinique.xlsx : Classeur contenant l\'onglet « Comparaison Vidal-Dorosz »}
        {--essai : Compter sans rien enregistrer}';

    protected $description = 'Relier les produits du stock (noms commerciaux) aux fiches du guide des médicaments (DCI)';

    public function handle(RapprochementStock $rapprochement): int
    {
        try {
            $r = $rapprochement->relier(base_path($this->option('fichier')), (bool) $this->option('essai'));
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info(($this->option('essai') ? '[essai] ' : '') . "{$r['relies']} produit(s) relié(s) : {$r['par_methode']['vidal']} par leur nom Vidal, "
            . "{$r['par_methode']['marque']} par leur marque, {$r['par_methode']['nom']} par leur DCI. {$r['deja']} déjà relié(s), non modifié(s).");

        $fichier = storage_path('app/private/produits_non_relies_au_guide.csv');
        $csv = fopen($fichier, 'w');
        fputcsv($csv, ['Produit', "Nombre d'établissements"], ';');
        foreach ($r['non_relies'] as $nom => $nombre) {
            fputcsv($csv, [$nom, $nombre], ';');
        }
        fclose($csv);
        $this->line(count($r['non_relies']) . " nom(s) de produit sans fiche sûre, à relier à la main depuis le guide (bouton « Lier ») : $fichier");

        return self::SUCCESS;
    }
}
