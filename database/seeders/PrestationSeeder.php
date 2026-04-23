<?php

namespace Database\Seeders;
use Illuminate\Support\Str;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrestationSeeder extends Seeder
{
    public function run(): void
    {
        $services = DB::table('service_medicals')->pluck('id', 'nom');

        $prestations = [
            // Médecine Générale
            ['s' => 'Médecine Générale', 'nom' => 'Consultation générale', 'q' => false, 'p' => 5000],
            ['s' => 'Médecine Générale', 'nom' => 'Consultation à domicile', 'q' => false, 'p' => 15000],

            // Pédiatrie
            ['s' => 'Pédiatrie', 'nom' => 'Consultation pédiatrique', 'q' => false, 'p' => 7000],
            ['s' => 'Pédiatrie', 'nom' => 'Pesée & Vaccination', 'q' => false, 'p' => 3000],

            // Radiologie
            ['s' => 'Radiologie', 'nom' => 'Échographie abdominale', 'q' => false, 'p' => 15000],
            ['s' => 'Radiologie', 'nom' => 'Radiographie thoracique', 'q' => false, 'p' => 10000],
            ['s' => 'Radiologie', 'nom' => 'Échographie obstétricale', 'q' => false, 'p' => 12000],

            // Soins Infirmiers
            ['s' => 'Soins Infirmiers', 'nom' => 'Injection IM', 'q' => true, 'p' => 1000],
            ['s' => 'Soins Infirmiers', 'nom' => 'Injection IV', 'q' => true, 'p' => 2000],
            ['s' => 'Soins Infirmiers', 'nom' => 'Pansement simple', 'q' => true, 'p' => 2500],
            ['s' => 'Soins Infirmiers', 'nom' => 'Prise de tension', 'q' => false, 'p' => 500],

            // Laboratoire
            ['s' => 'Laboratoire d\'Analyses', 'nom' => 'NFS (Hémogramme)', 'q' => false, 'p' => 8000],
            ['s' => 'Laboratoire d\'Analyses', 'nom' => 'Test Rapide Paludisme (TDR)', 'q' => false, 'p' => 1000],
            ['s' => 'Laboratoire d\'Analyses', 'nom' => 'Glycémie à jeun', 'q' => false, 'p' => 2000],
            ['s' => 'Laboratoire d\'Analyses', 'nom' => 'Analyse des urines (ECBU)', 'q' => false, 'p' => 6000],
            ['s' => 'Laboratoire d\'Analyses', 'nom' => 'Widal (Fièvre typhoïde)', 'q' => false, 'p' => 5000],

            // Maternité / Gynéco
            ['s' => 'Maternité', 'nom' => 'Accouchement normal', 'q' => false, 'p' => 30000],
            ['s' => 'Maternité', 'nom' => 'Césarienne', 'q' => false, 'p' => 150000],
            ['s' => 'Maternité', 'nom' => 'Suivi de grossesse (CPN)', 'q' => false, 'p' => 5000],
        ];

        foreach ($prestations as $pre) {
            if (isset($services[$pre['s']])) {
                DB::table('prestations')->updateOrInsert(
                    ['nom' => $pre['nom']],
                    [
                        'service_medical_id' => $services[$pre['s']],
                        'description' => $pre['nom'] . ' au service ' . $pre['s'],
                        'quantifiable' => $pre['q'],
                        'prix' => $pre['p'],
                        'uuid' => (string) Str::uuid(), 'created_at' => now(),
                        'uuid' => (string) Str::uuid(), 'updated_at' => now(),
                    ]
                );
            }
        }
    }
}