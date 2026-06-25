<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PrestationSeeder extends Seeder
{
    public function run(): void
    {
        $services = DB::table('service_medicals')->pluck('id', 'nom');

        $prestations = [
            // Médecine Générale
            ['s' => 'Médecine Générale', 'nom' => 'Consultation initiale',       'desc' => 'Première consultation avec examen complet.',                     'q' => false, 'p' => 4500],
            ['s' => 'Médecine Générale', 'nom' => 'Consultation de suivi',       'desc' => 'Suivi médical régulier.',                                         'q' => false, 'p' => 3500],

            // Pédiatrie
            ['s' => 'Pédiatrie', 'nom' => 'Consultation pédiatrique',            'desc' => 'Bilan de santé complet pour enfants.',                            'q' => false, 'p' => 5000],
            ['s' => 'Pédiatrie', 'nom' => 'Vaccination',                        'desc' => 'Administration des vaccins selon le calendrier.',                 'q' => false, 'p' => 3000],
            ['s' => 'Pédiatrie', 'nom' => 'Bilan de croissance',                 'desc' => 'Suivi de la croissance et du développement.',                     'q' => false, 'p' => 4000],

            // Radiologie
            ['s' => 'Radiologie', 'nom' => 'Radiographie standard',             'desc' => 'Examen radiologique de base.',                                    'q' => false, 'p' => 6000],
            ['s' => 'Radiologie', 'nom' => 'Échographie abdominale',             'desc' => 'Examen échographique de l\'abdomen.',                             'q' => false, 'p' => 8000],
            ['s' => 'Radiologie', 'nom' => 'Scanner (CT)',                       'desc' => 'Tomodensitométrie.',                                              'q' => false, 'p' => 15000],
            ['s' => 'Radiologie', 'nom' => 'IRM',                                'desc' => 'Imagerie par résonance magnétique.',                              'q' => false, 'p' => 20000],

            // Soins Infirmiers
            ['s' => 'Soins Infirmiers', 'nom' => 'Pansement simple',             'desc' => 'Pose et changement de pansement.',                               'q' => true,  'p' => 1500],
            ['s' => 'Soins Infirmiers', 'nom' => 'Injection intramusculaire',    'desc' => 'Administration de médicament par injection IM.',                   'q' => true,  'p' => 1000],
            ['s' => 'Soins Infirmiers', 'nom' => 'Injection sous-cutanée',       'desc' => 'Administration de médicament par injection SC.',                  'q' => true,  'p' => 1000],
            ['s' => 'Soins Infirmiers', 'nom' => 'Prise de sang',                'desc' => 'Prélèvement sanguin pour analyses.',                              'q' => true,  'p' => 2000],

            // Laboratoire d'Analyses
            ['s' => 'Laboratoire d\'Analyses', 'nom' => 'Hémogramme complet',    'desc' => 'Numération sanguine complète.',                                   'q' => false, 'p' => 2500],
            ['s' => 'Laboratoire d\'Analyses', 'nom' => 'Bilan hépatique',       'desc' => 'Dosage des enzymes hépatiques.',                                 'q' => false, 'p' => 3500],
            ['s' => 'Laboratoire d\'Analyses', 'nom' => 'Bilan rénal',           'desc' => 'Dosage de la créatinine et urée.',                                'q' => false, 'p' => 3000],
            ['s' => 'Laboratoire d\'Analyses', 'nom' => 'Test PCR COVID-19',     'desc' => 'Test de dépistage du COVID-19.',                                  'q' => false, 'p' => 5000],

            // Cardiologie
            ['s' => 'Cardiologie', 'nom' => 'Consultation cardiologique',        'desc' => 'Examen complet du système cardiovasculaire.',                      'q' => false, 'p' => 7000],
            ['s' => 'Cardiologie', 'nom' => 'Électrocardiogramme (ECG)',         'desc' => 'Enregistrement de l\'activité électrique du cœur.',               'q' => false, 'p' => 5000],
            ['s' => 'Cardiologie', 'nom' => 'Holter ECG',                        'desc' => 'Enregistrement continu de l\'ECG sur 24h.',                        'q' => false, 'p' => 12000],
            ['s' => 'Cardiologie', 'nom' => 'Échocardiographie',                 'desc' => 'Examen échographique du cœur.',                                   'q' => false, 'p' => 10000],

            // Gynécologie
            ['s' => 'Gynécologie', 'nom' => 'Consultation gynécologique',        'desc' => 'Examen gynécologique complet.',                                   'q' => false, 'p' => 6000],
            ['s' => 'Gynécologie', 'nom' => 'Frottis cervico-utérin',            'desc' => 'Test de dépistage du cancer du col de l\'utérus.',                 'q' => false, 'p' => 4000],
            ['s' => 'Gynécologie', 'nom' => 'Échographie pelvienne',            'desc' => 'Examen échographique des organes pelviens.',                       'q' => false, 'p' => 8000],
            ['s' => 'Gynécologie', 'nom' => 'Suivi de grossesse (trimestre)',    'desc' => 'Suivi médical pendant la grossesse.',                             'q' => false, 'p' => 5000],

            // Urgences
            ['s' => 'Urgences', 'nom' => 'Consultation d\'urgence',              'desc' => 'Prise en charge immédiate des urgences médicales.',               'q' => false, 'p' => 10000],
            ['s' => 'Urgences', 'nom' => 'Suture de plaie',                      'desc' => 'Rapprochement des bords d\'une plaie.',                            'q' => true,  'p' => 8000],
            ['s' => 'Urgences', 'nom' => 'Pose de plâtre',                       'desc' => 'Immobilisation d\'un membre fracturé.',                            'q' => false, 'p' => 12000],
            ['s' => 'Urgences', 'nom' => 'Désinfection et pansement de brûlure', 'desc' => 'Soins d\'une brûlure légère à modérée.',                            'q' => true,  'p' => 6000],

            // Maternité
            ['s' => 'Maternité', 'nom' => 'Accouchement normal',                 'desc' => 'Accouchement par voie basse sans complication.',                  'q' => false, 'p' => 50000],
            ['s' => 'Maternité', 'nom' => 'Accouchement par césarienne',         'desc' => 'Accouchement par chirurgie.',                                     'q' => false, 'p' => 80000],
            ['s' => 'Maternité', 'nom' => 'Suivi postnatal',                     'desc' => 'Suivi médical de la mère après l\'accouchement.',                 'q' => false, 'p' => 10000],
            ['s' => 'Maternité', 'nom' => 'Soins du nouveau-né',                'desc' => 'Examen et soins du nouveau-né.',                                 'q' => false, 'p' => 8000],

            // Pharmacie
            ['s' => 'Pharmacie', 'nom' => 'Délivrance de médicaments',           'desc' => 'Remise de médicaments sur ordonnance.',                           'q' => true,  'p' => 0],
            ['s' => 'Pharmacie', 'nom' => 'Conseil pharmaceutique',              'desc' => 'Conseil sur l\'utilisation des médicaments.',                     'q' => false, 'p' => 0],
            ['s' => 'Pharmacie', 'nom' => 'Préparation magistrale',              'desc' => 'Préparation de médicaments personnalisés.',                       'q' => false, 'p' => 1500],
        ];

        $now = now();
        $inserted = 0;
        $skipped  = 0;

        foreach ($prestations as $pre) {
            // Vérifier si le service existe
            if (!isset($services[$pre['s']])) {
                $this->command->warn("⚠️  Service introuvable : {$pre['s']}");
                $skipped++;
                continue;
            }

            // Vérifier si la prestation existe déjà (nom + service)
            $exists = DB::table('prestations')
                ->where('nom', $pre['nom'])
                ->where('service_medical_id', $services[$pre['s']])
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            DB::table('prestations')->insert([
                'uuid'               => (string) Str::uuid(),
                'service_medical_id' => $services[$pre['s']],
                'nom'                => $pre['nom'],
                'description'        => $pre['desc'],
                'quantifiable'       => $pre['q'],
                'prix'               => $pre['p'],
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);

            $inserted++;
        }

        $this->command->info("✅ Prestations seedées : {$inserted} insérées, {$skipped} ignorées.");
    }
}
