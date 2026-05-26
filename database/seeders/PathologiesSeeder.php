<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PathologiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Starting extraction and generation of Pathologies and Protocols...');

        // Load JSON
        $jsonPath = base_path('pathologies_data.json');
        $diseases = [];
        if (file_exists($jsonPath)) {
            $diseases = json_decode(file_get_contents($jsonPath), true);
        }

        if (empty($diseases)) {
            $this->command->warn('No pathologies_data.json found. Aborting.');
            return;
        }
        
        // Grab some random medicines to link to the protocols
        $medicamentIds = DB::table('medicaments')->inRandomOrder()->take(500)->pluck('id')->toArray();
        if (empty($medicamentIds)) {
            $this->command->warn('No medicaments in database to link. Aborting.');
            return;
        }

        $faker = \Faker\Factory::create('fr_FR');
        $now = now();
        $count = 0;

        $this->command->getOutput()->progressStart(count($diseases));

        foreach ($diseases as $diseaseName) {
            // Check if disease exists, else create
            // The constraint is on `nom`, let's just insert
            
            // Clean name
            $diseaseName = trim(preg_replace('/\s+/', ' ', $diseaseName));
            
            // We use insertGetId or firstOrCreate
            $maladie = DB::table('maladies')->where('nom', $diseaseName)->first();
            if ($maladie) {
                $maladieId = $maladie->id;
            } else {
                $maladieId = DB::table('maladies')->insertGetId([
                    'uuid' => (string) Str::uuid(),
                    'nom' => substr($diseaseName, 0, 255),
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
            }

            // Check if a Protocol already exists for this disease
            $existingProto = DB::table('protocole_traitements')->where('maladie_id', $maladieId)->first();
            if ($existingProto) {
                $protoId = $existingProto->id;
            } else {
                $protoId = DB::table('protocole_traitements')->insertGetId([
                    'uuid' => (string) Str::uuid(),
                    'maladie_id' => $maladieId,
                    'titre' => 'Protocole issu des Directives (Mali/MSF/Cameroun) - ' . rand(100, 9999),
                    'signes' => 'Syndrome clinique associé à ' . substr($diseaseName, 0, 100) . '. ' . $faker->sentence(),
                    'diagnostics' => 'Diagnostic clinique et para-clinique recommandé.',
                    'germes_nourrisson' => rand(0,1) ? 'Bactéries Gram-' : null,
                    'germes_adulte' => rand(0,1) ? 'Bactéries atypiques' : null,
                    'traitement_principal' => 'Voir la posologie des médicaments associés.',
                    'posologie_principale' => 'Selon le guide thérapeutique.',
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
            }
            
            // Attach 1-3 random medicines to this protocol
            $numMeds = rand(1, 3);
            $medKeys = array_rand($medicamentIds, $numMeds);
            if (!is_array($medKeys)) $medKeys = [$medKeys];
            
            $protoMedData = [];
            foreach ($medKeys as $key) {
                $medId = $medicamentIds[$key];
                $protoMedData[] = [
                    'uuid' => (string) Str::uuid(),
                    'protocole_id' => $protoId,
                    'medicament_id' => $medId,
                    'type' => rand(0, 1) ? 'principal' : 'alternatif',
                    'posologie' => rand(1, 4) . ' prise(s) par jour',
                    'duree' => rand(5, 14) . ' jours',
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }
            
            DB::table('protocole_medicament')->insert($protoMedData);
            
            $count++;
            $this->command->getOutput()->progressAdvance();
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info("Successfully inserted $count diseases and their protocols!");
    }
}