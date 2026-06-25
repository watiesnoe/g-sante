<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProtocoleMedicamentTableSeeder extends Seeder
{
    public function run(): void
    {
        $associations = [];
        
        $types = ['principal', 'alternatif', 'adjuvant', 'relais', 'assos'];
        
        // Créer des associations pour chaque protocole
        for ($protocoleId = 1; $protocoleId <= 50; $protocoleId++) {
            // Chaque protocole a 2-5 médicaments associés
            $numMedicaments = rand(2, 5);
            for ($j = 1; $j <= $numMedicaments; $j++) {
                $medicamentId = rand(1, 100);
                $type = $types[rand(0, 4)];
                
                $associations[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentId,
                    'type' => $type,
                    'posologie' => 'Dose adaptée au protocole standard',
                    'duree' => rand(1, 30) . ' jours',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }
        
        // Limiter à 200 entrées uniques
        $uniqueAssociations = [];
        foreach ($associations as $assoc) {
            $key = $assoc['protocole_id'] . '_' . $assoc['medicament_id'];
            if (!isset($uniqueAssociations[$key])) {
                $uniqueAssociations[$key] = $assoc;
            }
        }
        
        DB::table('protocole_medicament')->insert(array_slice(array_values($uniqueAssociations), 0, 200));
    }
}