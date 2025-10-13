<?php

namespace App\Console\Commands;

use App\Services\Referentiel\ImportGuideClinique;
use Illuminate\Console\Command;

/** Import du fichier « Maladies, symptômes et traitements » dans le guide clinique. */
class ImporterGuideClinique extends Command
{
    protected $signature = 'referentiel:importer-guide-clinique {fichier : Classeur .xlsx « Maladies, symptômes et traitements »}
        {--remplacer : Supprimer les maladies, symptômes et protocoles absents du fichier (archivés si des consultations les utilisent)}
        {--essai : Afficher ce qui serait importé, sans rien enregistrer}';

    protected $description = 'Importer le guide clinique (maladies, symptômes, signes de gravité, traitements, molécules)';

    public function handle(ImportGuideClinique $import): int
    {
        try {
            if ($this->option('essai')) {
                $donnees = $import->analyser($this->argument('fichier'));
                $this->table(['Chapitre', 'Maladie', 'Symptômes', 'Médicaments'], collect($donnees['maladies'])->map(fn($m) => [
                    $m['chapitre'], $m['nom'], count($m['symptomes']), count($donnees['lignes'][$m['nom']] ?? []),
                ]));
                $this->info(count($donnees['maladies']) . ' maladie(s), ' . count($donnees['dcis']) . ' molécule(s). Rien n\'a été enregistré.');

                return self::SUCCESS;
            }

            $r = $import->importer($this->argument('fichier'), (bool) $this->option('remplacer'));
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("{$r['maladies']} maladie(s), {$r['symptomes']} symptôme(s), {$r['medicaments']} molécule(s), {$r['lignes']} ligne(s) de traitement, {$r['abreviations']} abréviation(s).");
        if ($r['berrebi']) {
            $this->line("Berrebi : {$r['berrebi']} fiche(s) ; posologies lues par OCR, affichées « à vérifier ».");
        }
        if ($this->option('remplacer')) {
            $this->line("Absents du fichier — supprimés : {$r['supprimes']['maladies']} maladie(s), {$r['supprimes']['symptomes']} symptôme(s), {$r['supprimes']['protocoles']} protocole(s) ; "
                . "archivés car utilisés par des consultations : {$r['archives']['maladies']} maladie(s), {$r['archives']['symptomes']} symptôme(s), {$r['archives']['protocoles']} protocole(s).");
        }
        $this->warn('Aide à la décision : vérifier le protocole national et l\'état du patient avant toute prescription.');

        return self::SUCCESS;
    }
}
