<?php

namespace Database\Seeders;
use Illuminate\Support\Str;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceMedicalSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['nom' => 'Médecine Générale', 'description' => 'Consultations générales pour adultes et enfants.'],
            ['nom' => 'Pédiatrie', 'description' => 'Suivi médical complet des enfants et adolescents.'],
            ['nom' => 'Radiologie', 'description' => 'Service d’imagerie médicale (radiographie, échographie, scanner, IRM).'],
            ['nom' => 'Soins Infirmiers', 'description' => 'Soins de base, pansements, injections et traitements infirmiers.'],
            ['nom' => 'Laboratoire d\'Analyses', 'description' => 'Examens biologiques et tests médicaux.'],
            ['nom' => 'Cardiologie', 'description' => 'Prise en charge des maladies cardiovasculaires.'],
            ['nom' => 'Gynécologie', 'description' => 'Santé reproductive et suivi des grossesses.'],
            ['nom' => 'Urgences', 'description' => 'Prise en charge immédiate des situations médicales graves.'],
            ['nom' => 'Maternité', 'description' => 'Accouchements et soins néonataux.'],
            ['nom' => 'Pharmacie', 'description' => 'Dispensation de médicaments et conseils.'],
        ];

        foreach ($services as $service) {
            DB::table('service_medicals')->updateOrInsert(
                ['nom' => $service['nom']],
                [
                    'description' => $service['description'],
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now(),
                ]
            );
        }
    }
}
