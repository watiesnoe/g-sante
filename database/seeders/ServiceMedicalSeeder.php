<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceMedicalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('service_medicals')->insert([
            [
                'nom' => 'Médecine Générale',
                'description' => 'Consultations générales pour adultes et enfants.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Pédiatrie',
                'description' => 'Suivi médical complet des enfants et adolescents.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Radiologie',
                'description' => 'Service d’imagerie médicale (radiographie, échographie, scanner, IRM).',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Soins Infirmiers',
                'description' => 'Soins de base, pansements, injections et traitements infirmiers.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Cardiologie',
                'description' => 'Prise en charge des maladies cardiovasculaires.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Gynécologie',
                'description' => 'Santé reproductive, suivi des grossesses et maladies gynécologiques.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Urgences',
                'description' => 'Prise en charge immédiate des situations médicales graves.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Dermatologie',
                'description' => 'Diagnostic et traitement des maladies de la peau, cheveux et ongles.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Ophtalmologie',
                'description' => 'Soins des yeux, troubles de la vision et chirurgies oculaires.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Chirurgie',
                'description' => 'Interventions chirurgicales générales et spécialisées.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Neurologie',
                'description' => 'Prise en charge des maladies du système nerveux.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Psychiatrie',
                'description' => 'Soins liés à la santé mentale et accompagnement psychologique.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
