<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Unite;
use App\Models\Famille;
use App\Models\Maladie;

class MedicamentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Starting massive generation of 20000 medications...');

        // Ensure we have unites
        if (Unite::count() == 0) {
            $unites = ['Boîte', 'Plaquette', 'Flacon', 'Ampoule', 'Tube', 'Sachet', 'Carton'];
            foreach ($unites as $u) {
                Unite::create(['nom' => $u, 'abreviation' => substr($u, 0, 3), 'uuid' => (string) Str::uuid()]);
            }
        }
        $uniteIds = Unite::pluck('id')->toArray();

        // Ensure we have familles
        if (Famille::count() == 0) {
            $familles = ['Antalgiques', 'Antibiotiques', 'Antiparasitaires', 'Anti-inflammatoires', 'Vitamines', 'Antiseptiques', 'Solutés de perfusion'];
            foreach ($familles as $f) {
                Famille::create(['nom' => $f, 'uuid' => (string) Str::uuid()]);
            }
        }
        $familleIds = Famille::pluck('id')->toArray();

        // Ensure we have maladies
        if (Maladie::count() == 0) {
            $maladies = ['Paludisme', 'Typhoïde', 'Grippe', 'Diabète', 'Hypertension', 'Tuberculose', 'VIH/SIDA', 'Asthme', 'Choléra', 'Anémie'];
            foreach ($maladies as $m) {
                Maladie::create(['nom' => $m, 'uuid' => (string) Str::uuid()]);
            }
        }
        $maladieIds = Maladie::pluck('id')->toArray();

        // Load JSON
        $jsonPath = base_path('medicines_data.json');
        $baseMedicines = [];
        if (file_exists($jsonPath)) {
            $baseMedicinesData = json_decode(file_get_contents($jsonPath), true);
            foreach ($baseMedicinesData as $med) {
                $baseMedicines[] = $med['nom'];
            }
        }

        // Add the basic essential medicines from previous seeder
        $essentialMedicines = [
            'Amoxicilline', 'Co-amoxiclav (Amoxicilline/Acide clavulanique)', 'Ampicilline', 'Benzathine benzylpénicilline', 
            'Benzylpénicilline (Pénicilline G)', 'Phénoxyméthylpénicilline (Pénicilline V)', 'Cloxacilline', 'Céfalexine', 
            'Céfixime', 'Céfotaxime', 'Ceftriaxone', 'Azithromycine', 'Clarithromycine', 'Érythromycine', 
            'Ciprofloxacine', 'Gentamicine', 'Streptomycine', 'Doxycycline', 'Co-trimoxazole (SMX/TMP)', 'Métronidazole',
            'Rifampicine', 'Isoniazide (H)', 'Pyrazinamide (Z)', 'Éthambutol (E)', 'Rifapentine',
            'Artéméther/Luméfantrine (AL)', 'Artésunate/Amodiaquine (AS/AQ)', 'Dihydroartémisinine/Pipéraquine (DHA/PPQ)',
            'Artésunate injectable', 'Quinine', 'Chloroquine', 'Méfloquine', 'Sulfadoxine/Pyriméthamine (SP)',
            'Abacavir (ABC)', 'Lamivudine (3TC)', 'Zidovudine (AZT)', 'Ténofovir disoproxil fumarate (TDF)',
            'Dolutégravir (DTG)', 'Éfavirenz (EFV)', 'Névirapine (NVP)', 'Atazanavir (ATV)', 'Darunavir (DRV)', 'Ritonavir (RTV)',
            'Fluconazole', 'Itraconazole', 'Amphotéricine B conventionnelle', 'Amphotéricine B liposomale',
            'Flucytosine', 'Griséofulvine', 'Nystatine', 'Miconazole (crème/gel)',
            'Albendazole', 'Mébendazole', 'Ivermectine', 'Praziquantel', 'Niclosamide', 'Diéthylcarbamazine (DEC)',
            'Triclabendazole', 'Pyrantel',
            'Fluoxétine', 'Sertraline', 'Paroxétine', 'Amitriptyline', 'Lévétiracétam',
            'Halopéridol', 'Chlorpromazine', 'Olanzapine', 'Rispéridone',
            'Carbamazépine', 'Phénobarbital', 'Phénytoïne', 'Valproate de sodium',
            'Énalapril', 'Amlodipine', 'Labétalol', 'Méthyldopa', 'Bisoprolol', 'Hydralazine', 'Nifédipine', 'Furosémide',
            'Metformine', 'Insuline rapide', 'Insuline intermédiaire', 'Insuline biphasique', 'Glibenclamide', 'Gliclazide',
            'Dexaméthasone', 'Hydrocortisone', 'Prednisolone', 'Béclométasone (inhalé)',
            'Paracétamol', 'Ibuprofène', 'Diclofénac', 'Morphine LP', 'Codéine', 'Tramadol',
            'Prométhazine', 'Loratadine',
            'Butylscopolamine', 'Atropine', 'Bipéridène',
            'Éthinylestradiol/Lévonorgestrel', 'Lévonorgestrel', 'Médroxyprogestérone injectable',
        ];

        // Merge lists and remove duplicates
        $allBaseMedicines = array_unique(array_merge($baseMedicines, $essentialMedicines));

        $totalTarget = 20000;
        $chunkSize = 1000;
        $insertedMedCount = 0;
        
        $faker = \Faker\Factory::create('fr_FR');
        $now = now();

        $variations = ['500 mg', '1 g', '200 mg', '250 mg', '100 mg', '50 mg', 'Sirop 125mg/5ml', 'Injectable', 'Suppositoire', 'Gélule', 'Comprimé'];

        $this->command->getOutput()->progressStart($totalTarget);

        while ($insertedMedCount < $totalTarget) {
            $medsData = [];
            $currentChunk = min($chunkSize, $totalTarget - $insertedMedCount);

            for ($i = 0; $i < $currentChunk; $i++) {
                // Pick a real name and add a random variation
                $baseMed = $allBaseMedicines[array_rand($allBaseMedicines)];
                // Clean newline from baseMed
                $baseMed = trim(preg_replace('/\s+/', ' ', $baseMed));
                
                // Add variation to make it unique-ish
                $suffix = $variations[array_rand($variations)];
                $fullName = substr($baseMed, 0, 100) . ' - ' . $suffix;
                
                $prixAchat = rand(5, 50) * 100; // de 500 à 5000 CFA
                $prixVente = ceil(($prixAchat * 1.4) / 25) * 25; // Marge 40% arrondie au 25 CFA sup
                
                $medsData[] = [
                    'uuid' => (string) Str::uuid(),
                    'nom' => $fullName,
                    'description' => 'Médicament essentiel fusionné. ' . $faker->sentence(),
                    'stock' => rand(10, 500),
                    'stock_min' => rand(5, 50),
                    'prix_achat' => $prixAchat,
                    'prix_vente' => $prixVente,
                    'unite_id' => $uniteIds[array_rand($uniteIds)],
                    'famille_id' => $familleIds[array_rand($familleIds)],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('medicaments')->insert($medsData);
            
            // Get the IDs of the newly inserted meds to attach to protocols
            $lastIds = DB::table('medicaments')->orderBy('id', 'desc')->take($currentChunk)->pluck('id')->toArray();
            
            // Create some random protocols
            $protoData = [];
            $protoMedData = [];
            
            $numProtocols = 50; 
            for ($p = 0; $p < $numProtocols; $p++) {
                $protoUuid = (string) Str::uuid();
                
                $newMaladieId = DB::table('maladies')->insertGetId([
                    'uuid' => (string) Str::uuid(),
                    'nom' => 'Maladie associée au traitement ' . $faker->word . ' ' . rand(1, 100000),
                    'created_at' => $now,
                    'updated_at' => $now
                ]);

                $protoId = DB::table('protocole_traitements')->insertGetId([
                    'uuid' => $protoUuid,
                    'maladie_id' => $newMaladieId,
                    'titre' => 'Protocole MSF/Haiti ' . rand(100, 9999),
                    'signes' => $faker->sentence(),
                    'diagnostics' => $faker->sentence(),
                    'germes_nourrisson' => 'Streptococcus, E. Coli',
                    'germes_adulte' => 'Staphylococcus',
                    'traitement_principal' => 'Repose sur l\'administration de ' . $allBaseMedicines[array_rand($allBaseMedicines)],
                    'posologie_principale' => '1 prise 3 fois par jour',
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
                
                $medsToAttach = array_rand(array_flip($lastIds), rand(1, 3));
                if (!is_array($medsToAttach)) $medsToAttach = [$medsToAttach];
                
                foreach ($medsToAttach as $medId) {
                    $protoMedData[] = [
                        'uuid' => (string) Str::uuid(),
                        'protocole_id' => $protoId,
                        'medicament_id' => $medId,
                        'type' => rand(0, 1) ? 'principal' : 'alternatif',
                        'posologie' => rand(1, 3) . ' comprimés/jour',
                        'duree' => rand(3, 14) . ' jours',
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                }
            }
            
            DB::table('protocole_medicament')->insert($protoMedData);

            $insertedMedCount += $currentChunk;
            $this->command->getOutput()->progressAdvance($currentChunk);
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info('Successfully inserted ' . $totalTarget . ' medications and their protocols!');
    }
}