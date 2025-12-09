<?php

namespace Database\Seeders;

use App\Http\Controllers\PrestationController;
use App\Models\Unite;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('watiesnoe123'),
            'role' => 'secretaire',
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'testdocteur@example.com',
            'password' => Hash::make('watiesnoe123'),
            'role' => 'medecin',
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'testpharmacien@example.com',
            'password' => Hash::make('watiesnoe123'),
            'role' => 'pharmacien',
        ]);


        User::factory()->create([
            'name' => 'Test User',
            'email' => 'testgestionnaire@example.com',
            'password' => Hash::make('watiesnoe123'),
            'role' => 'gestionnaire',
        ]);

        User::factory()->create([
            'name' => 'Siaba Noé',
            'email' => 'siabaneotraore@gmail.com',
            'password' => Hash::make('watiesnoe123'),
            'role' => 'superadmin',
        ]);
        User::factory()->create([
            'name' => 'Bakary SAMAKE',
            'email' => 'samakebakary338@gmail.com',
            'password' => Hash::make('79653526'),
            'role' => 'superadmin',
        ]);
        $this->call([
            ServiceMedicalSeeder::class,
            PrestationSeeder::class,
            SalleLitExamenSeeder::class,
            UniteSeeder::class,
            FamilleSeeder::class,
            MaladieSymptomeSeeder::class,
            MedicamentsSeeder::class
        ]);
    }
}
