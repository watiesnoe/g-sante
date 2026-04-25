<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Lie les médicaments aux protocoles de traitement via la table pivot protocole_medicament.
 * Pour chaque protocole, extrait les noms de molécules du champ traitement_principal/alternatif
 * et les associe aux médicaments correspondants en BD.
 * Idempotent : vérifie l'existence avant d'insérer.
 */
class ProtocoleMedicamentLinkSeeder extends Seeder
{
    public function run(): void
    {
        // Mapping manuel : nom exact maladie => [médicaments principaux, médicaments alternatifs]
        $mappings = [
            // ===== PALUDISME =====
            'Paludisme simple'                          => ['p' => ['Artéméther/Luméfantrine'],           'a' => ['Artésunate/Amodiaquine']],
            'Paludisme grave'                           => ['p' => ['Artésunate IV'],                     'a' => ['Quinine']],
            'Paludisme non compliqué (P. falciparum)'  => ['p' => ['Artéméther/Luméfantrine'],           'a' => ['Artésunate/Amodiaquine']],
            'Paludisme grave (hospitalier)'             => ['p' => ['Artésunate IV 60mg'],                'a' => ['Quinine']],
            'Paludisme grave (alternative)'             => ['p' => ['Artéméther IM 80mg'],               'a' => ['Quinine']],
            'Paludisme à P. vivax'                     => ['p' => ['Chloroquine', 'Primaquine 15mg'],    'a' => ['Artéméther/Luméfantrine']],
            'Prophylaxie paludisme (>5 kg)'            => ['p' => ['Méfloquine', 'Atovaquone/Proguanil (Malarone) 250/100mg'], 'a' => ['Doxycycline']],

            // ===== RESPIRATOIRE =====
            'Pneumonie (non grave)'         => ['p' => ['Amoxicilline'],            'a' => ['Cotrimoxazole', 'Azithromycine']],
            'Pneumonie simple'              => ['p' => ['Amoxicilline'],            'a' => ['Cotrimoxazole']],
            'Pneumonie sévère'              => ['p' => ['Ceftriaxone', 'Amoxicilline'], 'a' => ['Céfotaxime']],
            'Pneumopathie bactérienne'      => ['p' => ['Amoxicilline + Acide Clavulanique'], 'a' => ['Ceftriaxone', 'Azithromycine']],
            'Angine streptococcique'        => ['p' => ['Amoxicilline'],            'a' => ['Azithromycine']],
            'Otite moyenne aiguë'           => ['p' => ['Amoxicilline'],            'a' => ['Amoxicilline + Acide Clavulanique']],
            'Coqueluche'                    => ['p' => ['Azithromycine'],           'a' => ['Érythromycine']],
            'Septicémie néonatale'          => ['p' => ['Ampicilline', 'Gentamicine'], 'a' => ['Céfotaxime']],

            // ===== TUBERCULOSE =====
            'Tuberculose (pulmonaire)'                      => ['p' => ['Rifampicine', 'Isoniazide', 'Pyrazinamide', 'Éthambutol'], 'a' => []],
            'Tuberculose pulmonaire'                        => ['p' => ['Rifampicine', 'Isoniazide', 'Pyrazinamide', 'Éthambutol'], 'a' => []],
            'Tuberculose latente'                           => ['p' => ['Isoniazide'],                   'a' => ['Rifampicine']],
            'Tuberculose multirésistante (MDR-TB)'          => ['p' => ['Bédaquiline 100mg', 'Prétomanide 200mg', 'Linézolide 600mg'], 'a' => []],

            // ===== IST =====
            'Syphilis'              => ['p' => ['Pénicilline G Benzathine'],        'a' => ['Doxycycline']],
            'Syphilis primaire'     => ['p' => ['Pénicilline G Benzathine'],        'a' => ['Doxycycline']],
            'Gonococcie (IST)'      => ['p' => ['Ceftriaxone'],                     'a' => ['Azithromycine']],
            'Trachome (conjonctivite chronique)' => ['p' => ['Azithromycine'],     'a' => []],

            // ===== INFECTIONS DIGESTIVES =====
            'Choléra'                  => ['p' => ['Doxycycline'],                  'a' => ['Azithromycine']],
            'Shigellose'               => ['p' => ['Ciprofloxacine'],               'a' => ['Azithromycine']],
            'Typhoïde (fièvre typhoïde)' => ['p' => ['Ciprofloxacine'],            'a' => ['Azithromycine', 'Ceftriaxone']],
            'Typhoïde'                 => ['p' => ['Ciprofloxacine'],               'a' => ['Azithromycine', 'Ceftriaxone']],
            'Amibiase intestinale'     => ['p' => ['Métronidazole'],                'a' => ['Tinidazole']],
            'Giardiase'                => ['p' => ['Métronidazole'],                'a' => ['Tinidazole']],
            'Gastro-entérite aiguë'    => ['p' => ['SRO', 'Zinc sulfate 20mg'],    'a' => ['Métronidazole']],
            'Gastro-entérite'          => ['p' => ['SRO', 'Zinc sulfate 20mg'],    'a' => ['Métronidazole']],

            // ===== URINAIRES =====
            'Cystite aiguë'            => ['p' => ['Ciprofloxacine'],               'a' => ['Cotrimoxazole']],
            'Infection Urinaire (Cystite)' => ['p' => ['Ciprofloxacine'],           'a' => ['Amoxicilline + Acide Clavulanique']],

            // ===== FONGIQUES =====
            'Candidose oropharyngée (muguet)' => ['p' => ['Nystatine', 'Fluconazole'], 'a' => ['Miconazole gel buccal 20mg/g']],
            'Candidose oesophagienne'   => ['p' => ['Fluconazole'],                 'a' => ['Caspofungine IV 50mg']],
            'Candidose systémique'      => ['p' => ['Caspofungine IV 50mg'],        'a' => ['Fluconazole']],
            'Cryptococcose (neuroméningée)' => ['p' => ['Amphotéricine B', 'Flucytosine 500mg (5-FC)'], 'a' => ['Fluconazole']],
            'Dermatophytose cutanée'    => ['p' => ['Terbinafine crème 1%', 'Clotrimazole crème 1%'], 'a' => ['Fluconazole']],
            'Teignes (tinea capitis)'   => ['p' => ['Griséofulvine 125mg'],         'a' => ['Terbinafine']],
            'Sporotrichose'             => ['p' => ['Itraconazole 100mg'],           'a' => []],

            // ===== PARASITOSES =====
            'Ascaridiose (Ascaris)'         => ['p' => ['Albendazole'],             'a' => ['Mébendazole']],
            'Oxyurose'                      => ['p' => ['Albendazole'],             'a' => ['Mébendazole']],
            'Oxyurose (Enterobius)'         => ['p' => ['Albendazole'],             'a' => ['Mébendazole']],
            'Trichocéphalose (Trichuris)'   => ['p' => ['Albendazole'],             'a' => ['Mébendazole']],
            'Téniasis (Taenia)'             => ['p' => ['Praziquantel'],            'a' => ['Niclosamide']],
            'Hyménolépiase (H. nana)'       => ['p' => ['Praziquantel'],            'a' => ['Nitazoxanide 500mg']],
            'Filariose lymphatique'          => ['p' => ['Diéthylcarbamazine (DEC) 100mg', 'Albendazole'], 'a' => ['Ivermectine']],
            'Onchocercose'                  => ['p' => ['Ivermectine'],             'a' => ['Doxycycline']],
            'Schistosomiase'                => ['p' => ['Praziquantel'],            'a' => ['Oxamniquine 250mg']],
            'Fasciolose'                    => ['p' => ['Triclabendazole 250mg'],   'a' => ['Nitazoxanide 500mg']],
            'Leishmaniose viscérale (Kala-azar)' => ['p' => ['Amphotéricine B'],   'a' => ['Méglumine antimoniate (Glucantime) IM']],

            // ===== VIRALES =====
            'Herpès simplex (HSV)'  => ['p' => ['Acyclovir 400mg'],                'a' => ['Valacyclovir 500mg']],
            'Zona (Herpès zoster)'  => ['p' => ['Valacyclovir 1000mg'],            'a' => ['Acyclovir 800mg']],
            'Grippe (H1N1)'         => ['p' => ['Oseltamivir 75mg (Tamiflu)'],     'a' => ['Zanamivir inhalé']],
            'Grippe'                => ['p' => ['Oseltamivir 75mg (Tamiflu)'],     'a' => ['Zanamivir inhalé']],
            'Kératite herpétique'   => ['p' => ['Acyclovir collyre 3%', 'Acyclovir 400mg'], 'a' => ['Valacyclovir 500mg']],
            'VIH/SIDA'              => ['p' => ['Ténofovir/Lamivudine/Dolutégravir (TLD)'], 'a' => ['Ténofovir/Lamivudine/Éfavirenz']],
            'Hépatite B'            => ['p' => ['Ténofovir disoproxil 300mg (TDF)'], 'a' => ['Entécavir 0.5mg']],

            // ===== VIH/PTME =====
            'PTME (prévention mère-enfant)'  => ['p' => ['Ténofovir/Lamivudine/Dolutégravir (TLD)', 'Névirapine 200mg'], 'a' => ['Ténofovir/Lamivudine/Éfavirenz']],
            'VIH (association fixe)'          => ['p' => ['Ténofovir/Lamivudine/Dolutégravir (TLD)'], 'a' => ['Ténofovir/Lamivudine/Éfavirenz']],
            'VIH (boosté par IP)'             => ['p' => ['Darunavir 800mg', 'Ritonavir 100mg'],      'a' => ['Lopinavir/Ritonavir 200/50mg']],
            'VIH (1ère ligne adulte)'          => ['p' => ['Ténofovir/Lamivudine/Dolutégravir (TLD)'], 'a' => ['Ténofovir/Lamivudine/Éfavirenz']],
            'VIH pédiatrique (<25kg)'          => ['p' => ['Abacavir 300mg', 'Lamivudine'],           'a' => ['Lopinavir/Ritonavir 200/50mg']],

            // ===== MÉNINGITE =====
            'Méningite'             => ['p' => ['Ceftriaxone'],                     'a' => ['Céfotaxime']],
            'Méningite bactérienne' => ['p' => ['Ceftriaxone'],                     'a' => ['Céfotaxime']],

            // ===== NEUROLOGIE =====
            'État de mal convulsif'                           => ['p' => ['Diazépam', 'Phénobarbital'], 'a' => ['Lorazépam IV 4mg/2ml']],
            'Épilepsie (crises partielles)'                   => ['p' => ['Carbamazépine'],              'a' => ['Lamotrigine 50mg', 'Lévétiracétam 500mg']],
            'Épilepsie (crises généralisées tonico-cloniques)' => ['p' => ['Valproate de sodium'],       'a' => ['Lamotrigine 100mg']],
            'Épilepsie (crises généralisées)'                 => ['p' => ['Valproate de sodium'],        'a' => ['Lévétiracétam 500mg']],
            'Épilepsie (absence, myoclonies)'                 => ['p' => ['Valproate de sodium'],        'a' => ['Éthosuximide 250mg']],
            'Douleur neuropathique'                           => ['p' => ['Amitriptyline 25mg', 'Gabapentine 300mg'], 'a' => ['Prégabaline 75mg']],
            'Douleur légère à modérée'                        => ['p' => ['Paracétamol'],                'a' => ['Ibuprofène']],
            'Douleur sévère'                                  => ['p' => ['Morphine orale 10mg'],        'a' => ['Tramadol 50mg']],

            // ===== CARDIOVASCULAIRE =====
            'Hypertension artérielle (HTA)' => ['p' => ['Amlodipine', 'Énalapril'], 'a' => ['Hydrochlorothiazide 25mg', 'Bisoprolol 5mg']],
            'Hypertension gravidique'        => ['p' => ['Labétalol 100mg', 'Méthyldopa 250mg'], 'a' => ['Nifédipine']],
            'Insuffisance cardiaque'         => ['p' => ['Énalapril', 'Bisoprolol 5mg', 'Furosémide'], 'a' => ['Sacubitril/Valsartan 97/103mg (Entresto)']],
            'Rhumatisme articulaire aigu'    => ['p' => ['Pénicilline G Benzathine', 'Aspirine'], 'a' => ['Amoxicilline']],
            'Maladie de Kawasaki'            => ['p' => ['Immunoglobulines IV 5g (IgIV)', 'Aspirine'], 'a' => ['Infliximab IV 100mg']],

            // ===== ENDOCRINOLOGIE =====
            'Diabète de type 1'         => ['p' => ['Insuline Rapide (Regular)', 'Insuline NPH (intermédiaire)'], 'a' => []],
            'Diabète de type 2'         => ['p' => ['Metformine'],               'a' => ['Glibenclamide 5mg', 'Insuline NPH (intermédiaire)']],
            'Hypoglycémie sévère'       => ['p' => ['Glucagon IM 1mg', 'Glucose 30% 250ml IV'], 'a' => []],
            'Hypothyroïdie'             => ['p' => ['Lévothyroxine 100µg'],       'a' => []],
            'Insuffisance surrénale (cortisol)'     => ['p' => ['Hydrocortisone 10mg'],   'a' => ['Prednisolone 20mg']],
            'Insuffisance surrénale (aldostérone)'  => ['p' => ['Fludrocortisone 0.1mg'], 'a' => []],

            // ===== ONCOLOGIE / EFFETS =====
            'Nausées / vomissements (post-chimiothérapie)' => ['p' => ['Ondansétron 4mg/2ml IV', 'Dexaméthasone'], 'a' => ['Aprépitant 125mg']],
            'Hyperuricémie (post-chimiothérapie)'          => ['p' => ['Allopurinol 300mg'],                        'a' => ['Rasburicase IV 7.5mg']],
            'Cystite hémorragique (post-cyclophosphamide)' => ['p' => ['MESNA 400mg'],                              'a' => []],
            'Intoxication médicamenteuse aiguë'            => ['p' => ['Charbon activé 50g'],                       'a' => ['Naloxone IV 0.4mg/ml', 'Flumazénil IV 0.5mg', 'N-Acétylcystéine IV 200mg/ml']],
            'Intoxication au paracétamol'                  => ['p' => ['N-Acétylcystéine IV 200mg/ml'],             'a' => []],
            'Intoxication aux organophosphorés'            => ['p' => ['Atropine 1mg/ml IV', 'Pralidoxime IV 1g'],  'a' => []],

            // ===== LÈPRE =====
            'Lèpre paucibacillaire (PB)' => ['p' => ['Rifampicine', 'Dapsone'], 'a' => []],
            'Lèpre multibacillaire (MB)' => ['p' => ['Rifampicine', 'Dapsone', 'Clofazimine'], 'a' => []],

            // ===== PÉDIATRIE =====
            'Hémorragie du nouveau-né (MK)'   => ['p' => ['Vitamine K1 (Phytoménadione) 1mg'], 'a' => []],
            'Diphthérie'                        => ['p' => ['Antitoxine diphtérique', 'Pénicilline G'], 'a' => ['Érythromycine']],
            'Acidose métabolique sévère (pH<7,1)' => ['p' => ['Bicarbonate de sodium 8.4% IV'], 'a' => []],
        ];

        $linked = 0;
        $skipped = 0;
        $notFound = 0;

        // Charger tous les médicaments en mémoire (id => nom_lowercase)
        $allMeds = DB::table('medicaments')->get(['id', 'nom']);

        foreach ($mappings as $nomMaladie => $listes) {
            // Trouver la maladie
            $maladie = DB::table('maladies')->where('nom', $nomMaladie)->first();
            if (!$maladie) {
                // LIKE fallback
                $maladie = DB::table('maladies')
                    ->where('nom', 'LIKE', '%' . mb_substr($nomMaladie, 0, 12) . '%')
                    ->first();
            }
            if (!$maladie) { $notFound++; continue; }

            // Trouver le protocole
            $protocole = DB::table('protocole_traitements')
                ->where('maladie_id', $maladie->id)
                ->first();
            if (!$protocole) { $notFound++; continue; }

            // Traiter les deux types (principal et alternatif)
            foreach (['p' => 'principal', 'a' => 'alternatif'] as $key => $type) {
                foreach ($listes[$key] ?? [] as $nomMed) {
                    // Chercher le médicament par correspondance partielle
                    $medNormalized = mb_strtolower(trim($nomMed));
                    $medicament = null;

                    foreach ($allMeds as $m) {
                        $mNorm = mb_strtolower($m->nom);
                        // Match si le nom du médicament est contenu dans le nom BD ou vice-versa
                        if (str_contains($mNorm, $medNormalized) || str_contains($medNormalized, $mNorm)
                            || similar_text($medNormalized, $mNorm, $percent) && $percent > 65
                        ) {
                            $medicament = $m;
                            break;
                        }
                    }

                    if (!$medicament) { $notFound++; continue; }

                    // Vérifier si le lien existe déjà
                    $exists = DB::table('protocole_medicament')
                        ->where('protocole_id', $protocole->id)
                        ->where('medicament_id', $medicament->id)
                        ->exists();

                    if (!$exists) {
                        DB::table('protocole_medicament')->insert([
                            'uuid'          => (string) Str::uuid(),
                            'protocole_id'  => $protocole->id,
                            'medicament_id' => $medicament->id,
                            'type'          => $type,
                            'posologie'     => 'Voir protocole',
                            'duree'         => 'Selon protocole',
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);
                        $linked++;
                    } else {
                        $skipped++;
                    }
                }
            }
        }

        $this->command->info("✅ Liens protocole-médicament : {$linked} créés, {$skipped} déjà présents, {$notFound} non trouvés.");
        $this->command->info("   Total liens pivot : " . DB::table('protocole_medicament')->count());
    }
}
