<?php

namespace Database\Seeders;

use App\Services\Referentiel\ImportGuideClinique;
use Illuminate\Database\Seeder;

/**
 * Référentiel médical commun à la plateforme : familles de médicaments, puis le
 * guide clinique (maladies, symptômes, signes de gravité, traitements) importé
 * du classeur database/data/guide_clinique.xlsx (guide clinique MSF, OMS, Vidal, Dorosz).
 */
class ReferentielMedicalSeeder extends Seeder
{
    public const FICHIER = 'database/data/guide_clinique.xlsx';

    public function run(ImportGuideClinique $import): void
    {
        $this->call(FamilleSeeder::class);

        if (!is_file(base_path(self::FICHIER))) {
            $this->command?->warn('Guide clinique absent (' . self::FICHIER . ') : importez-le avec referentiel:importer-guide-clinique.');

            return;
        }

        $r = $import->importer(base_path(self::FICHIER), true);
        $this->command?->info("Guide clinique : {$r['maladies']} maladies, {$r['symptomes']} symptômes, {$r['lignes']} lignes de traitement.");
    }
}
