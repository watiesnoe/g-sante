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
            
            // 2. Infrastructures et Personnel (Dépendent des services)
            SalleLitExamenSeeder::class,
            UserSeeder::class,
            
            // 3. Données de soins (Dépendent des référentiels)
            PatientSeeder::class,
            MedicamentSeeder::class,
            MaladieSymptomeSeeder::class,
            
            // 4. Protocoles et Spécialisations
            ProtocoleTraitementSeeder::class,
            InfectiologieSeeder::class,

            // 5. Tests de performance (Optionnel - 1 Million de lignes)
            // LargeDataSeeder::class,
        ]);
    }
}
