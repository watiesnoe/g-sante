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
            // MedicamentsSeederCustom::class,

            // 5. Maladies & Symptômes
            // DiseaseSeeder::class,
            // MaladiesSeederCustom::class,
            // SymptomesSeederCustom::class,
            // MaladieSymptomeSeederCustom::class,

            // 6. Protocoles et Guidelines
            // ProtocoleTraitementsSeederCustom::class,
            // ProtocoleMedicamentSeederCustom::class,
            // WhoGuidelinesSeeder::class,
            // InfectiologieSeeder::class,
            // PathologiesSeeder::class,

            // 7. Permissions & Rôles (doit être avant les utilisateurs)
            PermissionRoleSeeder::class,

            // 8. Utilisateurs
            UserSeeder::class,

            // 9. Patients de démonstration (optionnel)
            // PatientSeeder::class,
        ]);
    }
}
