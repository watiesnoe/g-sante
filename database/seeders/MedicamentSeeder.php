<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MedicamentSeeder extends Seeder
{
    public function run(): void
    {
        $uniteId = DB::table('unites')->first()?->id ?? 1;
        $familleId = DB::table('familles')->first()?->id ?? 1;
        
        $medicaments = [];
        
        $medicamentsList = [
            // Antibactériens (20)
            'Amoxicilline', 'Co-amoxiclav (Amoxicilline/Acide clavulanique)', 'Ampicilline', 'Benzathine benzylpénicilline', 
            'Benzylpénicilline (Pénicilline G)', 'Phénoxyméthylpénicilline (Pénicilline V)', 'Cloxacilline', 'Céfalexine', 
            'Céfixime', 'Céfotaxime', 'Ceftriaxone', 'Azithromycine', 'Clarithromycine', 'Érythromycine', 
            'Ciprofloxacine', 'Gentamicine', 'Streptomycine', 'Doxycycline', 'Co-trimoxazole (SMX/TMP)', 'Métronidazole',
            
            // Antituberculeux (5)
            'Rifampicine', 'Isoniazide (H)', 'Pyrazinamide (Z)', 'Éthambutol (E)', 'Rifapentine',
            
            // Antipaludiques (8)
            'Artéméther/Luméfantrine (AL)', 'Artésunate/Amodiaquine (AS/AQ)', 'Dihydroartémisinine/Pipéraquine (DHA/PPQ)',
            'Artésunate injectable', 'Quinine', 'Chloroquine', 'Méfloquine', 'Sulfadoxine/Pyriméthamine (SP)',
            
            // Antiviraux VIH (10)
            'Abacavir (ABC)', 'Lamivudine (3TC)', 'Zidovudine (AZT)', 'Ténofovir disoproxil fumarate (TDF)',
            'Dolutégravir (DTG)', 'Éfavirenz (EFV)', 'Névirapine (NVP)', 'Atazanavir (ATV)', 'Darunavir (DRV)', 'Ritonavir (RTV)',
            
            // Antifongiques (8)
            'Fluconazole', 'Itraconazole', 'Amphotéricine B conventionnelle', 'Amphotéricine B liposomale',
            'Flucytosine', 'Griséofulvine', 'Nystatine', 'Miconazole (crème/gel)',
            
            // Antihelminthiques (8)
            'Albendazole', 'Mébendazole', 'Ivermectine', 'Praziquantel', 'Niclosamide', 'Diéthylcarbamazine (DEC)',
            'Triclabendazole', 'Pyrantel',
            
            // Antidépresseurs (5)
            'Fluoxétine', 'Sertraline', 'Paroxétine', 'Amitriptyline', 'Lévétiracétam',
            
            // Antipsychotiques (4)
            'Halopéridol', 'Chlorpromazine', 'Olanzapine', 'Rispéridone',
            
            // Antiépileptiques (4)
            'Carbamazépine', 'Phénobarbital', 'Phénytoïne', 'Valproate de sodium',
            
            // Antihypertenseurs (8)
            'Énalapril', 'Amlodipine', 'Labétalol', 'Méthyldopa', 'Bisoprolol', 'Hydralazine', 'Nifédipine', 'Furosémide',
            
            // Antidiabétiques (4)
            'Metformine', 'Insuline rapide', 'Insuline intermédiaire', 'Insuline biphasique', 'Glibenclamide', 'Gliclazide',
            
            // Corticoïdes (4)
            'Dexaméthasone', 'Hydrocortisone', 'Prednisolone', 'Béclométasone (inhalé)',
            
            // AINS/Antalgiques (6)
            'Paracétamol', 'Ibuprofène', 'Diclofénac', 'Morphine LP', 'Codéine', 'Tramadol',
            
            // Antihistaminiques (2)
            'Prométhazine', 'Loratadine',
            
            // Antispasmodiques (3)
            'Butylscopolamine', 'Atropine', 'Bipéridène',
            
            // Contraceptifs (3)
            'Éthinylestradiol/Lévonorgestrel', 'Lévonorgestrel', 'Médroxyprogestérone injectable',
        ];
        
        foreach ($medicamentsList as $medicament) {
            $medicaments[] = [
                'uuid' => (string) Str::uuid(),
                'nom' => $medicament,
                'description' => 'Médicament essentiel selon la liste OMS et le guide MSF',
                'unite_id' => $uniteId,
                'famille_id' => $familleId,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        
        DB::table('medicaments')->insert($medicaments);
    }
}