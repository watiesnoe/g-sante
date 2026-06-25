<?php

namespace Database\Seeders;
use Illuminate\Support\Str;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaladieSymptomeSeeder extends Seeder
{
    /**
     * Crée les maladies et symptômes de base et les lie.
     * Les données avancées (protocoles, médicaments) sont gérées par PathologiesSeeder et InfectiologieSeeder.
     * ENRICHI avec données du guideline-339 OMS 2024:
     * - Maladies infectieuses (paludisme, tuberculose, VIH, hépatites, IST)
     * - Maladies non transmissibles (diabète, HTA, maladies cardiovasculaires)
     * - Affections respiratoires, digestives, neurologiques, dermatologiques
     * - Parasitoses et maladies tropicales négligées
     */
    public function run()
    {
        $now = now();

        // --- SYMPTÔMES ---
        $symptomes = [
            // Symptômes généraux (gardés)
            ['nom' => 'Fièvre',             'description' => 'Élévation de la température corporelle au-dessus de 38°C'],
            ['nom' => 'Fatigue',            'description' => 'Sensation de faiblesse et épuisement généralisé'],
            ['nom' => 'Céphalées',          'description' => 'Douleurs ou maux de tête'],
            ['nom' => 'Nausées',            'description' => 'Envie de vomir, malaise gastrique'],
            ['nom' => 'Vomissements',       'description' => 'Expulsion forcée du contenu gastrique'],
            ['nom' => 'Toux',               'description' => 'Expulsion brusque d\'air des voies respiratoires'],
            ['nom' => 'Frissons',           'description' => 'Tremblements involontaires accompagnant la fièvre'],
            ['nom' => 'Diarrhée',           'description' => 'Selles liquides fréquentes'],
            ['nom' => 'Courbatures',        'description' => 'Douleurs musculaires diffuses'],
            ['nom' => 'Perte d\'appétit',  'description' => 'Diminution ou absence de l\'envie de manger'],
            ['nom' => 'Dyspnée',            'description' => 'Difficulté ou gêne respiratoire'],
            ['nom' => 'Douleur thoracique', 'description' => 'Douleur ou oppression au niveau de la poitrine'],
            ['nom' => 'Sueurs nocturnes',   'description' => 'Transpiration excessive pendant le sommeil'],
            ['nom' => 'Amaigrissement',     'description' => 'Perte de poids involontaire'],
            ['nom' => 'Ictère',             'description' => 'Jaunissement de la peau et des muqueuses'],
            ['nom' => 'Prurit',             'description' => 'Démangeaisons cutanées'],
            ['nom' => 'Éruption cutanée',   'description' => 'Rougeurs ou boutons sur la peau'],
            ['nom' => 'Hémoptysies',        'description' => 'Crachats de sang d\'origine pulmonaire'],
            ['nom' => 'Raideur de la nuque','description' => 'Limitation douloureuse des mouvements du cou'],
            ['nom' => 'Photophobie',        'description' => 'Hypersensibilité à la lumière'],
            
            // Symptômes ENRICHI du guideline OMS
            ['nom' => 'Tachypnée',           'description' => 'Fréquence respiratoire augmentée > 20/min au repos'],
            ['nom' => 'Tirage intercostal',  'description' => 'Rétraction des espaces intercostaux lors de l\'inspiration'],
            ['nom' => 'Cyanose',             'description' => 'Coloration bleutée des lèvres et extrémités (hypoxémie)'],
            ['nom' => 'Splénomégalie',       'description' => 'Augmentation du volume de la rate'],
            ['nom' => 'Hépatomégalie',       'description' => 'Augmentation du volume du foie'],
            ['nom' => 'Adénopathies',        'description' => 'Gonflement des ganglions lymphatiques'],
            ['nom' => 'Edèmes des membres inférieurs', 'description' => 'Gonflement des jambes par rétention hydrique'],
            ['nom' => 'Polyurie',            'description' => 'Augmentation du volume urinaire'],
            ['nom' => 'Polydipsie',          'description' => 'Soif excessive'],
            ['nom' => 'Polyphagie',          'description' => 'Faim excessive'],
            ['nom' => 'Hypoglycémie',        'description' => 'Glycémie < 3 mmol/L (symptômes: sueurs, tremblements, confusion)'],
            ['nom' => 'Douleur abdominale',  'description' => 'Douleur au niveau de l\'abdomen'],
            ['nom' => 'Ténesme',             'description' => 'Sensations de besoin impérieux de déféquer sans selle'],
            ['nom' => 'Méléna',              'description' => 'Selles noires goudronneuses (saignement digestif haut)'],
            ['nom' => 'Hématurie',           'description' => 'Présence de sang dans les urines'],
            ['nom' => 'Dysurie',            'description' => 'Douleur lors de la miction'],
            ['nom' => 'Pollakiurie',         'description' => 'Fréquence urinaire augmentée'],
            ['nom' => 'Convulsions',         'description' => 'Crises épileptiques généralisées ou partielles'],
            ['nom' => 'Troubles de la conscience', 'description' => 'Confusion, obnubilation, coma'],
            ['nom' => 'Vertiges',            'description' => 'Sensation de rotation ou déséquilibre'],
            ['nom' => 'Paresthésies',        'description' => 'Sensations anormales (picotements, engourdissements)'],
            ['nom' => 'Douleur osseuse',     'description' => 'Douleurs des os (crise drépanocytaire, métastases)'],
            ['nom' => 'Douleur articulaire', 'description' => 'Douleurs des articulations'],
            ['nom' => 'Lymphœdème',          'description' => 'Gonflement des membres par obstruction lymphatique'],
            ['nom' => 'Prurit anal',         'description' => 'Démangeaisons anales (oxyurose)'],
            ['nom' => 'Toux quinteuse',      'description' => 'Toux en quintes caractéristique de la coqueluche'],
            ['nom' => 'Syndrome méningé',    'description' => 'Raideur de nuque, Kernig, Brudzinski'],
            ['nom' => 'Hypotonie',           'description' => 'Diminution du tonus musculaire (nourrisson)'],
            ['nom' => 'Fontanelle bombante',  'description' => 'Bombement de la fontanelle antérieure (méningite nourrisson)'],
            ['nom' => 'Anémie',              'description' => 'Pâleur des conjonctives, muqueuses, fatigue, dyspnée d\'effort'],
            ['nom' => 'Pâleur',              'description' => 'Teint pâle (anémie, choc)'],
            ['nom' => 'Ictère néonatal',     'description' => 'Jaunisse chez le nouveau-né < 28 jours'],
            ['nom' => 'Écoulement urétral',  'description' => 'Écoulement purulent par l\'urètre (gonococcie)'],
            ['nom' => 'Chancre',             'description' => 'Ulcération génitale indolore (syphilis primaire)'],
            ['nom' => 'Condylomes',          'description' => 'Végétations vénériennes (HPV)'],
            ['nom' => 'Muguet buccal',       'description' => 'Enduit blanc crémeux de la cavité buccale (candidose)'],
            ['nom' => 'Angine',              'description' => 'Douleur pharyngée avec dysphagie'],
            ['nom' => 'Otalgie',             'description' => 'Douleur d\'oreille'],
            ['nom' => 'Conjonctivite',       'description' => 'Rougeur et écoulement oculaire'],
            ['nom' => 'Kératite',           'description' => 'Inflammation de la cornée (herpès)'],
            ['nom' => 'Dermatite',           'description' => 'Inflammation cutanée avec rougeur, vésicules, prurit'],
            ['nom' => 'Nodules sous-cutanés', 'description' => 'Petites masses sous la peau (onchocercose, rhumatisme)'],
            ['nom' => 'Ulcère cutané',       'description' => 'Perte de substance cutanée (leishmanioses, pyoderma)'],
            ['nom' => 'Hémoglobinurie',      'description' => 'Urine foncée (coloquinte) (paludisme grave, drépanocytose)'],
        ];

        foreach ($symptomes as $s) {
            DB::table('symptomes')->updateOrInsert(
                ['nom' => $s['nom']],
                ['description' => $s['description'], 'uuid' => (string) Str::uuid(), 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $sId = DB::table('symptomes')->pluck('id', 'nom');

        // --- MALADIES COURANTES ---
        $maladies = [
            // Maladies infectieuses (gardées et enrichies)
            ['nom' => 'Paludisme simple',      'description' => 'Paludisme non compliqué (P. falciparum, vivax, ovale, malariae). Fièvre, frissons, céphalées. TDR ou GE positifs.'],
            ['nom' => 'Paludisme grave',       'description' => 'Paludisme avec signes de gravité OMS: coma, convulsions, détresse respiratoire, choc, ictère, hémoglobinurie, hypoglycémie, anémie sévère.'],
            ['nom' => 'Paludisme à P. vivax', 'description' => 'Paludisme à Plasmodium vivax. Hypnozoïtes hépatiques (rechutes). Nécessite primaquine après dosage G6PD.'],
            ['nom' => 'Grippe',                'description' => 'Infection virale respiratoire saisonnière (influenza A/B). Fièvre, myalgies, céphalées, toux.'],
            ['nom' => 'Gastro-entérite',       'description' => 'Inflammation de l\'estomac et des intestins. Diarrhée, vomissements, douleurs abdominales.'],
            ['nom' => 'Choléra',               'description' => 'Infection à Vibrio cholerae. Diarrhée aqueuse profuse « eau de riz ». Déshydratation rapide. Déclaration obligatoire.'],
            ['nom' => 'Typhoïde',              'description' => 'Fièvre typhoïde (Salmonella Typhi). Fièvre en plateau, douleurs abdominales, constipation/diarrhée, taches rosées.'],
            ['nom' => 'Shigellose',            'description' => 'Dysenterie bacillaire (Shigella). Diarrhée sanglante, douleurs abdominales, ténesme.'],
            ['nom' => 'Amibiase intestinale',  'description' => 'Infection à Entamoeba histolytica. Dysenterie amibienne, abcès amibien du foie.'],
            ['nom' => 'Méningite',            'description' => 'Inflammation des méninges d\'origine bactérienne ou virale. Fièvre, céphalées, raideur de nuque, photophobie.'],
            ['nom' => 'Méningite cryptococcique', 'description' => 'Méningite à Cryptococcus neoformans (souvent VIH). Céphalées, fièvre, troubles de conscience.'],
            ['nom' => 'Tuberculose pulmonaire','description' => 'Infection bactérienne chronique des poumons (Mycobacterium tuberculosis). Toux >2 sem, hémoptysies, sueurs nocturnes, amaigrissement.'],
            ['nom' => 'Tuberculose multirésistante', 'description' => 'TB résistente à rifampicine ± isoniazide. Protocoles длительные (18-24 mois).'],
            ['nom' => 'Candidose systémique',  'description' => 'Infection fongique généralisée par Candida. Fièvre réfractaire aux antibiotiques, neutropénie.'],
            ['nom' => 'Pneumonie simple',      'description' => 'Infection respiratoire légère. Toux, fièvre, tachypnée. SpO2 >90%.'],
            ['nom' => 'Pneumonie sévère',      'description' => 'Infection respiratoire grave. Détresse respiratoire, SpO2 <90%, cyanose. Hospitalisation.'],
            ['nom' => 'Coqueluche',            'description' => 'Infection à Bordetella pertussis. Toux quinteuse nocturne, quintes avec reprise inspiratoire (« chant du coq »).'],
            ['nom' => 'Lèpre',                 'description' => 'Maladie de Hansen (Mycobacterium leprae). Lésions cutanées hypo/anesthésiques, épaississement nerveux. PB ou MB.'],
            ['nom' => 'Cystite',               'description' => 'Infection urinaire basse (cystite). Dysurie, pollakiurie, douleurs sus-pubiennes.'],
            ['nom' => 'Pyélonéphrite',         'description' => 'Infection urinaire haute (rein). Fièvre, douleurs lombaires, vomissements.'],
            
            // IST
            ['nom' => 'VIH/SIDA',              'description' => 'Virus de l\'immunodéfiscience humaine. Stade précoce, intermédiaire, SIDA (CD4<200).'],
            ['nom' => 'Syphilis',               'description' => 'Infection à Treponema pallidum. Stade primaire (chancre), secondaire (rash), tertiaire (cardiovasculaire, neurosyphilis).'],
            ['nom' => 'Gonococcie',             'description' => 'Blennorragie (Neisseria gonorrhoeae). Urétrite, cervicite, écoulement purulent.'],
            ['nom' => 'Chlamydia',              'description' => 'Infection à Chlamydia trachomatis. Urétrite, cervicite, doulent pelviennes.'],
            ['nom' => 'Trichomonase',           'description' => 'Infection à Trichomonas vaginalis. Leucorrhées verdâtres, prurit vulvaire.'],
            
            // Hépatites
            ['nom' => 'Hépatite B',            'description' => 'Infection virale s\'attaquant au foie (VHB). Chronique: traitement ténofovir à vie.'],
            ['nom' => 'Hépatite C',            'description' => 'Infection virale du foie (VHC). Chronique: traitement par antiviraux d\'action directe (SOF/VEL).'],
            
            // Parasitoses / MTN
            ['nom' => 'Ascaridiose',           'description' => 'Infection à Ascaris lumbricoides. Nématodose intestinale. Traitement: albendazole.'],
            ['nom' => 'Oxyurose',              'description' => 'Infection à Enterobius vermicularis. Prurit anal nocturne. Traitement: albendazole ou pyrantel.'],
            ['nom' => 'Schistosomiase',        'description' => 'Bilharziose (Schistosoma haematobium/mansoni/japonicum). Hématurie, hepatosplénomégalie. Traitement: praziquantel.'],
            ['nom' => 'Filariose lymphatique',  'description' => 'Filariose à Wuchereria bancrofti/Brugia malayi. Lymphœdème, hydrocèle, éléphantiasis. MDA: DEC+albendazole.'],
            ['nom' => 'Onchocercose',          'description' => 'Cécité des rivières (Onchocerca volvulus). Prurit, nodules, troubles visuels, cécité. Traitement: ivermectine.'],
            ['nom' => 'Leishmaniose viscérale','description' => 'Kala-azar (Leishmania donovani). Fièvre, splénomégalie, pancytopénie. Traitement: Amphotéricine B liposomale.'],
            ['nom' => 'Gale',                  'description' => 'Infestation à Sarcoptes scabiei. Prurit intense nocturne, sillons interdigitaux. Traitement: perméthrine.'],
            ['nom' => 'Teigne',                'description' => 'Dermatophytose du cuir chevelu (tinea capitis). Plaques squameuses, cheveux cassés. Traitement: griséofulvine.'],
            ['nom' => 'Dermatophytose cutanée','description' => 'Infection fongique de la peau (Trichophyton, Microsporum). Lésions annulaires, prurit. Traitement: terbinafine, clotrimazole.'],
            
            // Maladies non transmissibles
            ['nom' => 'Hypertension artérielle', 'description' => 'Pression artérielle > 140/90 mmHg. Traitement à vie: amlodipine, énalapril.'],
            ['nom' => 'Hypertension gravidique', 'description' => 'HTA après 20 SA. Risque pré-éclampsie. Labétalol, méthyldopa.'],
            ['nom' => 'Diabète de type 1',     'description' => 'Déficit absolu en insuline (auto-immun). Insulinothérapie permanente.'],
            ['nom' => 'Diabète de type 2',     'description' => 'Trouble métabolique avec insulinorésistance. Metformine ± sulfamides.'],
            ['nom' => 'Asthme',                'description' => 'Maladie inflammatoire bronchique chronique. Dyspnée paroxystique, sibilants, toux.'],
            ['nom' => 'Insuffisance cardiaque','description' => 'Coeur incapable d\'assurer un débit suffisant. Dyspnée, œdèmes, fatigue. Traitement: IEC, bêtabloquants, diurétiques.'],
            ['nom' => 'Drépanocytose',         'description' => 'Maladie génétique de l\'hémoglobine (HbS). Anémie, crises vaso-occlusives. Prophylaxie pénicilline.'],
            ['nom' => 'Syndrome thoracique aigu', 'description' => 'Complication drépanocytaire grave. Douleur thoracique, fièvre, hypoxémie.'],
            
            // Neurologie / Psychiatrie
            ['nom' => 'Épilepsie',             'description' => 'Maladie neurologique avec crises convulsives répétées. Traitement: carbamazépine, valproate, lévétiracétam.'],
            ['nom' => 'État de mal convulsif', 'description' => 'Crises convulsives continues. Urgence vitale: diazépam, phénytoïne.'],
            ['nom' => 'Douleur neuropathique',  'description' => 'Douleur par lésion ou dysfonctionnement du système nerveux. Amitriptyline, gabapentine.'],
            ['nom' => 'Dépression',            'description' => 'Trouble de l\'humeur. Traitement: IRS, tricycliques (amitriptyline).'],
            ['nom' => 'Schizophrénie',         'description' => 'Trouble psychotique chronique. Traitement: antipsychotiques (halopéridol, olanzapine, rispéridone).'],
            ['nom' => 'Troubles anxieux',      'description' => 'Anxiété généralisée, attaques de panique. Benzodiazépines, IRS.'],
            
            // Urgences / Intoxications
            ['nom' => 'Intoxication au paracétamol', 'description' => 'Surdosage au paracétamol > toxicité hépatique. Antidote: N-acétylcystéine IV.'],
            ['nom' => 'Intoxication aux organophosphorés', 'description' => 'Exposition aux insecticides organophosphorés. Syndrome cholinergique SLUDGE. Antidote: atropine + pralidoxime.'],
            ['nom' => 'Choc anaphylactique',    'description' => 'Réaction allergique sévère immédiate. Urgence: adrénaline IM.'],
            ['nom' => 'Hypoglycémie sévère',   'description' => 'Glycémie < 2,5 mmol/L avec signes. Urgence: glucose IV ou glucagon IM.'],
            ['nom' => 'Acidocétose diabétique', 'description' => 'Complication métabolique du DT1. Polyurie, polydipsie, vomissements, haleine cétonique.'],
            ['nom' => 'Pré-éclampsie',         'description' => 'HTA + protéinurie après 20 SA. Risque éclampsie. Sulfate de magnésium, labétalol.'],
            ['nom' => 'Éclampsie',             'description' => 'Convulsions sur pré-éclampsie. Urgence obstétricale. Sulfate de magnésium.'],
            
            // Pédiatrie
            ['nom' => 'Méningite néonatale',   'description' => 'Méningite chez le nourrisson < 1 mois. Fontanelle bombante, hypotonie, convulsions.'],
            ['nom' => 'Septicémie néonatale',  'description' => 'Infection bacterienne généralisée du nouveau-né. Prise en charge urgente: ampicilline + gentamicine.'],
            ['nom' => 'Bronchiolite',          'description' => 'Infection virale des bronchioles (VRS). Nourrisson < 2 ans. Tachypnée, sibilants.'],
            ['nom' => ' Rougeole',             'description' => 'Infection virale (paramyxovirus). Fièvre, toux, coryza, conjonctivite, puis rash maculopapuleux.'],
            ['nom' => 'Oreillons',             'description' => 'Infection virale à paramyxovirus. Parotidite bilatérale fébrile. Complications: orchite, méningite.'],
            ['nom' => 'Varicelle',             'description' => 'Infection virale (VZV). Vésicules prurigineuses en « goutte de rosée ». Zona possible secondaire.'],
            ['nom' => 'Diphtérie',             'description' => 'Infection à Corynebacterium diphtheriae. Angine pseudomembraneuse, atteinte cardiaque et neurologique. Antitoxine.'],
            
            // Divers
            ['nom' => 'Candidose oropharyngée', 'description' => 'Muguet (Candida albicans). Enduit blanc buccal, brûlures. Traitement: nystatine, fluconazole.'],
            ['nom' => 'Cystite radique',       'description' => 'Cystite hémorragique sous cyclophosphamide. MESNA prophylactique.'],
            ['nom' => 'Syndrome de lyse tumorale', 'description' => 'Complication des chimiotherapies à haute dose. Hyperuricémie, hyperkaliémie. Allopurinol, rasburicase.'],
            ['nom' => 'PTME',                  'description' => 'Prévention de la transmission mère-enfant du VIH. TAR maternel + prophylaxie néonatale.'],
            ['nom' => 'Gale norvégienne',       'description' => 'Forme hyperinfestante de gale (immunodéprimés). Croûtes épaisses, millions d\'acariens.'],
            ['nom' => 'PTI (purpura thrombopénique immunologique)', 'description' => 'Thrombopénie auto-immune. Purpura, saignements muqueux. Corticoïdes, IgIV.'],
            ['nom' => 'Maladie de Kawasaki',   'description' => 'Vasculite des enfants < 5 ans. Fièvre, conjonctivite, Rash, langue framboisée, adénopathies. IgIV + aspirine.'],
        ];

        foreach ($maladies as $m) {
            DB::table('maladies')->updateOrInsert(
                ['nom' => $m['nom']],
                ['description' => $m['description'] ?? null, 'uuid' => (string) Str::uuid(), 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $mId = DB::table('maladies')->pluck('id', 'nom');

        // --- LIAISONS MALADIE ↔ SYMPTÔMES ---
        $liens = [
            'Paludisme simple'      => ['Fièvre', 'Frissons', 'Céphalées', 'Courbatures', 'Fatigue', 'Nausées', 'Vomissements'],
            'Paludisme grave'       => ['Fièvre', 'Vomissements', 'Fatigue', 'Ictère', 'Douleur thoracique', 'Troubles de la conscience', 'Convulsions', 'Hémoglobinurie'],
            'Paludisme à P. vivax' => ['Fièvre', 'Frissons', 'Splénomégalie', 'Amaigrissement', 'Anémie'],
            'Grippe'                => ['Fièvre', 'Fatigue', 'Céphalées', 'Toux', 'Courbatures', 'Douleur thoracique'],
            'Gastro-entérite'      => ['Fièvre', 'Nausées', 'Vomissements', 'Diarrhée', 'Perte d\'appétit', 'Douleur abdominale'],
            'Choléra'              => ['Diarrhée', 'Vomissements', 'Déshydratation', 'Hypotonie', 'Crampe musculaire'],
            'Typhoïde'             => ['Fièvre', 'Céphalées', 'Douleur abdominale', 'Constipation', 'Diarrhée', 'Sueurs nocturnes', 'Amaigrissement'],
            'Shigellose'           => ['Diarrhée', 'Douleur abdominale', 'Ténesme', 'Fièvre', 'Sang dans les selles'],
            'Amibiase intestinale'=> ['Diarrhée', 'Douleur abdominale', 'Ténesme', 'Sang dans les selles', 'Fièvre'],
            'Méningite'           => ['Fièvre', 'Céphalées', 'Raideur de la nuque', 'Photophobie', 'Vomissements', 'Fatigue', 'Troubles de la conscience', 'Convulsions'],
            'Méningite cryptococcique' => ['Fièvre', 'Céphalées', 'Raideur de la nuque', 'Troubles de la conscience', 'Troubles visuels'],
            'Tuberculose pulmonaire' => ['Toux', 'Hémoptysies', 'Sueurs nocturnes', 'Amaigrissement', 'Fièvre', 'Fatigue', 'Douleur thoracique'],
            'Tuberculose multirésistante' => ['Toux', 'Sueurs nocturnes', 'Amaigrissement', 'Fièvre', 'Fatigue'],
            'Candidose systémique' => ['Fièvre', 'Fatigue', 'Prurit', 'Éruption cutanée', 'Douleur abdominale'],
            'Pneumonie simple'     => ['Toux', 'Fièvre', 'Tachypnée', 'Dyspnée', 'Douleur thoracique'],
            'Pneumonie sévère'     => ['Toux', 'Fièvre', 'Dyspnée sévère', 'Cyanose', 'Tirage intercostal', 'Confusion'],
            'Coqueluche'          => ['Toux quinteuse', 'Vomissements', ' cyanose paroxystique', 'Fièvre'],
            'Lèpre'               => ['Éruption cutanée', 'Perte de sensibilité', 'Douleur nerveuse', 'Nodules sous-cutanés', 'Déformations'],
            'Cystite'             => ['Pollakiurie', 'Dysurie', 'Douleur abdominale', 'Hématurie'],
            'Pyélonéphrite'       => ['Fièvre', 'Douleur lombaire', 'Vomissements', 'Pollakiurie', 'Dysurie'],
            'VIH/SIDA'            => ['Fièvre', 'Diarrhée', 'Amaigrissement', 'Sueurs nocturnes', 'Adénopathies', 'Fatigue', 'Candidose'],
            'Syphilis'            => ['Chancre', 'Éruption cutanée', 'Adénopathies', 'Fièvre', 'Céphalées'],
            'Gonococcie'          => ['Écoulement urétral', 'Dysurie', 'Douleur abdominale', 'Fièvre'],
            'Chlamydia'           => ['Écoulement urétral', 'Dysurie', 'Saignements intermenstruels', 'Douleur pelvienne'],
            'Trichomonase'        => ['Prurit', 'Leucorrhées verdâtres', 'Dysurie'],
            'Hépatite B'          => ['Ictère', 'Fatigue', 'Douleur abdominale', 'Hépatomégalie', 'Urine foncée'],
            'Hépatite C'          => ['Fatigue', 'Ictère (parfois)', 'Hépatomégalie', 'Asymptomatique souvent'],
            'Ascaridiose'         => ['Douleur abdominale', 'Diarrhée', 'Amaigrissement', 'Toux (migration larvaire)'],
            'Oxyurose'            => ['Prurit anal', 'Agitation nocturne', 'Douleur abdominale'],
            'Schistosomiase'      => ['Hématurie', 'Douleur abdominale', 'Hépatomégalie', 'Splénomégalie', 'Diarrhée', 'Ascites'],
            'Filariose lymphatique'=> ['Lymphœdème', 'Edèmes des membres inférieurs', 'Fièvre récurrente', 'Hydrocèle'],
            'Onchocercose'        => ['Prurit', 'Dermatite', 'Nodules sous-cutanés', 'Troubles visuels', 'Cécité'],
            'Leishmaniose viscérale' => ['Fièvre', 'Splénomégalie', 'Hépatomégalie', 'Amaigrissement', 'Anémie', 'Pâleur'],
            'Gale'                => ['Prurit intense', 'Prurit anal', 'Éruption cutanée', 'Sillons interdigitaux'],
            'Teigne'              => ['Plaques squameuses', 'Cheveux cassés', 'Prurit du cuir chevelu'],
            'Dermatophytose cutanée' => ['Éruption cutanée', 'Prurit', 'Lésions annulaires'],
            'Hypertension artérielle' => ['Céphalées occipitales', 'Vertiges', 'Acouphènes', 'Asymptomatique souvent'],
            'Hypertension gravidique' => ['Céphalées', 'Edèmes des membres inférieurs', 'Troubles visuels'],
            'Diabète de type 1'   => ['Polyurie', 'Polydipsie', 'Polyphagie', 'Amaigrissement', 'Fatigue', 'Troubles visuels'],
            'Diabète de type 2'   => ['Polyurie', 'Polydipsie', 'Polyphagie', 'Amaigrissement', 'Fatigue', 'Paresthésies', 'Troubles visuels'],
            'Asthme'              => ['Dyspnée paroxystique', 'Sibilants', 'Toux', 'Oppression thoracique'],
            'Insuffisance cardiaque' => ['Dyspnée', 'Edèmes des membres inférieurs', 'Fatigue', 'Orthopnée', 'Hépatomégalie'],
            'Drépanocytose'       => ['Anémie', 'Douleur osseuse', 'Douleur articulaire', 'Fièvre', 'Ictère', 'Splénomégalie', 'Amaigrissement'],
            'Syndrome thoracique aigu' => ['Douleur thoracique', 'Fièvre', 'Dyspnée', 'Toux', 'Cyanose'],
            'Épilepsie'           => ['Convulsions', 'Troubles de la conscience', 'Perte d\'urine', 'Morsure de langue'],
            'État de mal convulsif' => ['Convulsions continues', 'Troubles de la conscience', 'Cyanose', 'Hypersalivation'],
            'Douleur neuropathique' => ['Paresthésies', 'Douleur brûlante', 'Allodynie', 'Picotements'],
            'Dépression'          => ['Fatigue', 'Tristesse', 'Perte d\'appétit', 'Insomnie', 'Perte d\'intérêt'],
            'Schizophrénie'       => ['Hallucinations', 'Délires', 'Troubles du comportement', 'Apathie', 'Isolement'],
            'Intoxication au paracétamol' => ['Nausées', 'Vomissements', 'Douleur abdominale', 'Ictère', 'Troubles de la conscience'],
            'Intoxication aux organophosphorés' => ['Hypersalivation', 'Larmoiement', 'Bronchospasme', 'Bradycardie', 'Myosis', 'Convulsions', 'Diarrhée'],
            'Choc anaphylactique'  => ['Urticaire', 'Prurit', 'Dyspnée', 'Edème de Quincke', 'Hypotension', 'Cyanose'],
            'Hypoglycémie sévère'  => ['Sueurs', 'Tremblements', 'Palpitations', 'Confusion', 'Perte de connaissance', 'Convulsions'],
            'Acidocétose diabétique' => ['Polyurie', 'Polydipsie', 'Vomissements', 'Douleur abdominale', 'Confusion', 'Hyperventilation'],
            'Pré-éclampsie'       => ['Céphalées', 'Troubles visuels', 'Edèmes', 'HTA', 'Protéinurie'],
            'Éclampsie'           => ['Convulsions', 'Troubles de la conscience', 'Céphalées', 'Troubles visuels'],
            'Méningite néonatale' => ['Fièvre', 'Fontanelle bombante', 'Hypotonie', 'Convulsions', 'Vomissements', 'Irritabilité'],
            'Septicémie néonatale'=> ['Fièvre', 'Hypothermie', 'Hypotonie', 'Mauvaise perfusion', 'Apnées', 'Vomissements'],
            'Bronchiolite'        => ['Tachypnée', 'Sibilants', 'Toux', 'Fièvre', 'Distension thoracique'],
            'Rougeole'            => ['Fièvre', 'Toux', 'Coryza', 'Conjonctivite', 'Éruption cutanée', 'Taches de Koplik'],
            'Oreillons'           => ['Fièvre', 'Douleur parotidienne', 'Gonflement parotidien bilatéral'],
            'Varicelle'           => ['Fièvre', 'Éruption cutanée', 'Prurit', 'Vésicules'],
            'Diphtérie'           => ['Angine', 'Fièvre', 'Dysphagie', 'Douleur cervicale', 'Pseudomembranes'],
            'Candidose oropharyngée' => ['Muguet buccal', 'Brûlures buccales', 'Dysphagie', 'Perte de goût'],
            'PTME'                => ['Asymptomatique (nourrisson)', 'VIH exposé (surveillance)'],
            'Gale norvégienne'    => ['Prurit intense', 'Coutures épaisses', 'Kératoses', 'Déformations unguéales'],
            'PTI'                 => ['Purpura', 'Pétéchies', 'Saignements muqueux', 'Épistaxis'],
            'Maladie de Kawasaki' => ['Fièvre', 'Conjonctivite', 'Éruption cutanée', 'Langage framboisée', 'Adénopathies', 'Edèmes'],
        ];

        foreach ($liens as $maladie => $symptomesList) {
            if (!isset($mId[$maladie])) continue;

            foreach ($symptomesList as $symptomNom) {
                if (!isset($sId[$symptomNom])) continue;

                DB::table('maladie_symptome')->updateOrInsert(
                    [
                        'maladie_id' => $mId[$maladie],
                        'symptome_id' => $sId[$symptomNom],
                    ],
                    [
                        'uuid' => (string) Str::uuid(),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }
}