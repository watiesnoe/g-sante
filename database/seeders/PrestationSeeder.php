<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrestationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prestations = [
            // Service 1: Consultations
            ['service_medical_id' => 1, 'nom' => 'Consultation générale', 'description' => 'Consultation médicale de routine pour diagnostic initial.', 'prix' => 5000],
            ['service_medical_id' => 1, 'nom' => 'Consultation spécialisée', 'description' => 'Consultation avec un spécialiste selon la pathologie.', 'prix' => 10000],
            ['service_medical_id' => 1, 'nom' => 'Suivi post-opératoire', 'description' => 'Suivi médical après intervention chirurgicale.', 'prix' => 7000],

            // Service 2: Imagerie
            ['service_medical_id' => 2, 'nom' => 'Échographie abdominale', 'description' => 'Examen d’imagerie pour évaluation abdominale.', 'prix' => 15000],
            ['service_medical_id' => 2, 'nom' => 'IRM cérébrale', 'description' => 'Imagerie par résonance magnétique du cerveau.', 'prix' => 40000],
            ['service_medical_id' => 2, 'nom' => 'Radiographie thoracique', 'description' => 'Radiographie pour examen du thorax.', 'prix' => 8000],

            // Service 3: Injections
            ['service_medical_id' => 3, 'nom' => 'Injection intramusculaire', 'description' => 'Injection médicamenteuse selon prescription.', 'prix' => 3000],
            ['service_medical_id' => 3, 'nom' => 'Injection intraveineuse', 'description' => 'Perfusion ou médicament administré par voie IV.', 'prix' => 4000],
            ['service_medical_id' => 3, 'nom' => 'Vaccination', 'description' => 'Vaccin selon calendrier vaccinal.', 'prix' => 3500],

            // Service 4: Analyses
            ['service_medical_id' => 4, 'nom' => 'Analyse sanguine complète', 'description' => 'Bilan sanguin complet.', 'prix' => 12000],
            ['service_medical_id' => 4, 'nom' => 'Analyse urinaire', 'description' => 'Examen complet des urines.', 'prix' => 5000],
            ['service_medical_id' => 4, 'nom' => 'Test de glycémie', 'description' => 'Mesure du taux de sucre dans le sang.', 'prix' => 3000],

            // Service 5: Examens spécialisés
            ['service_medical_id' => 5, 'nom' => 'Électrocardiogramme', 'description' => 'Examen du rythme cardiaque.', 'prix' => 10000],
            ['service_medical_id' => 5, 'nom' => 'Spirométrie', 'description' => 'Mesure de la fonction respiratoire.', 'prix' => 8000],
            ['service_medical_id' => 5, 'nom' => 'Audiométrie', 'description' => 'Test auditif complet.', 'prix' => 6000],

            // Service 6: Certificats
            ['service_medical_id' => 6, 'nom' => 'Certificat médical d’aptitude', 'description' => 'Certificat attestant de l’aptitude à certaines activités.', 'prix' => 5000],
            ['service_medical_id' => 6, 'nom' => 'Arrêt de travail', 'description' => 'Certificat médical pour congé de maladie.', 'prix' => 4000],
        ];

        // Ajouter created_at et updated_at automatiquement
        $now = now();
        foreach ($prestations as &$prestation) {
            $prestation['created_at'] = $now;
            $prestation['updated_at'] = $now;
        }

        DB::table('prestations')->insert($prestations);
    }
}
