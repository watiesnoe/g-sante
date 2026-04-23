<?php

namespace Database\Seeders;
use Illuminate\Support\Str;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProtocoleMedicamentSeeder extends Seeder
{
    public function run(): void
    {
        // Récupération des IDs des protocoles et médicaments
        $protocoleIds = DB::table('protocole_traitements')->pluck('id', 'titre')->toArray();
        $medicamentIds = DB::table('medicaments')->pluck('id', 'nom')->toArray();
        
        $protocoleMedicaments = [];
        
        // ==================== PALUDISME ====================
        
        // Protocole: Traitement du paludisme non compliqué à P. falciparum
        if (isset($protocoleIds['Traitement du paludisme non compliqué à P. falciparum'])) {
            $protocoleId = $protocoleIds['Traitement du paludisme non compliqué à P. falciparum'];
            
            // Artéméther/Luméfantrine (traitement principal)
            if (isset($medicamentIds['Artéméther/Luméfantrine 20/120mg'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Artéméther/Luméfantrine 20/120mg'],
                    'type' => 'principal',
                    'posologie' => 'Selon poids : 5-15kg: 1 cp/prise; 15-25kg: 2 cp/prise; 25-35kg: 3 cp/prise; ≥35kg: 4 cp/prise, 2x/jour pendant 3 jours',
                    'duree' => '3 jours',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
            
            // Artésunate/Amodiaquine (traitement alternatif)
            if (isset($medicamentIds['Artésunate/Amodiaquine 50/135mg'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Artésunate/Amodiaquine 50/135mg'],
                    'type' => 'alternatif',
                    'posologie' => 'Selon poids : 4,5-9kg: 1 cp 25/67,5mg; 9-18kg: 1 cp 50/135mg; 18-36kg: 1 cp 100/270mg; ≥36kg: 2 cp 100/270mg, 1x/jour pendant 3 jours',
                    'duree' => '3 jours',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
        }
        
        // Protocole: Traitement du paludisme grave
        if (isset($protocoleIds['Traitement du paludisme grave (après artésunate IV)'])) {
            $protocoleId = $protocoleIds['Traitement du paludisme grave (après artésunate IV)'];
            
            // Quinine (traitement alternatif)
            if (isset($medicamentIds['Quinine 300mg'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Quinine 300mg'],
                    'type' => 'alternatif',
                    'posologie' => 'Adulte: 600mg 3x/jour; Enfant: 10mg/kg 3x/jour',
                    'duree' => '7 jours',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
        }
        
        // ==================== INFECTIONS RESPIRATOIRES ====================
        
        // Protocole: Pneumonie sans signes de gravité
        if (isset($protocoleIds['Pneumonie sans signes de gravité'])) {
            $protocoleId = $protocoleIds['Pneumonie sans signes de gravité'];
            
            // Amoxicilline (traitement principal)
            if (isset($medicamentIds['Amoxicilline 500mg'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Amoxicilline 500mg'],
                    'type' => 'principal',
                    'posologie' => 'Adulte: 1g 3x/jour; Enfant: 30mg/kg 3x/jour',
                    'duree' => '5 jours',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
            
            // Co-trimoxazole (traitement alternatif)
            if (isset($medicamentIds['Co-trimoxazole 800/160mg'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Co-trimoxazole 800/160mg'],
                    'type' => 'alternatif',
                    'posologie' => 'Adulte: 800/160mg 2x/jour; Enfant: 20/4mg/kg 2x/jour',
                    'duree' => '5 jours',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
        }
        
        // Protocole: Pneumonie sévère
        if (isset($protocoleIds['Pneumonie sévère (hospitalisation)'])) {
            $protocoleId = $protocoleIds['Pneumonie sévère (hospitalisation)'];
            
            // Ceftriaxone (traitement principal)
            if (isset($medicamentIds['Ceftriaxone 1g injectable'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Ceftriaxone 1g injectable'],
                    'type' => 'principal',
                    'posologie' => '50-100mg/kg/jour IV',
                    'duree' => '3-5 jours puis relais oral',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
        }
        
        // ==================== HYPERTENSION ARTÉRIELLE ====================
        
        // Protocole: Traitement de l'hypertension artérielle essentielle
        if (isset($protocoleIds['Traitement de l\'hypertension artérielle essentielle'])) {
            $protocoleId = $protocoleIds['Traitement de l\'hypertension artérielle essentielle'];
            
            // Amlodipine (traitement principal)
            if (isset($medicamentIds['Amlodipine 5mg'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Amlodipine 5mg'],
                    'type' => 'principal',
                    'posologie' => '5-10mg 1x/jour',
                    'duree' => 'à vie',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
            
            // Énalapril (traitement principal)
            if (isset($medicamentIds['Énalapril 5mg'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Énalapril 5mg'],
                    'type' => 'principal',
                    'posologie' => '5-20mg 1-2x/jour',
                    'duree' => 'à vie',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
            
            // Hydrochlorothiazide (traitement alternatif)
            if (isset($medicamentIds['Hydrochlorothiazide 25mg'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Hydrochlorothiazide 25mg'],
                    'type' => 'alternatif',
                    'posologie' => '12,5-25mg 1x/jour',
                    'duree' => 'à vie',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
        }
        
        // Protocole: Hypertension gravidique
        if (isset($protocoleIds['Hypertension gravidique / Pré-éclampsie'])) {
            $protocoleId = $protocoleIds['Hypertension gravidique / Pré-éclampsie'];
            
            // Labétalol (traitement principal)
            if (isset($medicamentIds['Labétalol 100mg'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Labétalol 100mg'],
                    'type' => 'principal',
                    'posologie' => '100-200mg 2x/jour',
                    'duree' => 'selon grossesse',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
            
            // Méthyldopa (traitement principal)
            if (isset($medicamentIds['Méthyldopa 250mg'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Méthyldopa 250mg'],
                    'type' => 'principal',
                    'posologie' => '250mg 2-3x/jour jusqu\'à 1,5g/jour',
                    'duree' => 'selon grossesse',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
        }
        
        // ==================== DIABÈTE ====================
        
        // Protocole: Traitement du diabète de type 2
        if (isset($protocoleIds['Traitement du diabète de type 2'])) {
            $protocoleId = $protocoleIds['Traitement du diabète de type 2'];
            
            // Metformine (traitement principal)
            if (isset($medicamentIds['Metformine 500mg'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Metformine 500mg'],
                    'type' => 'principal',
                    'posologie' => '500mg 1x/jour (S1) puis 500mg 2x/jour (S2) puis 1g 2x/jour',
                    'duree' => 'à vie',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
            
            // Glibenclamide (traitement alternatif)
            if (isset($medicamentIds['Glibenclamide 5mg'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Glibenclamide 5mg'],
                    'type' => 'alternatif',
                    'posologie' => '2,5-5mg 1-2x/jour',
                    'duree' => 'à vie',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
        }
        
        // ==================== GASTRO-ENTÉRITE ====================
        
        // Protocole: Traitement de la gastro-entérite aiguë
        if (isset($protocoleIds['Traitement de la gastro-entérite aiguë'])) {
            $protocoleId = $protocoleIds['Traitement de la gastro-entérite aiguë'];
            
            // Zinc (traitement principal)
            if (isset($medicamentIds['Zinc sulfate 20mg'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Zinc sulfate 20mg'],
                    'type' => 'principal',
                    'posologie' => '20mg/jour (10mg si <6 mois)',
                    'duree' => '10 jours',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
            
            // Métronidazole (traitement alternatif)
            if (isset($medicamentIds['Métronidazole 500mg'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Métronidazole 500mg'],
                    'type' => 'alternatif',
                    'posologie' => 'Adulte: 500mg 3x/jour; Enfant: 15mg/kg 3x/jour',
                    'duree' => '5-7 jours',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
        }
        
        // ==================== TYPHOÏDE ====================
        
        // Protocole: Traitement de la fièvre typhoïde
        if (isset($protocoleIds['Traitement de la fièvre typhoïde'])) {
            $protocoleId = $protocoleIds['Traitement de la fièvre typhoïde'];
            
            // Ciprofloxacine (traitement principal)
            if (isset($medicamentIds['Ciprofloxacine 500mg'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Ciprofloxacine 500mg'],
                    'type' => 'principal',
                    'posologie' => 'Adulte: 500mg 2x/jour; Enfant: 15mg/kg 2x/jour',
                    'duree' => '10-14 jours',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
            
            // Azithromycine (traitement alternatif)
            if (isset($medicamentIds['Azithromycine 500mg'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Azithromycine 500mg'],
                    'type' => 'alternatif',
                    'posologie' => '1g J1 puis 500mg/jour J2-J7',
                    'duree' => '7 jours',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
        }
        
        // ==================== DRÉPANOCYTOSE ====================
        
        // Protocole: Prévention des infections chez le drépanocytaire
        if (isset($protocoleIds['Prévention des infections chez le drépanocytaire'])) {
            $protocoleId = $protocoleIds['Prévention des infections chez le drépanocytaire'];
            
            // Amoxicilline (traitement principal)
            if (isset($medicamentIds['Amoxicilline 500mg'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Amoxicilline 500mg'],
                    'type' => 'principal',
                    'posologie' => 'Enfant <3 ans: 125mg 2x/jour; 3-5 ans: 250mg 2x/jour; >5 ans: 500mg 2x/jour',
                    'duree' => 'jusqu\'à 5 ans minimum',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
        }
        
        // Protocole: Traitement de la crise vaso-occlusive drépanocytaire
        if (isset($protocoleIds['Traitement de la crise vaso-occlusive drépanocytaire'])) {
            $protocoleId = $protocoleIds['Traitement de la crise vaso-occlusive drépanocytaire'];
            
            // Paracétamol (traitement principal)
            if (isset($medicamentIds['Paracétamol 500mg'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Paracétamol 500mg'],
                    'type' => 'principal',
                    'posologie' => '1g 4x/jour',
                    'duree' => 'selon douleur',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
            
            // Ibuprofène (traitement principal)
            if (isset($medicamentIds['Ibuprofène 400mg'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Ibuprofène 400mg'],
                    'type' => 'principal',
                    'posologie' => '400mg 3x/jour',
                    'duree' => 'selon douleur',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
            
            // Morphine (traitement alternatif)
            if (isset($medicamentIds['Morphine LI 10mg'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Morphine LI 10mg'],
                    'type' => 'alternatif',
                    'posologie' => '10mg toutes les 4h selon douleur',
                    'duree' => 'selon douleur',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
        }
        
        // ==================== VIH/SIDA ====================
        
        // Protocole: Traitement antirétroviral de première ligne
        if (isset($protocoleIds['Traitement antirétroviral de première ligne (adulte)'])) {
            $protocoleId = $protocoleIds['Traitement antirétroviral de première ligne (adulte)'];
            
            // Ténofovir (traitement principal)
            if (isset($medicamentIds['Ténofovir 300mg'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Ténofovir 300mg'],
                    'type' => 'principal',
                    'posologie' => '300mg 1x/jour',
                    'duree' => 'à vie',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
            
            // Lamivudine (traitement principal)
            if (isset($medicamentIds['Lamivudine 150mg'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Lamivudine 150mg'],
                    'type' => 'principal',
                    'posologie' => '300mg 1x/jour (2 cp)',
                    'duree' => 'à vie',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
            
            // Dolutégravir (traitement principal)
            if (isset($medicamentIds['Dolutégravir 50mg'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Dolutégravir 50mg'],
                    'type' => 'principal',
                    'posologie' => '50mg 1x/jour',
                    'duree' => 'à vie',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
            
            // Éfavirenz (traitement alternatif)
            if (isset($medicamentIds['Éfavirenz 600mg'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Éfavirenz 600mg'],
                    'type' => 'alternatif',
                    'posologie' => '600mg 1x/jour',
                    'duree' => 'à vie',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
        }
        
        // ==================== MÉNINGITE ====================
        
        // Protocole: Traitement de la méningite bactérienne
        if (isset($protocoleIds['Traitement de la méningite bactérienne'])) {
            $protocoleId = $protocoleIds['Traitement de la méningite bactérienne'];
            
            // Ceftriaxone (traitement principal)
            if (isset($medicamentIds['Ceftriaxone 1g injectable'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Ceftriaxone 1g injectable'],
                    'type' => 'principal',
                    'posologie' => 'Adulte: 2g 2x/jour IV; Enfant: 50-100mg/kg 2x/jour IV',
                    'duree' => '7-14 jours',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
            
            // Dexaméthasone (traitement adjuvant)
            if (isset($medicamentIds['Dexaméthasone 4mg'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Dexaméthasone 4mg'],
                    'type' => 'adjuvant',
                    'posologie' => '0,15-0,6mg/kg/jour avant la première dose d\'antibiotique',
                    'duree' => '2-4 jours',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
        }
        
        // ==================== HÉPATITE B ====================
        
        // Protocole: Traitement de l'hépatite B chronique
        if (isset($protocoleIds['Traitement de l\'hépatite B chronique'])) {
            $protocoleId = $protocoleIds['Traitement de l\'hépatite B chronique'];
            
            // Ténofovir (traitement principal)
            if (isset($medicamentIds['Ténofovir 300mg'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Ténofovir 300mg'],
                    'type' => 'principal',
                    'posologie' => '300mg 1x/jour',
                    'duree' => 'à vie',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
            
            // Lamivudine (traitement alternatif)
            if (isset($medicamentIds['Lamivudine 150mg'])) {
                $protocoleMedicaments[] = [
                    'protocole_id' => $protocoleId,
                    'medicament_id' => $medicamentIds['Lamivudine 150mg'],
                    'type' => 'alternatif',
                    'posologie' => '100mg 1x/jour',
                    'duree' => 'à vie',
                    'uuid' => (string) Str::uuid(), 'created_at' => now(),
                    'uuid' => (string) Str::uuid(), 'updated_at' => now()
                ];
            }
        }
        
        DB::table('protocole_medicament')->insert($protocoleMedicaments);
    }
}