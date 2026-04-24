<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $serviceIds = DB::table('service_medicals')->pluck('id', 'nom')->toArray();

        $users = [
            [
                'name' => 'Dr Diallo', 'nom' => 'Diallo', 'prenom' => 'Amadou', 'telephone' => '771234567',
                'adresse' => 'Dakar', 'role' => 'medecin', 'email' => 'doctor@example.com',
                'password' => Hash::make('password'), 'statut' => 'actif', 'service_medical_id' => $serviceIds['Médecine Générale'] ?? null,
            ],
            [
                'name' => 'Dr Sow', 'nom' => 'Sow', 'prenom' => 'Fatou', 'telephone' => '772345678',
                'adresse' => 'Dakar', 'role' => 'medecin', 'email' => 'doctor2@example.com',
                'password' => Hash::make('password'), 'statut' => 'actif', 'service_medical_id' => $serviceIds['Pédiatrie'] ?? null,
            ],
            [
                'name' => 'Pharmacien', 'nom' => 'Ndiaye', 'prenom' => 'Moussa', 'telephone' => '773456789',
                'adresse' => 'Dakar', 'role' => 'pharmacien', 'email' => 'pharmacy@example.com',
                'password' => Hash::make('password'), 'statut' => 'actif', 'service_medical_id' => null,
            ],
            [
                'name' => 'Gestionnaire', 'nom' => 'Fall', 'prenom' => 'Aissatou', 'telephone' => '774567890',
                'adresse' => 'Dakar', 'role' => 'gestionnaire', 'email' => 'manager@example.com',
                'password' => Hash::make('password'), 'statut' => 'actif', 'service_medical_id' => null,
            ],
            [
                'name' => 'SuperAdmin', 'nom' => 'Admin', 'prenom' => 'System', 'telephone' => '775678901',
                'adresse' => 'Dakar', 'role' => 'superadmin', 'email' => 'admin@example.com',
                'password' => Hash::make('password'), 'statut' => 'actif', 'service_medical_id' => null,
            ],
            [
                'name' => 'Siabaneo', 'nom' => 'TRAORE', 'prenom' => 'Siabaneo', 'telephone' => '770000000',
                'adresse' => 'Dakar', 'role' => 'superadmin', 'email' => 'siabaneotraore@gmail.com',
                'password' => Hash::make('password'), 'statut' => 'actif', 'service_medical_id' => null,
            ],
        ];

        foreach ($users as $user) {
            $user['uuid'] = (string) Str::uuid();
            $user['email_verified_at'] = now();
            $user['created_at'] = now();
            $user['updated_at'] = now();

            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                $user
            );
        }
    }
}
