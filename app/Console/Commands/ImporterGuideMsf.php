<?php

namespace App\Console\Commands;

use App\Services\Referentiel\ImportGuideMsf;
use Illuminate\Console\Command;

/** Import (ou mise à jour) du guide MSF « Médicaments essentiels » dans le référentiel des médicaments. */
class ImporterGuideMsf extends Command
{
    protected $signature = 'referentiel:importer-msf {fichier : PDF du guide (ou son texte extrait en .txt)}
        {--source=Médecins Sans Frontières — Médicaments essentiels, guide pratique d\'utilisation (janvier 2026) : Édition citée sur chaque fiche}
        {--essai : Afficher ce qui serait importé, sans rien enregistrer}';

    protected $description = 'Importer le guide MSF des médicaments essentiels dans le référentiel (fiches à valider)';

    public function handle(ImportGuideMsf $import): int
    {
        try {
            if ($this->option('essai')) {
                $texte = str_ends_with(strtolower($this->argument('fichier')), '.txt')
                    ? file_get_contents($this->argument('fichier'))
                    : shell_exec('pdftotext -layout ' . escapeshellarg($this->argument('fichier')) . ' - 2>/dev/null');
                $fiches = $import->analyser(explode("\n", (string) $texte));
                $this->table(['Page', 'Nom', 'Voie', 'Rubriques remplies', 'Avertissement'], collect($fiches)->map(fn($f) => [
                    $f['page'], $f['nom'], $f['voie'],
                    collect(['action_therapeutique', 'indications', 'presentation', 'posologie', 'duree', 'precautions', 'remarques', 'conservation'])->filter(fn($c) => filled($f[$c]))->count() . '/8',
                    $f['avertissement'] ? 'oui' : '',
                ]));
                $this->info(count($fiches) . ' fiche(s) reconnue(s). Rien n\'a été enregistré.');

                return self::SUCCESS;
            }

            $resultat = $import->importer($this->argument('fichier'), $this->option('source'));
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("{$resultat['fiches']} fiche(s) : {$resultat['creees']} créée(s), {$resultat['modifiees']} modifiée(s) (à revalider), {$resultat['inchangees']} inchangée(s).");
        if ($resultat['retirees'] || $resultat['conservees']) {
            $this->line("Absentes de cette édition : {$resultat['retirees']} retirée(s), {$resultat['conservees']} conservée(s) car reliée(s) à un stock.");
        }
        $protocoles = app(\App\Services\Referentiel\RapprochementProtocoles::class)->relier();
        $this->line("Protocoles de traitement : {$protocoles['reliees']} médicament(s) nouvellement relié(s) au guide, {$protocoles['non_reliees']} sans fiche correspondante.");
        $this->warn('Les fiches nouvelles ou modifiées sont « à valider » par un médecin ou un pharmacien avant usage.');

        return self::SUCCESS;
    }
}
