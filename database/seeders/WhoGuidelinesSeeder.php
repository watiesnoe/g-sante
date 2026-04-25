<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class WhoGuidelinesSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();
        $file = base_path('database/seeders/data/who_guidelines.tsv');

        if (!file_exists($file)) {
            $this->command->error("File who_guidelines.tsv not found.");
            return;
        }

        $defaultUniteId = DB::table('unites')->value('id') ?? 1;

        $handle = fopen($file, "r");
        
        // Skip header
        fgets($handle);

        while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {
            if (count($data) < 5) continue;

            $numero = trim($data[0]);
            $pathologie = trim($data[1]);
            $traitement = trim($data[2]);
            $medicamentNom = trim($data[3]);
            $posologie = trim($data[4]);
            $symptomesText = isset($data[5]) ? trim($data[5]) : '';

            // 1. Create or Find Maladie
            $maladie = DB::table('maladies')->where('nom', $pathologie)->first();
            if (!$maladie) {
                $maladieId = DB::table('maladies')->insertGetId([
                    'nom' => $pathologie,
                    'uuid' => (string) Str::uuid(),
                    'created_at' => $now,
                    'updated_at' => $now,
                    'description' => "Standard WHO : " . $pathologie
                ]);
            } else {
                $maladieId = $maladie->id;
            }

            // 2. Handle Symptoms
            if (!empty($symptomesText)) {
                $symptomesList = array_map('trim', explode(',', $symptomesText));
                foreach ($symptomesList as $sName) {
                    if (empty($sName)) continue;
                    $symptom = DB::table('symptomes')->where('nom', $sName)->first();
                    if (!$symptom) {
                        $symptomId = DB::table('symptomes')->insertGetId([
                            'nom' => $sName,
                            'uuid' => (string) Str::uuid(),
                            'created_at' => $now,
                            'updated_at' => $now
                        ]);
                    } else {
                        $symptomId = $symptom->id;
                    }

                    DB::table('maladie_symptome')->updateOrInsert(
                        ['maladie_id' => $maladieId, 'symptome_id' => $symptomId],
                        ['uuid' => (string) Str::uuid(), 'created_at' => $now, 'updated_at' => $now]
                    );
                }
            }

            // 3. Create or Find Protocol
            $protocol = DB::table('protocole_traitements')->where('maladie_id', $maladieId)->first();
            if (!$protocol) {
                $protocoleId = DB::table('protocole_traitements')->insertGetId([
                    'maladie_id' => $maladieId,
                    'uuid' => (string) Str::uuid(), 
                    'titre' => "Protocole WHO : " . $pathologie,
                    'created_at' => $now, 
                    'updated_at' => $now
                ]);
            } else {
                $protocoleId = $protocol->id;
            }

            // 4. Handle Therapeutic Family (from Traitement column)
            $familleId = DB::table('familles')->where('nom', $traitement)->value('id');
            if (!$familleId && !empty($traitement)) {
                $familleId = DB::table('familles')->insertGetId([
                    'nom' => $traitement,
                    'uuid' => (string) Str::uuid(),
                    'created_at' => $now,
                    'updated_at' => $now,
                    'description' => "Catégorie thérapeutique : " . $traitement
                ]);
            }
            $familleId = $familleId ?? 1;

            // 5. Create or Find Medicament
            $medicament = DB::table('medicaments')->where('nom', $medicamentNom)->first();
            if (!$medicament) {
                $medicamentId = DB::table('medicaments')->insertGetId([
                    'nom' => $medicamentNom,
                    'description' => "Classe: " . $traitement,
                    'stock' => 100,
                    'stock_min' => 10,
                    'prix_achat' => 500,
                    'prix_vente' => 1000,
                    'unite_id' => $defaultUniteId,
                    'famille_id' => $familleId,
                    'uuid' => (string) Str::uuid(),
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
            } else {
                $medicamentId = $medicament->id;
                // Update family if necessary
                DB::table('medicaments')->where('id', $medicamentId)->update(['famille_id' => $familleId]);
            }

            // 6. Link Protocol -> Medicament
            $link = DB::table('protocole_medicament')
                ->where('protocole_id', $protocoleId)
                ->where('medicament_id', $medicamentId)
                ->first();
                
            if (!$link) {
                DB::table('protocole_medicament')->insert([
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentId,
                    'uuid' => (string) Str::uuid(),
                    'type' => 'principal',
                    'posologie' => $posologie,
                    'duree' => "Selon protocole WHO",
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
            } else {
                DB::table('protocole_medicament')
                    ->where('id', $link->id)
                    ->update([
                        'posologie' => $posologie,
                        'updated_at' => $now
                    ]);
            }
        }

        fclose($handle);
        $this->command->info('WHO Guidelines seeded successfully with standardized treatments!');
    }
}
