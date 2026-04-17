<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalleLitExamenSeeder extends Seeder
{
    public function run(): void
    {
        $services = DB::table('service_medicals')->get();

        foreach ($services as $service) {
            // Créer/Mettre à jour des salles
            for ($i = 1; $i <= 3; $i++) {
                $salleNom = $service->nom . ' - Salle ' . $i;
                DB::table('salles')->updateOrInsert(
                    ['nom' => $salleNom],
                    [
                        'type' => $i == 1 ? 'Consultation' : 'Hospitalisation',
                        'service_medical_id' => $service->id,
                        'capacite' => 4,
                        'prix' => 10000,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
                
                $salleId = DB::table('salles')->where('nom', $salleNom)->value('id');

                // Lits (idempotent: on vérifie si le lit existe déjà pour cette salle)
                for ($j = 1; $j <= 4; $j++) {
                    $litNum = $salleNom . ' - Lit ' . $j;
                    DB::table('lits')->updateOrInsert(
                        ['numero' => $litNum, 'salle_id' => $salleId],
                        [
                            'statut' => 'Libre',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }
    }
}
