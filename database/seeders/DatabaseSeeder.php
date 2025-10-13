<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Ordre important : respecter les dépendances FK.
     */
    public function run(): void
    {
        $this->call([
            // 1. Structure de base (services, salles, lits, examens)
            // ServiceMedicalSeeder::class,
            // SalleLitExamenSeeder::class,
            // PrestationSeeder::class,

            // 2. Familles de médicaments (doit précéder MedicamentsSeederCustom)
            // FamilleSeeder::class,

            // 3. Assurances
            // AssuranceSeeder::class,

            // 4. Médicaments et leurs unités (médicament_id dans unites)
            // Médicaments : MedicamentSeeder, après les établissements de démonstration (voir 10)

            // 5-6. Maladies, symptômes et protocoles : voir ReferentielMedicalSeeder ci-dessous

            // 7. Permissions & Rôles (doit être avant les utilisateurs)
            PermissionRoleSeeder::class,

            // 8. Utilisateurs
            UserSeeder::class,

            // 9. Patients de démonstration (optionnel)
            // PatientSeeder : après les établissements de démonstration (voir 10)
        ]);

        // 9b. Guide des médicaments (MSF) s'il est fourni, puis référentiel médical (OMS) relié au guide
        $guide = env('GUIDE_MSF_PDF', base_path('guideline-339-fr (1).pdf'));
        if (is_file($guide)) {
            $this->command->call('referentiel:importer-msf', ['fichier' => $guide]);
        }
        $this->call(ReferentielMedicalSeeder::class);

        // 10. Établissements de démonstration (Cabinet Wassa, Hôpital Niannakoro Fomba, Clinique Amitie,
        //     CSRéf Famory Doumbia) et leur personnel : jamais en production (mots de passe connus)
        if (!app()->environment('production')) {
            $this->call(EtablissementsDemoSeeder::class);
            $this->call(PatientSeeder::class);
            $this->call(MedicamentSeeder::class);
        }
    }
}
