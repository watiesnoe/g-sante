<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Ajoute les médicaments mentionnés dans les protocoles de traitement
 * mais absents de la table medicaments.
 * Idempotent : utilise updateOrInsert basé sur le nom.
 */
class MedicamentsProtocolesSeeder extends Seeder
{
    public function run(): void
    {
        $familles = DB::table('familles')->pluck('id', 'nom');
        $unites   = DB::table('unites')->pluck('id', 'nom');

        $cp  = $unites['comprimé']        ?? $unites->first();
        $amp = $unites['ampoule']         ?? $cp;
        $ml  = $unites['ml']              ?? $cp;
        $mg  = $unites['mg']              ?? $cp;
        $sac = $unites['sachet']          ?? $cp;
        $sup = $unites['suppositoire']    ?? $cp;
        $cry = $unites['crème']           ?? $cp;
        $col = $unites['collyre']         ?? $cp;
        $flc = $unites['solution buvable'] ?? $cp;
        $inj = $unites['injection']        ?? $amp;
        $inh = $unites['inhalation']       ?? $cp;
        $gel = $unites['gel']              ?? $cp;
        $pom = $unites['pommade']          ?? $cp;
        $glu = $unites['gélule']           ?? $cp;
        $spr = $unites['spray']            ?? $cp;

        $defaultFamille = DB::table('familles')->first()->id ?? 1;

        // Familles réutilisées
        $atb  = $familles['Antibiotiques']       ?? $defaultFamille;
        $avir = $familles['Antiviraux']          ?? 7;
        $afng = $familles['Antifongiques']       ?? 8;
        $apal = $familles['Antipaludiques']      ?? 6;
        $apar = $familles['Antiparasitaires']    ?? 22;
        $ains = $familles['AINS']                ?? $defaultFamille;
        $cort = $familles['Corticoïdes']         ?? 17;
        $diab = $familles['Antidiabétiques']     ?? $defaultFamille;
        $card = $familles['Antihypertenseurs']   ?? 10;
        $diu  = $familles['Diurétiques']         ?? 11;
        $acv  = $familles['Anticonvulsivants']   ?? 20;
        $vit  = $familles['Vitamines']           ?? $defaultFamille;
        $anti = $familles['Antihistaminiques']   ?? 5;
        $opt  = $familles['Antalgiques']         ?? 2;
        $arv  = $familles['ART (association fixe)'] ?? $familles['Antiviraux'] ?? 7;
        $anest = $familles['Anesthésiques']      ?? 9;
        $bron  = $familles['Bronchodilatateurs'] ?? 25;
        $vac   = $familles['Vaccins']            ?? 14;
        $diur  = $familles['Diurétiques']        ?? 11;

        $medicaments = [
            // ==================== ANTIVIRAUX ====================
            ['nom' => 'Acyclovir 200mg', 'description' => 'Antivirale herpétique - herpès simplex, varicelle', 'famille_id' => $avir, 'unite_id' => $cp, 'prix_vente' => 150],
            ['nom' => 'Acyclovir 400mg', 'description' => 'Antivirale herpétique - traitement et prophylaxie HSV', 'famille_id' => $avir, 'unite_id' => $cp, 'prix_vente' => 250],
            ['nom' => 'Acyclovir 800mg', 'description' => 'Antivirale herpétique - zona, herpès sévère', 'famille_id' => $avir, 'unite_id' => $cp, 'prix_vente' => 400],
            ['nom' => 'Acyclovir IV 250mg', 'description' => 'Acyclovir injectable - formes sévères herpès et zona', 'famille_id' => $avir, 'unite_id' => $amp, 'prix_vente' => 2500],
            ['nom' => 'Valacyclovir 500mg', 'description' => 'Prodrogue de l\'acyclovir - HSV, zona', 'famille_id' => $avir, 'unite_id' => $cp, 'prix_vente' => 800],
            ['nom' => 'Valacyclovir 1000mg', 'description' => 'Prodrogue de l\'acyclovir - primo-infection HSV, zona', 'famille_id' => $avir, 'unite_id' => $cp, 'prix_vente' => 1200],
            ['nom' => 'Oseltamivir 75mg (Tamiflu)', 'description' => 'Antivirale anti-influenza - grippe A et B', 'famille_id' => $avir, 'unite_id' => $glu, 'prix_vente' => 3500],
            ['nom' => 'Zanamivir inhalé', 'description' => 'Antivirale inhalée anti-influenza', 'famille_id' => $avir, 'unite_id' => $inh, 'prix_vente' => 4000],
            ['nom' => 'Acyclovir collyre 3%', 'description' => 'Antivirale ophtalmique - kératite herpétique', 'famille_id' => $avir, 'unite_id' => $col, 'prix_vente' => 2000],

            // ==================== ARV (VIH) ====================
            ['nom' => 'Ténofovir/Lamivudine/Dolutégravir (TLD)', 'description' => 'ARV combiné 1ère ligne adulte - TDF+3TC+DTG', 'famille_id' => $arv, 'unite_id' => $cp, 'prix_vente' => 500],
            ['nom' => 'Ténofovir/Lamivudine/Éfavirenz', 'description' => 'ARV combiné - TDF+3TC+EFV (option 1ère ligne)', 'famille_id' => $arv, 'unite_id' => $cp, 'prix_vente' => 400],
            ['nom' => 'Dolutégravir 50mg', 'description' => 'Inhibiteur d\'intégrase - VIH adulte', 'famille_id' => $arv, 'unite_id' => $cp, 'prix_vente' => 600],
            ['nom' => 'Névirapine 200mg', 'description' => 'INNTI - VIH, prophylaxie PTME nourrisson', 'famille_id' => $arv, 'unite_id' => $cp, 'prix_vente' => 200],
            ['nom' => 'Darunavir 800mg', 'description' => 'IP (inhibiteur de protéase) - VIH 2ème ligne', 'famille_id' => $arv, 'unite_id' => $cp, 'prix_vente' => 1200],
            ['nom' => 'Ritonavir 100mg', 'description' => 'Booster pharmacocinétique des IP - VIH', 'famille_id' => $arv, 'unite_id' => $cp, 'prix_vente' => 300],
            ['nom' => 'Lopinavir/Ritonavir 200/50mg', 'description' => 'IP boosté - VIH adulte et pédiatrique', 'famille_id' => $arv, 'unite_id' => $cp, 'prix_vente' => 800],
            ['nom' => 'Emtricitabine 200mg', 'description' => 'INTI (analogue cytidine) - VIH', 'famille_id' => $arv, 'unite_id' => $cp, 'prix_vente' => 300],
            ['nom' => 'Abacavir 300mg', 'description' => 'INTI - VIH adulte et pédiatrique', 'famille_id' => $arv, 'unite_id' => $cp, 'prix_vente' => 400],
            ['nom' => 'Ténofovir disoproxil 300mg (TDF)', 'description' => 'INTI - VIH, hépatite B chronique', 'famille_id' => $arv, 'unite_id' => $cp, 'prix_vente' => 300],

            // ==================== ANTIBIOTIQUES SPÉCIAUX ====================
            ['nom' => 'Clindamycine 300mg', 'description' => 'Antibiotique - infections staphylococciques, anaérobies', 'famille_id' => $atb, 'unite_id' => $cp, 'prix_vente' => 450],
            ['nom' => 'Clindamycine IV 600mg', 'description' => 'Antibiotique injectable - infections sévères anaérobies', 'famille_id' => $atb, 'unite_id' => $amp, 'prix_vente' => 1200],
            ['nom' => 'Vancomycine IV 500mg', 'description' => 'Glycopeptide - SARM, infections graves à Gram+', 'famille_id' => $atb, 'unite_id' => $amp, 'prix_vente' => 5000],
            ['nom' => 'Linézolide 600mg', 'description' => 'Oxazolidinone - SARM, entérocoques résistants', 'famille_id' => $atb, 'unite_id' => $cp, 'prix_vente' => 8000],
            ['nom' => 'Streptomycine IM 1g', 'description' => 'Aminoside - tuberculose, brucellose, peste', 'famille_id' => $atb, 'unite_id' => $amp, 'prix_vente' => 800],
            ['nom' => 'Bédaquiline 100mg', 'description' => 'Antituberculeux - TB multirésistante (MDR-TB)', 'famille_id' => $atb, 'unite_id' => $cp, 'prix_vente' => 15000],
            ['nom' => 'Prétomanide 200mg', 'description' => 'Antituberculeux - TB MDR schéma BPaL', 'famille_id' => $atb, 'unite_id' => $cp, 'prix_vente' => 12000],
            ['nom' => 'Choramphenicol collyre 0.5%', 'description' => 'Antibiotique ophtalmique - conjonctivites bactériennes', 'famille_id' => $atb, 'unite_id' => $col, 'prix_vente' => 1200],
            ['nom' => 'Acide fusidique crème 2%', 'description' => 'Antibiotique topique - impétigo, infections cutanées staphylococciques', 'famille_id' => $atb, 'unite_id' => $cry, 'prix_vente' => 2000],
            ['nom' => 'Mupirocine pommade 2%', 'description' => 'Antibiotique topique - impétigo, décolonisation SARM nasal', 'famille_id' => $atb, 'unite_id' => $pom, 'prix_vente' => 2500],
            ['nom' => 'Caspofungine IV 50mg', 'description' => 'Échinocandine - candidose systémique, aspergillose', 'famille_id' => $afng, 'unite_id' => $amp, 'prix_vente' => 25000],
            ['nom' => 'Daptomycine IV 500mg', 'description' => 'Lipopeptide - SARM bactériémie, endocardite', 'famille_id' => $atb, 'unite_id' => $amp, 'prix_vente' => 30000],

            // ==================== ANTIPARASITAIRES ====================
            ['nom' => 'Artésunate IV 60mg', 'description' => 'Antipaludique injectable - paludisme grave', 'famille_id' => $apal, 'unite_id' => $amp, 'prix_vente' => 3500],
            ['nom' => 'Artésunate rectal 200mg', 'description' => 'Antipaludique rectal - paludisme grave pré-hospitalier', 'famille_id' => $apal, 'unite_id' => $sup, 'prix_vente' => 1500],
            ['nom' => 'Artéméther IM 80mg', 'description' => 'Antipaludique injectable IM - paludisme grave', 'famille_id' => $apal, 'unite_id' => $amp, 'prix_vente' => 2000],
            ['nom' => 'Primaquine 15mg', 'description' => 'Antipaludique gamétocytocide - élimination hypnozoïtes P. vivax/ovale', 'famille_id' => $apal, 'unite_id' => $cp, 'prix_vente' => 200],
            ['nom' => 'Atovaquone/Proguanil (Malarone) 250/100mg', 'description' => 'Prophylaxie palustre - traitement de recours', 'famille_id' => $apal, 'unite_id' => $cp, 'prix_vente' => 3000],
            ['nom' => 'Triclabendazole 250mg', 'description' => 'Anthelminthique - fasciolose (Fasciola hepatica)', 'famille_id' => $apar, 'unite_id' => $cp, 'prix_vente' => 1500],
            ['nom' => 'Nitazoxanide 500mg', 'description' => 'Antiparasitaire - giardiase, cryptosporidiose, fasciolose', 'famille_id' => $apar, 'unite_id' => $cp, 'prix_vente' => 1000],
            ['nom' => 'Miltefosine 50mg', 'description' => 'Antiparasitaire oral - leishmaniose viscérale', 'famille_id' => $apar, 'unite_id' => $cp, 'prix_vente' => 8000],
            ['nom' => 'Méglumine antimoniate (Glucantime) IM', 'description' => 'Antimonié - leishmaniose viscérale et cutanée', 'famille_id' => $apar, 'unite_id' => $amp, 'prix_vente' => 5000],
            ['nom' => 'Diéthylcarbamazine (DEC) 100mg', 'description' => 'Antiparasitaire - filarioses lymphatiques', 'famille_id' => $apar, 'unite_id' => $cp, 'prix_vente' => 500],

            // ==================== ANTIFONGIQUES ====================
            ['nom' => 'Griséofulvine 125mg', 'description' => 'Antifongique systémique - teignes du cuir chevelu', 'famille_id' => $afng, 'unite_id' => $cp, 'prix_vente' => 300],
            ['nom' => 'Terbinafine crème 1%', 'description' => 'Antifongique topique - dermatophytoses cutanées', 'famille_id' => $afng, 'unite_id' => $cry, 'prix_vente' => 2500],
            ['nom' => 'Clotrimazole crème 1%', 'description' => 'Antifongique topique - candidoses et dermatophytoses', 'famille_id' => $afng, 'unite_id' => $cry, 'prix_vente' => 1500],
            ['nom' => 'Miconazole gel buccal 20mg/g', 'description' => 'Antifongique mucosal - muguet buccal', 'famille_id' => $afng, 'unite_id' => $gel, 'prix_vente' => 2000],
            ['nom' => 'Itraconazole 100mg', 'description' => 'Antifongique azolé - sporotrichose, candidose oesophagienne', 'famille_id' => $afng, 'unite_id' => $glu, 'prix_vente' => 1500],
            ['nom' => 'Flucytosine 500mg (5-FC)', 'description' => 'Antifongique - cryptococcose en association avec amphotéricine B', 'famille_id' => $afng, 'unite_id' => $cp, 'prix_vente' => 3000],

            // ==================== CARDIOVASCULAIRE ====================
            ['nom' => 'Labétalol 100mg', 'description' => 'Bêtabloquant mixte alpha-bêta - HTA gravidique', 'famille_id' => $card, 'unite_id' => $cp, 'prix_vente' => 400],
            ['nom' => 'Méthyldopa 250mg', 'description' => 'Antihypertenseur central - HTA de la grossesse', 'famille_id' => $card, 'unite_id' => $cp, 'prix_vente' => 300],
            ['nom' => 'Bisoprolol 5mg', 'description' => 'Bêtabloquant cardiosélectif - HTA, insuffisance cardiaque', 'famille_id' => $card, 'unite_id' => $cp, 'prix_vente' => 350],
            ['nom' => 'Spironolactone 25mg', 'description' => 'Antialdostérone - insuffisance cardiaque, HTA résistante', 'famille_id' => $diur, 'unite_id' => $cp, 'prix_vente' => 300],
            ['nom' => 'Sacubitril/Valsartan 97/103mg (Entresto)', 'description' => 'Inhibiteur de l\'angiotensine-néprylysine - insuffisance cardiaque à FEVG réduite', 'famille_id' => $card, 'unite_id' => $cp, 'prix_vente' => 5000],
            ['nom' => 'Hydrochlorothiazide 25mg', 'description' => 'Diurétique thiazidique - HTA, insuffisance cardiaque', 'famille_id' => $diur, 'unite_id' => $cp, 'prix_vente' => 100],
            ['nom' => 'Ramipril 5mg', 'description' => 'IEC - HTA, insuffisance cardiaque, néphroprotection', 'famille_id' => $card, 'unite_id' => $cp, 'prix_vente' => 300],
            ['nom' => 'Sulfate de magnésium 1g/5ml IV', 'description' => 'Anticonvulsivant - pré-éclampsie sévère, convulsions', 'famille_id' => $acv, 'unite_id' => $amp, 'prix_vente' => 800],

            // ==================== ENDOCRINOLOGIE ====================
            ['nom' => 'Glibenclamide 5mg', 'description' => 'Sulfonylurée - diabète de type 2', 'famille_id' => $diab, 'unite_id' => $cp, 'prix_vente' => 100],
            ['nom' => 'Insuline NPH (intermédiaire)', 'description' => 'Insuline basale - diabète type 1 et 2', 'famille_id' => $diab, 'unite_id' => $inj, 'prix_vente' => 3500],
            ['nom' => 'Insuline Rapide (Regular)', 'description' => 'Insuline bolus - diabète, corps cétoniques, acido-cétose', 'famille_id' => $diab, 'unite_id' => $inj, 'prix_vente' => 3000],
            ['nom' => 'Glucagon IM 1mg', 'description' => 'Hormone hyperglycémiante - hypoglycémie sévère avec inconscience', 'famille_id' => $diab, 'unite_id' => $amp, 'prix_vente' => 5000],
            ['nom' => 'Lévothyroxine 50µg', 'description' => 'Hormone thyroïdienne - hypothyroïdie', 'famille_id' => $familles['Vitamines'] ?? $defaultFamille, 'unite_id' => $cp, 'prix_vente' => 200],
            ['nom' => 'Lévothyroxine 100µg', 'description' => 'Hormone thyroïdienne - hypothyroïdie adulte', 'famille_id' => $familles['Vitamines'] ?? $defaultFamille, 'unite_id' => $cp, 'prix_vente' => 300],
            ['nom' => 'Hydrocortisone 10mg', 'description' => 'Corticoïde oral - insuffisance surrénale', 'famille_id' => $cort, 'unite_id' => $cp, 'prix_vente' => 200],
            ['nom' => 'Fludrocortisone 0.1mg', 'description' => 'Minéralocorticoïde - insuffisance surrénale primaire (Addison)', 'famille_id' => $cort, 'unite_id' => $cp, 'prix_vente' => 800],
            ['nom' => 'Allopurinol 300mg', 'description' => 'Hypo-uricémiant - hyperuricémie, syndrome de lyse tumorale', 'famille_id' => $familles['Antidiabétiques'] ?? $defaultFamille, 'unite_id' => $cp, 'prix_vente' => 200],
            ['nom' => 'Rasburicase IV 7.5mg', 'description' => 'Uricolytique recombinant - syndrome de lyse tumorale', 'famille_id' => $familles['Antidiabétiques'] ?? $defaultFamille, 'unite_id' => $amp, 'prix_vente' => 50000],

            // ==================== NEUROLOGIE ====================
            ['nom' => 'Lamotrigine 50mg', 'description' => 'Antiépileptique - épilepsie focale et généralisée', 'famille_id' => $acv, 'unite_id' => $cp, 'prix_vente' => 500],
            ['nom' => 'Lamotrigine 100mg', 'description' => 'Antiépileptique de maintenance - épilepsie', 'famille_id' => $acv, 'unite_id' => $cp, 'prix_vente' => 800],
            ['nom' => 'Lévétiracétam 500mg', 'description' => 'Antiépileptique - crises partielles et généralisées', 'famille_id' => $acv, 'unite_id' => $cp, 'prix_vente' => 700],
            ['nom' => 'Éthosuximide 250mg', 'description' => 'Antiépileptique - absences typiques (petit mal)', 'famille_id' => $acv, 'unite_id' => $glu, 'prix_vente' => 600],
            ['nom' => 'Gabapentine 300mg', 'description' => 'Antiépileptique / antalgique neuropathique - douleurs neuropathiques', 'famille_id' => $acv, 'unite_id' => $glu, 'prix_vente' => 400],
            ['nom' => 'Prégabaline 75mg', 'description' => 'Antiépileptique / antalgique neuropathique', 'famille_id' => $acv, 'unite_id' => $glu, 'prix_vente' => 600],
            ['nom' => 'Amitriptyline 25mg', 'description' => 'Antidépresseur tricyclique - douleur neuropathique, dépression', 'famille_id' => $familles['Antidépresseurs'] ?? 21, 'unite_id' => $cp, 'prix_vente' => 200],
            ['nom' => 'Lorazépam IV 4mg/2ml', 'description' => 'Benzodiazépine IV - état de mal convulsif', 'famille_id' => $acv, 'unite_id' => $amp, 'prix_vente' => 1500],

            // ==================== VITAMINES / MICRONUTRIMENTS ====================
            ['nom' => 'Vitamine K1 (Phytoménadione) 1mg', 'description' => 'Vitamine K injectable - hémorragie du nouveau-né, prévention', 'famille_id' => $vit, 'unite_id' => $amp, 'prix_vente' => 800],
            ['nom' => 'Pyridoxine (B6) 50mg', 'description' => 'Vitamine B6 - prévention neuropathie sous isoniazide', 'famille_id' => $vit, 'unite_id' => $cp, 'prix_vente' => 100],
            ['nom' => 'Zinc sulfate 20mg', 'description' => 'Oligo-élément - diarrhée aiguë enfant, carence en zinc', 'famille_id' => $vit, 'unite_id' => $cp, 'prix_vente' => 150],
            ['nom' => 'N-Acétylcystéine IV 200mg/ml', 'description' => 'Antidote - intoxication au paracétamol, mucolytique', 'famille_id' => $familles['Antidote'] ?? $familles['Antidotes'] ?? $defaultFamille, 'unite_id' => $amp, 'prix_vente' => 3000],

            // ==================== ONCOLOGIE / SOINS PALLIATIFS ====================
            ['nom' => 'Ondansétron 8mg', 'description' => 'Antiémétique sérotoninergique (5-HT3) - chimiothérapie, post-opératoire', 'famille_id' => $familles['Antiémétique'] ?? $familles['Antispasmodiques'] ?? $defaultFamille, 'unite_id' => $cp, 'prix_vente' => 500],
            ['nom' => 'Ondansétron 4mg/2ml IV', 'description' => 'Antiémétique injectable - vomissements chimio et post-op', 'famille_id' => $familles['Antiémétique'] ?? $familles['Antispasmodiques'] ?? $defaultFamille, 'unite_id' => $amp, 'prix_vente' => 1500],
            ['nom' => 'Aprépitant 125mg', 'description' => 'Antagoniste NK1 - prévention vomissements post-chimio', 'famille_id' => $familles['Antiémétique'] ?? $familles['Antispasmodiques'] ?? $defaultFamille, 'unite_id' => $glu, 'prix_vente' => 8000],
            ['nom' => 'MESNA 400mg', 'description' => 'Uroprotecteur - prévention cystite hémorragique sous cyclophosphamide', 'famille_id' => $familles['Antidote'] ?? $defaultFamille, 'unite_id' => $amp, 'prix_vente' => 3000],
            ['nom' => 'Morphine orale 10mg', 'description' => 'Opioïde fort - douleurs sévères cancéreuses et non-cancéreuses', 'famille_id' => $familles['Antidouleurs'] ?? $defaultFamille, 'unite_id' => $cp, 'prix_vente' => 600],
            ['nom' => 'Tramadol 50mg', 'description' => 'Analgésique opioïde faible - douleurs modérées à sévères', 'famille_id' => $familles['Antidouleurs'] ?? $defaultFamille, 'unite_id' => $glu, 'prix_vente' => 300],

            // ==================== URGENCES ====================
            ['nom' => 'Adrénaline (Épinéphrine) 1mg/ml IV', 'description' => 'Catécholamine - anaphylaxie sévère, arret cardiaque', 'famille_id' => $familles['Antalhypertenseurs'] ?? $card, 'unite_id' => $amp, 'prix_vente' => 1500],
            ['nom' => 'Adrénaline 0.3mg auto-injecteur IM', 'description' => 'Adrénaline auto-injectable - anaphylaxie (EpiPen)', 'famille_id' => $card, 'unite_id' => $inj, 'prix_vente' => 8000],
            ['nom' => 'Atropine 1mg/ml IV', 'description' => 'Anticholinergique - bradycardie, intoxication organophosphorés', 'famille_id' => $familles['Antidote'] ?? $defaultFamille, 'unite_id' => $amp, 'prix_vente' => 500],
            ['nom' => 'Pralidoxime IV 1g', 'description' => 'Réactivateur cholinestérase - intoxication aux organophosphorés', 'famille_id' => $familles['Antidote'] ?? $defaultFamille, 'unite_id' => $amp, 'prix_vente' => 5000],
            ['nom' => 'Charbon activé 50g', 'description' => 'Adsorbant - intoxications médicamenteuses aiguës (< 1h)', 'famille_id' => $familles['Adsorbant'] ?? $defaultFamille, 'unite_id' => $sac, 'prix_vente' => 1500],
            ['nom' => 'Naloxone IV 0.4mg/ml', 'description' => 'Antagoniste opioïdes - overdose narcotiques', 'famille_id' => $familles['Antagoniste des opioïdes'] ?? $defaultFamille, 'unite_id' => $amp, 'prix_vente' => 4000],
            ['nom' => 'Flumazénil IV 0.5mg', 'description' => 'Antagoniste benzodiazépines - intoxication BZD', 'famille_id' => $familles['Antagoniste des opioïdes'] ?? $defaultFamille, 'unite_id' => $amp, 'prix_vente' => 3500],
            ['nom' => 'Bicarbonate de sodium 8.4% IV', 'description' => 'Tampon alcalin - acidose métabolique sévère, intoxications', 'famille_id' => $familles['Alcalin (buffer)'] ?? $familles['Alcalin'] ?? $defaultFamille, 'unite_id' => $amp, 'prix_vente' => 800],
            ['nom' => 'Ringer Lactate 500ml', 'description' => 'Soluté cristalloïde - réhydratation IV, remplissage vasculaire', 'famille_id' => $familles['Diurétiques'] ?? 11, 'unite_id' => $ml, 'prix_vente' => 1200],
            ['nom' => 'Glucose 30% 250ml IV', 'description' => 'Soluté hypertonique - hypoglycémie sévère', 'famille_id' => $familles['Glucose hypertonique'] ?? $defaultFamille, 'unite_id' => $ml, 'prix_vente' => 2000],

            // ==================== DERMATOLOGIE ====================
            ['nom' => 'Perméthrine crème 5%', 'description' => 'Antiparasitaire topique - gale, poux (alternative)', 'famille_id' => $apar, 'unite_id' => $cry, 'prix_vente' => 2000],
            ['nom' => 'Benzoate de benzyle lotion 25%', 'description' => 'Scabicide - gale sarcoptique', 'famille_id' => $apar, 'unite_id' => $ml, 'prix_vente' => 1200],
            ['nom' => 'Adapalène gel 0.1%', 'description' => 'Rétinoïde topique - acné vulgaire (Différine)', 'famille_id' => $familles['AINS'] ?? $defaultFamille, 'unite_id' => $gel, 'prix_vente' => 3500],
            ['nom' => 'Peroxyde de benzoyle 5% gel', 'description' => 'Antiseptique kératolytique - acné vulgaire', 'famille_id' => $familles['AINS'] ?? $defaultFamille, 'unite_id' => $gel, 'prix_vente' => 2500],

            // ==================== IMMUNOLOGIE ====================
            ['nom' => 'Immunoglobulines IV 5g (IgIV)', 'description' => 'Immunomodulateur - maladie de Kawasaki, déficits immunitaires', 'famille_id' => $vac, 'unite_id' => $amp, 'prix_vente' => 80000],
            ['nom' => 'Antitoxine diphtérique', 'description' => 'Immunoglobuline spécifique - traitement diphtérie', 'famille_id' => $vac, 'unite_id' => $amp, 'prix_vente' => 50000],
            ['nom' => 'Infliximab IV 100mg', 'description' => 'Anti-TNF alpha - Kawasaki résistant, maladies inflammatoires', 'famille_id' => $cort, 'unite_id' => $amp, 'prix_vente' => 150000],

            // ==================== PNEUMOLOGIE ====================
            ['nom' => 'Salbutamol 100µg inhalateur (ventoline)', 'description' => 'Bêta-2 agoniste - bronchospasme, asthme, anaphylaxie respiratoire', 'famille_id' => $bron, 'unite_id' => $inh, 'prix_vente' => 2500],
            ['nom' => 'Beclométasone inhalée 250µg', 'description' => 'Corticoïde inhalé - asthme persistant, BPCO', 'famille_id' => $cort, 'unite_id' => $inh, 'prix_vente' => 4000],
            ['nom' => 'Prednisolone 20mg', 'description' => 'Corticoïde oral - anti-inflammatoire, immunosuppresseur', 'famille_id' => $cort, 'unite_id' => $cp, 'prix_vente' => 150],

            // ==================== HÉPATO-GASTRO ====================
            ['nom' => 'Entécavir 0.5mg', 'description' => 'Antiviral - hépatite B chronique (alternative ténofovir)', 'famille_id' => $avir, 'unite_id' => $cp, 'prix_vente' => 2000],
            ['nom' => 'Oxamniquine 250mg', 'description' => 'Anthelminthique - schistosomiase à S. mansoni', 'famille_id' => $apar, 'unite_id' => $glu, 'prix_vente' => 2000],
        ];

        $created = 0;
        $skipped = 0;

        foreach ($medicaments as $med) {
            $nomLower = mb_strtolower(trim($med['nom']));

            // Vérifier si un médicament similaire existe déjà (comparaison normalisée)
            $exists = DB::table('medicaments')
                ->whereRaw('LOWER(nom) LIKE ?', ['%' . mb_substr($nomLower, 0, 12) . '%'])
                ->exists();

            if (!$exists) {
                DB::table('medicaments')->insert([
                    'uuid'        => (string) Str::uuid(),
                    'nom'         => $med['nom'],
                    'description' => $med['description'],
                    'stock'       => 100,
                    'stock_min'   => 10,
                    'prix_achat'  => round($med['prix_vente'] * 0.7),
                    'prix_vente'  => $med['prix_vente'],
                    'unite_id'    => $med['unite_id'],
                    'famille_id'  => $med['famille_id'],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
                $created++;
            } else {
                $skipped++;
            }
        }

        $this->command->info("✅ Médicaments : {$created} ajoutés, {$skipped} déjà présents.");
        $this->command->info("   Total médicaments en BD : " . DB::table('medicaments')->count());
    }
}
