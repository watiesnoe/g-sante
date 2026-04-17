<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // =============================================
        // 1. UTILISATEURS (comptes de démonstration)
        // =============================================
        $users = [
            [
                'name'     => 'Secrétaire Test',
                'email'    => 'test@example.com',
                'password' => Hash::make('watiesnoe123'),
                'role'     => 'secretaire',
            ],
            [
                'name'     => 'Dr. Test Médecin',
                'email'    => 'testdocteur@example.com',
                'password' => Hash::make('watiesnoe123'),
                'role'     => 'medecin',
            ],
            [
                'name'     => 'Pharmacien Test',
                'email'    => 'testpharmacien@example.com',
                'password' => Hash::make('watiesnoe123'),
                'role'     => 'pharmacien',
            ],
            [
                'name'     => 'Gestionnaire Test',
                'email'    => 'testgestionnaire@example.com',
                'password' => Hash::make('watiesnoe123'),
                'role'     => 'gestionnaire',
            ],
            [
                'name'     => 'Siaba Noé',
                'email'    => 'siabaneotraore@gmail.com',
                'password' => Hash::make('watiesnoe123'),
                'role'     => 'superadmin',
            ],
            [
                'name'     => 'Bakary SAMAKE',
                'email'    => 'samakebakary338@gmail.com',
                'password' => Hash::make('79653526'),
                'role'     => 'superadmin',
            ],
        ];

        foreach ($users as $u) {
            DB::table('users')->updateOrInsert(['email' => $u['email']], $u);
        }

        // =============================================
        // 2. SEEDERS (ordre strict à respecter)
        // =============================================
        $this->call([
            // — Référentiels de base —
            ServiceMedicalSeeder::class,    // Services médicaux (Médecine Générale, Pédiatrie...)
            UniteSeeder::class,             // Unités posologiques (comprimé, ampoule, ml...)
            FamilleSeeder::class,           // Familles de médicaments (Antibiotiques, Antalgiques...)

            // — Pharmacie —
            MedicamentsSeeder::class,       // 35+ médicaments essentiels avec prix et stock

            // — Configuration hospitalière —
            PrestationSeeder::class,        // Actes médicaux et leur tarification
            SalleLitExamenSeeder::class,    // Salles, lits et examens par service

            // — Référentiel clinique —
            MaladieSymptomeSeeder::class,   // Maladies courantes + symptômes + liaisons
            PathologiesSeeder::class,       // 9 pathologies avec protocoles & posologies (données enrichies)

            // — Infectiologie avancée —
            ProtocoleTraitementsSeeder::class,  // Protocoles additionnels (grippe, TB, GEA...)
            InfectiologieSeeder::class,          // Cas spéciaux (méningites, TB, candidose)
            ProtocoleMedicamentSeeder::class,    // Liaisons médicaments↔protocoles supplémentaires
        ]);
    }
}
