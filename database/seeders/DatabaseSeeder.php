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
            
            // 2. Infrastructures et Personnel (Dépendent des services)
            SalleLitExamenSeeder::class,
            PermissionRoleSeeder::class,
            UserSeeder::class,
            
            // 3. Données de soins (Dépendent des référentiels)
            PatientSeeder::class,
            PrestationSeeder::class,
            
            // 4. Médicaments (Doivent être exécutés avant les protocoles et pathologies qui les lient)
            MedicamentsSeeder::class,
            
            // 5. Maladies & Symptômes
            DiseaseSeeder::class,
            MaladiesSeeder::class,
            SymptomesSeeder::class,
            MaladieSymptomeSeeder::class,
            
            // 6. Protocoles et Guidelines
            ProtocoleTraitementsSeeder::class,
            ProtocoleMedicamentSeeder::class,
            WhoGuidelinesSeeder::class,
            InfectiologieSeeder::class,
            PathologiesSeeder::class, // Exécuté à la fin pour lier les médicaments et maladies importés en JSON
        ]);
    }
}

