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
                ->where('medicament_id',$medicamentId)
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
        
        // ENRICHISSEMENT: Ajout des guidelines OMS du guideline-339
        // Ces protocoles sont ajoutés directement en base pour éviter la redondance
        $this->enrichWithPdfGuidelines($now);
        
        // ENRICHISSEMENT: Ajout du Guide Thérapeutique de Terrain issu de public/index.html
        $this->enrichWithGuideTherapeutique($now);
        
        $this->command->info('WHO Guidelines seeded successfully with standardized treatments!');
    }
    
    /**
     * Enrichit la base avec les recommandations OMS issues du guideline-339
     * Guide pratique des médicaments essentiels OMS - 2024
     */
    private function enrichWithPdfGuidelines($now)
    {
        $defaultUniteId = DB::table('unites')->value('id') ?? 1;
        
        // Ligne directrices OMS - Paludisme
        $whoMaladies = [
            // ===== PALUDISME =====
            ['nom' => 'Paludisme non compliqué (P. falciparum)', 'description' => 'Recommandation OMS: ACT (Artéméther/Luméfantrine ou Artésunate/Amodiaquine). Test diagnostic obligatoire (TDR/GE).'],
            ['nom' => 'Paludisme grave', 'description' => 'Recommandation OMS: Artésunate IV (ou Artéméther IM ou Quinine). Hospitalisation obligatoire. Surveillance glycémie.'],
            ['nom' => 'Paludisme à P. vivax', 'description' => 'Recommandation OMS: Chloroquine + Primaquine (guérison radicale). Tester G6PD avant primaquine.'],
            ['nom' => 'Paludisme chez la femme enceinte', 'description' => 'Recommandation OMS: Pas d\'ACT au 1er trimestre. Quinine + Clindamycine. MII pendant la grossesse.'],
            ['nom' => 'Paludisme saisonnier chez l\'enfant (SMC)', 'description' => 'Recommandation OMS: SPC (Sulfadoxine-Pyriméthamine + Amodiaquine) mensuelle en zone de transmission saisonnière.'],
            
            // ===== TUBERCULOSE =====
            ['nom' => 'Tuberculose pulmonaire (1ère ligne)', 'description' => 'Recommandation OMS 2023: Schéma RHZE 2 mois puis RH 4 mois (6 mois). DOTS recommandé. Surveiller FH.'],
            ['nom' => 'Tuberculose multirésistante (MDR-TB)', 'description' => 'Recommandation OMS: Schéma BPaL (Bédaquiline + Prétomanide + Linézolide) 6-9 mois. Nouveau gold standard.'],
            ['nom' => 'Tuberculose latente', 'description' => 'Recommandation OMS: Isoniazide 5mg/kg/jour pendant 6-12 mois. Alternative: Rifampicine + Isoniazide 3 mois.'],
            
            // ===== VIH/ARV =====
            ['nom' => 'VIH adulte 1ère ligne (TLD)', 'description' => 'Recommandation OMS 2023: Ténofovir + Lamivudine + Dolutégravir (TLD) 1cp/jour. Objectif CV < 50 copies/mL.'],
            ['nom' => 'VIH adulte 2ème ligne', 'description' => 'Recommandation OMS: DRV/r + 2 INTI actifs. Test résistance avant changement. Renforcer observance.'],
            ['nom' => 'VIH pédiatrique', 'description' => 'Recommandation OMS: ABC + 3TC + DTG (enfant > 1 mois et > 3kg). Objectif CV < 50 copies/mL.'],
            ['nom' => 'PTME / Prevention of mother-to-child transmission', 'description' => 'Recommandation OMS: TAR pour toutes les femmes enceintes VIH+. Allaitement encouragé si TAR. NVP nourrisson.'],
            ['nom' => 'Prophylaxie post-exposition VIH', 'description' => 'Recommandation OMS: TLD pendant 28 jours. Début ASAP après exposition (< 72h). Suivi sérologique.'],
            
            // ===== HÉPATITES =====
            ['nom' => 'Hépatite B chronique', 'description' => 'Recommandation OMS: Ténofovir (TDF) 1mg/jour ou Entécavir 0,5mg/jour. Traitement à vie. Dépistage HCC.'],
            ['nom' => 'Hépatite C chronique', 'description' => 'Recommandation OMS: SOF/VEL (Sofosbuvir/Velpatasvir) 12 semaines. Antiviraux d\'action directe (AAD). Guérison > 95%.'],
            
            // ===== PNEUMONIE INFANTILE =====
            ['nom' => 'Pneumonie infantile (IMCI)', 'description' => 'Recommandation OMS: Amoxicilline 40mg/kg 2x/jour pendant 5 jours. Oxymétrie. Référence si signes de gravité.'],
            ['nom' => 'Bronchiolite (nourrisson)', 'description' => 'Recommandation OMS: Soins de support (hydration, nasal clearance). Pas d\'antibiotiques sauf surinfection bactérienne.'],
            
            // ===== DIARRHÉES =====
            ['nom' => 'Diarrhée aiguë de l\'enfant', 'description' => 'Recommandation OMS: SRO (ORS) + Zinc 20mg/jour pendant 10 jours. Ne pas utiliser anti-diarrhéiques ni antibiotiques systématiques.'],
            ['nom' => 'Diarrhée sanglante / dysenterie', 'description' => 'Recommandation OMS: Ciprofloxacine 15mg/kg 2x/jour pendant 3 jours (shigellose). SRO + Zinc.'],
            
            // ===== GRIPPE =====
            ['nom' => 'Grippe saisonnière', 'description' => 'Recommandation OMS: Oseltamivir 75mg 2x/jour pendant 5 jours si < 48h de symptômes. Vaccination annuelle si groupes à risque.'],
            
            // ===== ANTIBIOTHÉRAPIE =====
            ['nom' => 'Méningite bactérienne (adultes)', 'description' => 'Recommandation OMS: Ceftriaxone 2g 2x/jour IV + Dexaméthasone 0,15mg/kg q6h avant 1ère dose AB. 7-14 jours.'],
            ['nom' => 'Méningite néonatale', 'description' => 'Recommandation OMS: Ampicilline + Céfotaxime. 14-21 jours. Dépistage méningite si sepsis.'],
            ['nom' => 'Infections sexuellement transmissibles', 'description' => 'Recommandation OMS: Syphilis: Benzathine pénicilline G; Gonorrhée: Ceftriaxone 500mg IM + Azithromycine 1g.'],
            ['nom' => 'Infections urinaires (IU)', 'description' => 'Recommandation OMS: Cystite: Nitrofurantoïne 100mg 2x/5j ou Fosfomycine 3g dose unique. Pyélonéphrite: Ciprofloxacine 7j.'],
            ['nom' => 'Choléra', 'description' => 'Recommandation OMS: SRO optimisée (ORS à haute osmolarité). Réhydratation IV (Ringer Lactate) si déshydratation sévère. Antibiothérapie non systématique.'],
            
            // ===== MALADIES TROPICALES NÉGLIGÉES =====
            ['nom' => 'Leishmaniose viscérale', 'description' => 'Recommandation OMS: Amphotéricine B liposomale 10mg/kg total. Schéma SHORT (10mg/kg J1, J2, J3, J7, J14, J21). Efficacité > 95%.'],
            ['nom' => 'Schistosomiase (bilharziose)', 'description' => 'Recommandation OMS: Praziquantel 40-60mg/kg (dose unique ou répartie). Programme MDA en zones endémiques.'],
            ['nom' => 'Filariose lymphatique', 'description' => 'Recommandation OMS: MDA (DEC + Albendazole ou Ivermectine + Albendazole) annuelle pendant 5 ans minimum.'],
            ['nom' => 'Onchocercose (cécité des rivières)', 'description' => 'Recommandation OMS: Ivermectine 150µg/kg 1-2x/an à vie. Contrôle vectoriel. Surveiller Mazzotti.'],
            ['nom' => 'Gale', 'description' => 'Recommandation OMS: Perméthrine 5% crème. Traitement de masse si prévalence > 10%. Ivermectine orale alternative.'],
            
            // ===== DIABÈTE / MNT =====
            ['nom' => 'Diabète de type 2', 'description' => 'Recommandation OMS 2023: Metformine 500mg → 1g 2x/jour. Ajout sulfamide si non contrôlé. Insuline si échec OAD. Automonitoring.'],
            ['nom' => 'Diabète gestationnel', 'description' => 'Recommandation OMS: Mesures hygiéno-diététiques en 1ère intention. Insuline si glycémie non contrôlée. HBPG 75g.'],
            ['nom' => 'Hypertension (adultes)', 'description' => 'Recommandation OMS 2023: Traitement si PA ≥ 140/90. Objectif < 130/80 si risque CV élevé. Monothérapie possible si PA < 160/100.'],
            ['nom' => 'Asthme', 'description' => 'Recommandation OMS/GINA: Budésonide inhalé (contrôle) + Salbutamol (secours). Pas d\'ARB seuls. Step-up/down selon contrôle.'],
            
            // ===== NUTRITION =====
            ['nom' => 'Malnutrition aiguë sévère (MAS)', 'description' => 'Recommandation OMS: Aliments thérapeutiques prêts à l\'emploi (ATPE). Amoxicilline 5j. Renutrition progressive.'],
            ['nom' => 'Carence en vitamine A', 'description' => 'Recommandation OMS: Vitamine A 200 000 UI dose unique (enfant 6-59 mois). Répéter tous les 6 mois en zone endémique.'],
            ['nom' => 'Anémie ferriprive', 'description' => 'Recommandation OMS: Sulfate ferreux 60mg/jour (femme enceinte). 3-6 mois. Associations fer/acide folique. Prise à jeun.'],
            
            // ===== SANTÉ MATERNELLE =====
            ['nom' => 'Pré-éclampsie / Éclampsie', 'description' => 'Recommandation OMS: Sulfate de magnésium (protocole Pritchard). Labétalol IV ou Méthyldopa. Accouchement < 48h si possible.'],
            ['nom' => 'Hémorragie du post-partum', 'description' => 'Recommandation OMS: Oxytocine 10UI IM/IV. Misoprostol 800µg sublingual si oxytocin indisponible. Référence urgente.'],
            ['nom' => 'Infection du post-partum', 'description' => 'Recommandation OMS: Ampicilline + Gentamicine + Métronidazole (ou clindamycine). 7-10 jours. Lavage péritonéal si péritonite.'],
            ['nom' => 'Avortement incomplet', 'description' => 'Recommandation OMS: Misoprostol 600µg sublingual ou MVA. Antibioprophylaxie si fièvre ou infection. Rh: immunoglobuline anti-D.'],
            
            // ===== PÉDIATRIE =====
            ['nom' => 'Syndrome néphrotique (enfant)', 'description' => 'Recommandation OMS: Corticoïdes (Prednisolone) 60mg/m²/jour 4-6 semaines puis dégressif. Référence si stéroïde-résistant.'],
            ['nom' => 'Méningite virale', 'description' => 'Recommandation OMS: Soins de support. Pas d\'antiviraux systématiques (sauf HSV: Aciclovir). Surveillance clinique.'],
            
            // ===== CANCÉROLOGIE / PALIATIFS =====
            ['nom' => 'Cancers courants (soins palliatifs)', 'description' => 'Recommandation OMS: Accès aux opioïdes (morphine orale). Traitement de la douleur selon échelles. Soins de support.'],
            ['nom' => 'Troubles mentaux courants', 'description' => 'Recommandation OMS: Dépression: amitriptyline ou fluoxétine. Schizophrénie: halopéridol ou chlorpromazine. Thiazide diuretique.'],
            
            // ===== URGENCES / INTOXICATIONS =====
            ['nom' => 'Intoxication au paracétamol', 'description' => 'Recommandation OMS: N-Acétylcystéine IV (150mg/kg/1h puis 50mg/kg/4h puis 100mg/kg/16h). Indication si > 150mg/L à H4.'],
            ['nom' => 'Intoxication aux opiacés', 'description' => 'Recommandation OMS: Naloxone 0,4mg IV (répétable). Ventilation mécanique si besoin. Observation 24h.'],
            ['nom' => 'Rage (post-exposition)', 'description' => 'Recommandation OMS: Vaccin rabique J0, J3, J7, J14, J28. Immunoglobulines rabiques si catégorie III. Lavage plaie.'],
            ['nom' => 'Tétanos', 'description' => 'Recommandation OMS: Antitoxine tétanique humaine 500UI IM. Amoxicilline ou métronidazole. Toilette chirurgicale.'],
            ['nom' => 'Peste', 'description' => 'Recommandation OMS: Streptomycine 30mg/kg/jour IM ou Gentamicine 5mg/kg/jour IV/IM. Isolement. Déclaration obligatoire.'],
            ['nom' => 'Charbon (anthrax cutané)', 'description' => 'Recommandation OMS: Ciprofloxacine 500mg 2x/jour (adultes) pendant 7 jours. Pas d\'incision.'],
        ];
        
        $whoSymptoms = [
            'Fièvre' => 'Fièvre',
            'Céphalées' => 'Céphalées',
            'Frissons' => 'Frissons',
            'Toux' => 'Toux',
            'Diarrhée' => 'Diarrhée',
            'Dyspnée' => 'Dyspnée',
            'Ictère' => 'Ictère',
            'Amaigrissement' => 'Amaigrissement',
            'Sueurs nocturnes' => 'Sueurs nocturnes',
        ];
        
        foreach ($whoMaladies as $whoData) {
            $maladie = DB::table('maladies')->where('nom', $whoData['nom'])->first();
            if (!$maladie) {
                $maladieId = DB::table('maladies')->insertGetId([
                    'nom' => $whoData['nom'],
                    'description' => $whoData['description'],
                    'uuid' => (string) Str::uuid(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                $maladieId = $maladie->id;
                DB::table('maladies')->where('id', $maladieId)->update([
                    'description' => $whoData['description'],
                    'updated_at' => $now,
                ]);
            }
            
            // Create WHO protocol for this disease
            $protocol = DB::table('protocole_traitements')->where('maladie_id', $maladieId)->first();
            if (!$protocol) {
                $protocoleId = DB::table('protocole_traitements')->insertGetId([
                    'maladie_id' => $maladieId,
                    'uuid' => (string) Str::uuid(),
                    'titre' => 'Protocole WHO 2024 : ' . $whoData['nom'],
                    'signes' => 'Selon présentation clinique',
                    'diagnostics' => 'Selon protocole OMS',
                    'traitement_principal' => 'Voir guideline OMS 2024',
                    'posologie_principale' => 'Voir guideline OMS 2024',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Enrichit la base avec les pathologies, médicaments et protocoles cliniques
     * extraits dynamiquement du Guide Thérapeutique de Terrain (public/index.html).
     */
    private function enrichWithGuideTherapeutique($now)
    {
        $indexPath = public_path('index.html');
        if (!file_exists($indexPath)) {
            $this->command->warn("Guide clinique (index.html) non trouvé.");
            return;
        }

        $html = file_get_contents($indexPath);
        if (!preg_match('/const DATA = \s*\[([\s\S]*?)\];/m', $html, $matches)) {
            $this->command->error("DATA array non trouvé dans index.html");
            return;
        }

        $jsData = trim($matches[1]);
        // Supprime les commentaires JavaScript sur une ligne
        $jsData = preg_replace('/\/\/.*$/m', '', $jsData);

        // Convertit les clés d'objets JS en format JSON valide ("key":)
        $jsonData = preg_replace('/(?<=[\{,])\s*([a-zA-Z0-9_]+)\s*:/', '"$1":', $jsData);
        $jsonData = preg_replace('/,\s*\}/', '}', $jsonData);
        $jsonData = preg_replace('/,\s*\]/', ']', $jsonData);

        $finalJson = '[' . $jsonData . ']';
        $items = json_decode($finalJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error("Erreur de décodage JSON du guide : " . json_last_error_msg());
            return;
        }

        $defaultUniteId = DB::table('unites')->value('id') ?? 1;

        foreach ($items as $item) {
            $category = trim($item['c'] ?? 'Général');
            $urgent = (int)($item['u'] ?? 0);
            $pathology = trim($item['t'] ?? '');
            $diagnostic = trim($item['d'] ?? 'Diagnostic clinique.');
            $medications = $item['m'] ?? [];
            $treatment = trim($item['tx'] ?? '');

            if (empty($pathology)) continue;

            // 1. Créer ou trouver la maladie
            $maladie = DB::table('maladies')->where('nom', $pathology)->first();
            if (!$maladie) {
                $maladieId = DB::table('maladies')->insertGetId([
                    'nom' => $pathology,
                    'uuid' => (string) Str::uuid(),
                    'created_at' => $now,
                    'updated_at' => $now,
                    'description' => "Guide Thérapeutique : " . $pathology
                ]);
            } else {
                $maladieId = $maladie->id;
                DB::table('maladies')->where('id', $maladieId)->update([
                    'description' => "Guide Thérapeutique : " . $pathology,
                    'updated_at' => $now
                ]);
            }

            // 2. Gérer la famille thérapeutique (catégorie du guide)
            $familleId = DB::table('familles')->where('nom', $category)->value('id');
            if (!$familleId && !empty($category)) {
                $familleId = DB::table('familles')->insertGetId([
                    'nom' => $category,
                    'uuid' => (string) Str::uuid(),
                    'created_at' => $now,
                    'updated_at' => $now,
                    'description' => "Catégorie : " . $category
                ]);
            }
            $familleId = $familleId ?? 1;

            // 3. Créer ou trouver le protocole de traitement
            $protocol = DB::table('protocole_traitements')->where('maladie_id', $maladieId)->first();
            
            $protocolData = [
                'maladie_id' => $maladieId,
                'titre' => $urgent ? "URGENCE : " . $pathology : "Protocole : " . $pathology,
                'diagnostics' => $diagnostic,
                'traitement_principal' => Str::limit($treatment, 250), // Troncature pour varchar(255)
                'posologie_principale' => $treatment, // Text complet dans la colonne text
                'remarques' => $urgent ? "Alerte Urgence" : null,
                'updated_at' => $now
            ];

            if (!$protocol) {
                $protocolData['uuid'] = (string) Str::uuid();
                $protocolData['created_at'] = $now;
                $protocoleId = DB::table('protocole_traitements')->insertGetId($protocolData);
            } else {
                $protocoleId = $protocol->id;
                DB::table('protocole_traitements')->where('id', $protocoleId)->update($protocolData);
            }

            // 4. Gérer les médicaments et liaisons
            foreach ($medications as $medName) {
                $medName = trim($medName);
                if (empty($medName)) continue;

                // Créer ou trouver le médicament
                $medicament = DB::table('medicaments')->where('nom', $medName)->first();
                if (!$medicament) {
                    $medicamentId = DB::table('medicaments')->insertGetId([
                        'nom' => $medName,
                        'description' => "Molécule : " . $medName,
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
                }

                // Lier le protocole au médicament
                DB::table('protocole_medicament')->updateOrInsert(
                    [
                        'protocole_id' => $protocoleId,
                        'medicament_id' => $medicamentId
                    ],
                    [
                        'uuid' => (string) Str::uuid(),
                        'type' => 'principal',
                        'posologie' => 'Selon protocole',
                        'duree' => 'Selon protocole',
                        'created_at' => $now,
                        'updated_at' => $now
                    ]
                );
            }
        }
    }
}