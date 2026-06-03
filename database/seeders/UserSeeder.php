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
                'name' => 'Dr Diop', 'nom' => 'Diop', 'prenom' => 'Ousmane', 'telephone' => '772345679',
                'adresse' => 'Dakar', 'role' => 'medecin', 'email' => 'drdiop@example.com',
                'password' => Hash::make('password'), 'statut' => 'actif', 'service_medical_id' => $serviceIds['Radiologie'] ?? null,
            ],
            [
                'name' => 'Dr Keita', 'nom' => 'Keita', 'prenom' => 'Ibrahim', 'telephone' => '772345680',
                'adresse' => 'Dakar', 'role' => 'medecin', 'email' => 'drkeita@example.com',
                'password' => Hash::make('password'), 'statut' => 'actif', 'service_medical_id' => $serviceIds['Cardiologie'] ?? null,
            ],
            [
                'name' => 'Dr Kone', 'nom' => 'Kone', 'prenom' => 'Awa', 'telephone' => '772345681',
                'adresse' => 'Dakar', 'role' => 'medecin', 'email' => 'drkone@example.com',
                'password' => Hash::make('password'), 'statut' => 'actif', 'service_medical_id' => $serviceIds['Gynécologie'] ?? null,
            ],
            [
                'name' => 'Dr Traore', 'nom' => 'Traore', 'prenom' => 'Cheick', 'telephone' => '772345682',
                'adresse' => 'Dakar', 'role' => 'medecin', 'email' => 'drtraore@example.com',
                'password' => Hash::make('password'), 'statut' => 'actif', 'service_medical_id' => $serviceIds['Urgences'] ?? null,
            ],
            [
                'name' => 'Dr Coulibaly', 'nom' => 'Coulibaly', 'prenom' => 'Salif', 'telephone' => '772345683',
                'adresse' => 'Dakar', 'role' => 'medecin', 'email' => 'drcoulibaly@example.com',
                'password' => Hash::make('password'), 'statut' => 'actif', 'service_medical_id' => $serviceIds['Laboratoire d\'Analyses'] ?? null,
            ],
            [
                'name' => 'moussa_pharmacien', 'nom' => 'Ndiaye', 'prenom' => 'Moussa', 'telephone' => '773456789',
                'adresse' => 'Dakar', 'role' => 'pharmacien', 'email' => 'pharmacy@example.com',
                'password' => Hash::make('password'), 'statut' => 'actif', 'service_medical_id' => null,
            ],
            [
                'name' => 'aissatou_gestionnaire', 'nom' => 'Fall', 'prenom' => 'Aissatou', 'telephone' => '774567890',
                'adresse' => 'Dakar', 'role' => 'gestionnaire', 'email' => 'manager@example.com',
                'password' => Hash::make('password'), 'statut' => 'actif', 'service_medical_id' => null,
            ],
            [
                'name' => 'admin_systeme', 'nom' => 'Admin', 'prenom' => 'System', 'telephone' => '775678901',
                'adresse' => 'Dakar', 'role' => 'superadmin', 'email' => 'admin@example.com',
                'password' => Hash::make('password'), 'statut' => 'actif', 'service_medical_id' => null,
            ],
            [
                'name' => 'Siabaneo', 'nom' => 'TRAORE', 'prenom' => 'Siabaneo', 'telephone' => '770000000',
                'adresse' => 'Dakar', 'role' => 'superadmin', 'email' => 'siabaneotraore@gmail.com',
                'password' => Hash::make('password'), 'statut' => 'actif', 'service_medical_id' => null,
            ],
            [
                'name' => 'Amadou', 'nom' => 'TRAORE', 'prenom' => 'Amadou', 'telephone' => '77000000',
                'adresse' => 'Dakar', 'role' => 'secretaire', 'email' => 'amadou@gmail.com',
                'password' => Hash::make('password'), 'statut' => 'actif', 'service_medical_id' => null,
            ],
        ];

        foreach ($users as $userData) {
            $roleName = $userData['role'];
            unset($userData['role']);
            
            // Map legacy role names to new Spatie role names
            if ($roleName === 'superadmin') $roleName = 'super_admin';
            if ($roleName === 'gestionnaire') $roleName = 'gestionnaire_stock';

            $userData['uuid'] = (string) Str::uuid();
            $userData['email_verified_at'] = now();

            $user = \App\Models\User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
            
            // Assign role if it exists (requires Spatie Permission)
            try {
                $user->assignRole($roleName);
            } catch (\Exception $e) {
                // Role might not exist if PermissionRoleSeeder wasn't run yet
            }
        }
    }
}
