<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // 1. Référentiels de base (Sans dépendances)
            ServiceMedicalSeeder::class,
            UniteSeeder::class,
            FamilleSeeder::class,
            AssuranceSeeder::class,
            PathologiesSeeder::class,
            DiseaseSeeder::class,
            
            // 2. Infrastructures et Personnel (Dépendent des services)
            SalleLitExamenSeeder::class,
            PermissionRoleSeeder::class,
            UserSeeder::class,
            
            // 3. Données de soins (Dépendent des référentiels)
            PatientSeeder::class,
            MedicamentSeeder::class,
            MedicamentsProtocolesSeeder::class, // ✅ Médicaments des protocoles OMS manquants
            MaladieSymptomeSeeder::class,
            
            // 4. Protocoles et Spécialisations
            // ProtocoleTraitementSeeder::class,  // Désactivé : noms de maladies différents
            // InfectiologieSeeder::class,
            PrestationSeeder::class,
            WhoGuidelinesSeeder::class,
            ProtocoleEnrichmentSeeder::class,  // ✅ Enrichit les protocoles avec germes + traitements
            SignesDiagnosticsSeeder::class,    // ✅ Enrichit les signes cliniques et examens diagnostics
            ProtocoleMedicamentLinkSeeder::class, // ✅ Lie les médicaments aux protocoles (pivot)

            // 5. Tests de performance (Optionnel - 1 Million de lignes)
            // LargeDataSeeder::class,
        ]);
    }
}
