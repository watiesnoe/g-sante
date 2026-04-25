<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Enrichit les protocoles existants avec les signes cliniques
 * et les examens diagnostics (paracliniques).
 * Idempotent : utilise uniquement des UPDATE sur les protocoles existants.
 */
class SignesDiagnosticsSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // ==================== PALUDISME ====================
            'Paludisme simple' => [
                'signes'      => 'Fièvre, frissons, céphalées, courbatures, sueurs profuses, parfois vomissements et anorexie',
                'diagnostics' => 'TDR (test de diagnostic rapide) positif ou goutte épaisse/frottis sanguin positif à Plasmodium',
            ],
            'Paludisme grave' => [
                'signes'      => 'Altération de la conscience, détresse respiratoire, convulsions, choc, ictère, hémoglobinurie noir',
                'diagnostics' => 'Goutte épaisse positive + critères OMS de gravité; parasitémie élevée, hypoglycémie (<2.2 mmol/L)',
            ],
            'Paludisme non compliqué (P. falciparum)' => [
                'signes'      => 'Fièvre, céphalées, frissons, sueurs; pas de signe de gravité',
                'diagnostics' => 'TDR positif ou frottis/GE confirmant P. falciparum; parasitémie < 5%',
            ],
            'Paludisme grave (hospitalier)' => [
                'signes'      => 'Détresse respiratoire, coma, convulsions, choc, ictère, hypoglycémie, anémie sévère',
                'diagnostics' => 'GE positive + critères OMS; NFS, glycémie, créatinine, bilirubine, ALAT',
            ],
            'Paludisme grave (alternative)' => [
                'signes'      => 'Signes de gravité OMS: prostration, convulsions, coma ou hypoglycémie',
                'diagnostics' => 'Goutte épaisse positive + signes de gravité OMS',
            ],
            'Paludisme à P. vivax' => [
                'signes'      => 'Accès fébrile périodique (tierces = J3), splénomégalie, anémie progressive',
                'diagnostics' => 'Frottis identifiant Plasmodium vivax, TDR vivax positif, sérologie si doute',
            ],
            'P. vivax / P. ovale (guérison radicale)' => [
                'signes'      => 'Rechutes fébriles à distance du traitement initial, paludisme à vivax/ovale confirmé',
                'diagnostics' => 'Frottis confirmatoire, test G6PD avant primaquine',
            ],
            'Prophylaxie paludisme (>5 kg)' => [
                'signes'      => 'Voyage en zone endémique imminente (pas de signes cliniques - prévention)',
                'diagnostics' => 'Évaluation du risque selon destination, durée, saison et immunodépression',
            ],

            // ==================== INFECTIONS RESPIRATOIRES ====================
            'Pneumonie (non grave)' => [
                'signes'      => 'Toux, fièvre, tachypnée, tirage intercostal, crépitants à l\'auscultation; SpO2 ≥ 90%',
                'diagnostics' => 'Examen clinique, SpO2, radiographie thoracique si disponible (foyer de condensation)',
            ],
            'Pneumonie simple' => [
                'signes'      => 'Toux productive, fièvre, douleur thoracique, crépitants; signes de gravité absents',
                'diagnostics' => 'Radiographie thoracique (foyer alvéolaire), SpO2, NFS (hyperleucocytose)',
            ],
            'Pneumonie sévère' => [
                'signes'      => 'Détresse respiratoire sévère, SpO2 < 90%, cyanose, impossibilité de boire ou manger',
                'diagnostics' => 'SpO2, radiographie thoracique, NFS, CRP/PCT, hémocultures avant ATB',
            ],
            'Pneumopathie bactérienne' => [
                'signes'      => 'Expectoration purulente, fièvre, douleur thoracique, encombrement bronchique, foyer auscultatoire',
                'diagnostics' => 'Radiographie thoracique (foyer), NFS (hyperleucocytose), CRP, ECBC',
            ],
            'Tuberculose (pulmonaire)' => [
                'signes'      => 'Toux chronique > 3 semaines, sudations nocturnes, amaigrissement, hémoptysies, fièvre vespérale',
                'diagnostics' => 'Radiographie thoracique (infiltrats apicaux, cavernes), GeneXpert MTB/RIF, examen direct et culture des crachats (BAAR)',
            ],
            'Tuberculose pulmonaire' => [
                'signes'      => 'Toux chronique > 3 semaines, sudations nocturnes, amaigrissement, hémoptysies, fièvre vespérale',
                'diagnostics' => 'GeneXpert MTB/RIF, BAAR sur crachats, radiographie thoracique, culture sur milieu Löwenstein-Jensen',
            ],
            'Tuberculose latente' => [
                'signes'      => 'Asymptomatique; contage tuberculeaux récent ou immunodépression (VIH, diabète, corticoïdes)',
                'diagnostics' => 'IDR tuberculinique (> 10mm), IGRA (Quantiferon-TB Gold) positif, radiographie thoracique normale',
            ],
            'Tuberculose multirésistante (MDR-TB)' => [
                'signes'      => 'Symptômes tuberculeux persistant malgré traitement standard (rechute, persistance, échec)',
                'diagnostics' => 'GeneXpert MTB/RIF: résistance rifampicine; antibiogramme: résistance à R + H confirmée',
            ],
            'Angine streptococcique' => [
                'signes'      => 'Gorge rouge, amygdales hypertrophiées avec exsudat blanc, fièvre élevée ≥ 38.5°C, adénopathie sous-angulo-maxillaire',
                'diagnostics' => 'TDR angine (Strep A) positif; culture pharyngée si doute; score de McIsaac > 2',
            ],
            'Otite moyenne aiguë' => [
                'signes'      => 'Otalgie, fièvre, otorrhée possible, tympan rouge bombé à l\'otoscopie',
                'diagnostics' => 'Otoscopie (tympan rouge bombé, opacifié, épanchement), pas de bilan biologique systématique',
            ],
            'Septicémie néonatale' => [
                'signes'      => 'Instabilité thermique, hypotonie, refus du sein, détresse respiratoire, ictère précoce, convulsions',
                'diagnostics' => 'NFS (hyperleucocytose ou leucopénie), hémocultures, ponction lombaire, CRP, PCT',
            ],
            'Coqueluche' => [
                'signes'      => 'Quintes de toux spasmodiques avec reprise inspiratoire (chant du coq), vomissements post-tussifs, cyanose',
                'diagnostics' => 'PCR Bordetella pertussis sur écouvillon nasopharyngé (< 3 semaines), sérologie (> 3 semaines)',
            ],

            // ==================== INFECTIONS ORL / YEUX ====================
            'Trachome (conjonctivite chronique)' => [
                'signes'      => 'Conjonctivite folliculaire chronique, trichiasis, opacification cornéenne, cécité progressive',
                'diagnostics' => 'Examen clinique (classification OMS: TF, TI, TS, TT, CO), PCR Chlamydia trachomatis',
            ],
            'Kératite herpétique' => [
                'signes'      => 'Douleur oculaire, larmoiement, photophobie, ulcère cornéen dendritique à la fluorescéine',
                'diagnostics' => 'Lampe à fente (ulcère dendritique caractéristique), PCR HSV sur écouvillon cornéen',
            ],

            // ==================== INFECTIONS DIGESTIVES ====================
            'Choléra' => [
                'signes'      => 'Diarrhée d\'apparition brutale (eau de riz), vomissements en projectile, déshydratation sévère rapide, oligurie',
                'diagnostics' => 'Coproculture (Vibrio cholerae O1/O139), test rapide cholera, ionogramme (hypokaliémie)',
            ],
            'Shigellose' => [
                'signes'      => 'Diarrhée sanglante et muqueuse, ténesme, coliques intenses, fièvre élevée, déshydratation',
                'diagnostics' => 'Coproculture (Shigella spp.), examen microscopique des selles (leucocytes, GR)',
            ],
            'Typhoïde (fièvre typhoïde)' => [
                'signes'      => 'Fièvre progressivement élevée (en plateau), céphalées, dissociation pouls-température, splénomégalie, tuphos',
                'diagnostics' => 'Hémocultures (J1-J10), sérologie Widal (>160), coproculture (J10-J30), NFS (leucopénie relative)',
            ],
            'Typhoïde' => [
                'signes'      => 'Fièvre en plateau, céphalées, abdomen sensible, tuphos, roséoles typhiques, splénomégalie',
                'diagnostics' => 'Hémocultures, sérologie Widal/Félix, NFS (leucopénie, anémie), coproculture',
            ],
            'Amibiase intestinale' => [
                'signes'      => 'Diarrhée glaire-sanglante, ténesme, douleurs abdominales diffuses à type de colique',
                'diagnostics' => 'Examen parasitologique des selles (Entamoeba histolytica), sérologie amibienne si abcès',
            ],
            'Giardiase' => [
                'signes'      => 'Diarrhée chronique graisseuse, ballonnements, douleurs épigastriques, malabsorption, stéatorrhée',
                'diagnostics' => 'EPS (kystes ou trophozoïtes de Giardia), Ag Giardia dans les selles (ELISA)',
            ],
            'Gastro-entérite aiguë' => [
                'signes'      => 'Diarrhée aiguë, vomissements, nausées, crampes abdominales, fièvre modérée, déshydratation',
                'diagnostics' => 'Évaluation degré déshydratation (signe du pli cutané, fontanelle), coproculture si sang ou fièvre élevée',
            ],
            'Gastro-entérite' => [
                'signes'      => 'Diarrhée aiguë liquidienne, vomissements, crampes abdominales, fièvre légère à modérée',
                'diagnostics' => 'Clinique suffisante; coproculture si sang dans selles, fièvre >38.5°C ou immunodéprimé',
            ],

            // ==================== INFECTIONS URINAIRES ====================
            'Cystite aiguë' => [
                'signes'      => 'Brûlures mictionnelles, pollakiurie, douleur sus-pubienne, urines troubles ou malodorantes',
                'diagnostics' => 'Bandelette urinaire (leucocytes + nitrites), ECBU de confirmation (> 10^3 UFC/mL)',
            ],
            'Infection Urinaire (Cystite)' => [
                'signes'      => 'Brûlures mictionnelles, pollakiurie, dysurie, pesanteur pelvienne, sans fièvre',
                'diagnostics' => 'BU (leucocytes/nitrites positifs), ECBU (≥ 10^3 UFC/mL, leucocyturie ≥ 10^4/mL)',
            ],
            'Pyélonéphrite aiguë' => [
                'signes'      => 'Fièvre élevée ≥ 38.5°C, frissons, douleur lombaire, nausées, ECBU positif',
                'diagnostics' => 'ECBU positif (≥ 10^4 UFC/mL), NFS (hyperleucocytose), CRP, hémocultures, échographie rénale',
            ],

            // ==================== IST ====================
            'Gonococcie (IST)' => [
                'signes'      => 'Écoulement urétral purulent jaune-verdâtre, brûlures mictionnelles, urétrite, conjonctivite néonatale',
                'diagnostics' => 'Examen direct (diplocoques Gram négatif intracellulaires), culture gonocoque, PCR Neisseria gonorrhoeae',
            ],
            'Syphilis' => [
                'signes'      => 'Primaire: chancre induré indolore; Secondaire: roséole, syphilides; Tertiaire: gommes, atteinte cardiovasculaire',
                'diagnostics' => 'VDRL/RPR (tests non-tréponémiques) + TPHA/FTA-ABS (tests tréponémiques) positifs',
            ],
            'Syphilis primaire' => [
                'signes'      => 'Chancre induré non douloureux au point d\'inoculation, adénopathie régionale satellite',
                'diagnostics' => 'TPHA positif, VDRL positif, examen direct du chancre (microscopie à fond noir)',
            ],

            // ==================== INFECTIONS FONGIQUES ====================
            'Candidose oropharyngée (muguet)' => [
                'signes'      => 'Plaques blanches crémeuses douloureuses sur langue/muqueuse buccale, dysphagie légère',
                'diagnostics' => 'Examen clinique (plaques pseudo-membraneuses), frottis buccal avec examen direct (levures bourgeonnantes)',
            ],
            'Candidose oesophagienne' => [
                'signes'      => 'Dysphagie, odynophagie, fièvre; survient souvent dans contexte d\'immunodépression sévère (VIH, CD4 < 100)',
                'diagnostics' => 'Fibroscopie oesogastroduodénale (placards blanchâtres), biopsie + culture Candida, CD4 si VIH',
            ],
            'Candidose systémique' => [
                'signes'      => 'Fièvre sous ATB large spectre, choc septique réfractaire, emboles fongiques oculaires (endophtalmie)',
                'diagnostics' => 'Hémocultures (flacons mycologiques), bêta-D-glucane sérique, fond d\'oeil (endophtalmie)',
            ],
            'Cryptococcose (neuroméningée)' => [
                'signes'      => 'Céphalées chroniques progressives, fièvre modérée, raideur méningée, confusion; contexte VIH (CD4 < 100)',
                'diagnostics' => 'PL: Ag cryptococcique dans LCR (test agglutination au latex), encre de Chine positive, culture LCR',
            ],
            'Dermatophytose cutanée' => [
                'signes'      => 'Lésions annulaires prurigineuses à bords actifs sur peau glabre, pied d\'athlète (intertrigo inter-orteils)',
                'diagnostics' => 'Examen direct au KOH des squames (filaments mycéliens), culture sur milieu Sabouraud',
            ],
            'Teignes (tinea capitis)' => [
                'signes'      => 'Plaques alopéciques squameuses du cuir chevelu, cassure des cheveux, parfois kérion inflammatoire',
                'diagnostics' => 'Lampe de Wood (fluorescence selon espèce), examen direct KOH, culture sur Sabouraud',
            ],

            // ==================== INFECTIONS VIRALES & CHRONIQUES ====================
            'Herpès simplex (HSV)' => [
                'signes'      => 'Vésicules douloureuses groupées en bouquet sur muqueuses/peau, brûlures, adénopathies régionales',
                'diagnostics' => 'Clinique typique; PCR HSV sur frottis lésions (confirmation), sérologie IgM/IgG HSV',
            ],
            'Zona (Herpès zoster)' => [
                'signes'      => 'Éruption vésiculeuse douloureuse en métamère unilatéral, douleurs neuropathiques précédant l\'éruption',
                'diagnostics' => 'Clinique (trajet métamérique caractéristique); PCR VZV sur vésicules si doute',
            ],
            'Grippe (H1N1)' => [
                'signes'      => 'Fièvre élevée d\'apparition brutale, myalgies intenses, céphalées, rhinorrhée, toux sèche',
                'diagnostics' => 'TDR influenza A/B nasal, RT-PCR Influenza (si pneumonie ou forme grave)',
            ],
            'Grippe' => [
                'signes'      => 'Début brutal: fièvre > 38.5°C, courbatures intenses, céphalées, toux sèche, asthénie profonde',
                'diagnostics' => 'Test rapide Influenza A/B (sensibilité 50-70%), RT-PCR nasopharyngée si forme sévère',
            ],
            'VIH/SIDA' => [
                'signes'      => 'Primo-infection: fièvre, adénopathies, pharyngite, éruption; Chronique: amaigrissement, infections opportunistes',
                'diagnostics' => 'Sérologie VIH (Ag-Ac 4e génération), charge virale HIV ARN, CD4 (SIDA si <200/mm³)',
            ],
            'PTME (prévention mère-enfant)' => [
                'signes'      => 'Femme enceinte VIH positive (traitement préventif) pour éviter transmission mère-enfant',
                'diagnostics' => 'Sérologie VIH positive pendant grossesse, charge virale, CD4, test PCR du nourrisson à 6 semaines',
            ],
            'Hépatite B' => [
                'signes'      => 'Souvent asymptomatique; forme aiguë: ictère, fatigue, anorexie, hépatomégalie, urines foncées',
                'diagnostics' => 'AgHBs positif > 6 mois (chronicité), ALAT élevées, charge virale VHB, AgHBe, anti-HBc IgG',
            ],
            'Hémorragie du nouveau-né (MK)' => [
                'signes'      => 'Saignements (ombilical, crânien, digestif) chez nouveau-né dans les 24h-3 semaines de vie',
                'diagnostics' => 'TP allongé, TCA allongé, fibrinogène normal (déficit en facteurs vitamine K-dépendants)',
            ],

            // ==================== PARASITOSES ====================
            'Leishmaniose viscérale (Kala-azar)' => [
                'signes'      => 'Fièvre ondulante prolongée, splénomégalie majeure, amaigrissement, pancytopénie, teint grisâtre',
                'diagnostics' => 'Sérologie (IFI, rK39), examen direct moelle osseuse ou rate (amastigotes), PCR Leishmania',
            ],
            'Filariose lymphatique' => [
                'signes'      => 'Adénolymphangite récidivante, fièvre, lymphoedème progressif membres inférieurs, hydrocèle',
                'diagnostics' => 'Microfilarémie nocturne (frottis sanguin nuit), Ag circulant (TDR filarémie), sérologie',
            ],
            'Onchocercose' => [
                'signes'      => 'Prurit intense, nodules sous-cutanés (onchocercomes), lésions cutanées (dépigmentation), cécité des rivières',
                'diagnostics' => 'Biopsie cutanée (microfilaires), slit-lamp (microfilaires dans chambre antérieure), Mazzotti test',
            ],
            'Schistosomiase' => [
                'signes'      => 'Hématurie (S. haematobium), diarrhée sanglante (S. mansoni), hépatomégalie, fièvre de Katayama',
                'diagnostics' => 'ECBU (oeufs à éperon terminal - S. haematobium), EPS (oeufs à éperon latéral - S. mansoni), sérologie',
            ],
            'Ascaridiose (Ascaris)' => [
                'signes'      => 'Souvent asymptomatique; troubles digestifs, expulsion de vers, complications: occlusion, migration biliaire',
                'diagnostics' => 'EPS: oeufs d\'Ascaris lumbricoides (ovales à mammillons), parfois ver adulte dans selles',
            ],
            'Amibiase intestinale' => [
                'signes'      => 'Diarrhée glaire-sanglante, ténesme, douleurs abdominales en cadre colique',
                'diagnostics' => 'EPS frais (amibes hématophages), Ag ELISA Entamoeba histolytica, sérologie (si abcès amibien)',
            ],

            // ==================== CARDIOLOGIE / RHUMATOLOGIE ====================
            'Rhumatisme articulaire aigu' => [
                'signes'      => 'Polyarthrite migrante, fièvre, cardite (insuffisance mitrale/aortique), chorée de Sydenham, nodules',
                'diagnostics' => 'Critères de Jones: ASLO élevé, CRP élevée, VS accélérée, ECG, échocardiographie',
            ],
            'Maladie de Kawasaki' => [
                'signes'      => 'Fièvre > 5 jours + conjonctivite, rash polymorphe, chéilite, adénopathie cervicale, érythème palmo-plantaire',
                'diagnostics' => 'NFS (hyperleucocytose, thrombocytose), CRP, VS, Echocardiographie (anévrysmes coronaires)',
            ],
            'Rhumatisme psoriasique' => [
                'signes'      => 'Arthrite asymétrique, psoriasis cutané ou unguéal, dactylite, enthésite',
                'diagnostics' => 'Clinique + bilan inflammatoire (CRP, VS), radiographies articulaires, HLA-B27',
            ],
            'Hypertension artérielle (HTA)' => [
                'signes'      => 'PA ≥ 140/90 mmHg à plusieurs mesures; céphalées occipitales matinales, vertiges, parfois asymptomatique',
                'diagnostics' => 'Mesure TA répétée, MAPA, bilan rénal (créatinine, ECBU), ionogramme, glycémie, ECG, fond d\'oeil',
            ],
            'Hypertension gravidique' => [
                'signes'      => 'PA ≥ 140/90 mmHg après 20 SA; oedèmes, prise de poids rapide, céphalées, épigastralgies (pré-éclampsie)',
                'diagnostics' => 'TA répétée, protéinurie (> 300mg/24h = pré-éclampsie), NFS (thrombopénie), ALAT, créatinine',
            ],
            'Insuffisance cardiaque' => [
                'signes'      => 'Dyspnée effort puis repos, orthopnée, OAP, oedèmes membres inférieurs, asthénie majeure',
                'diagnostics' => 'ECG, radiographie thoracique (cardiomégalie, syndrome interstitiel), Echocardiographie (FEVG), BNP/NT-proBNP',
            ],

            // ==================== ENDOCRINOLOGIE ====================
            'Diabète de type 1' => [
                'signes'      => 'Polyurie, polydipsie, polyphagie avec amaigrissement rapide; souvent début par acidocétose chez l\'enfant',
                'diagnostics' => 'Glycémie à jeun ≥ 7.0 mmol/L ou glycémie aléatoire ≥ 11.1 mmol/L, HbA1c ≥ 6.5%, auto-anticorps (ICA, anti-GAD)',
            ],
            'Diabète de type 2' => [
                'signes'      => 'Souvent asymptomatique; ou polyurie, polydipsie, asthénie, infections récidivantes, troubles visuels',
                'diagnostics' => 'Glycémie à jeun ≥ 7.0 mmol/L (x2) ou glycémie 2h HGPO ≥ 11.1 mmol/L, HbA1c ≥ 6.5%',
            ],
            'Diabète de type 2 (>10 ans)' => [
                'signes'      => 'Souvent asymptomatique; dépistage systématique chez enfant obèse > 10 ans avec facteurs de risque',
                'diagnostics' => 'Glycémie à jeun ≥ 7.0 mmol/L, HGPO, HbA1c ≥ 6.5%',
            ],
            'Hypoglycémie sévère' => [
                'signes'      => 'Pâleur, sueurs, tremblements, faim intense, tachycardie; puis confusion, convulsions, coma si sévère',
                'diagnostics' => 'Glycémie capillaire < 2.8 mmol/L (<50 mg/dL); confirmer par glycémie veineuse',
            ],
            'Hypothyroïdie' => [
                'signes'      => 'Asthénie, frilosité, constipation, prise de poids, bradycardie, myxoedème, ralentissement psychomoteur',
                'diagnostics' => 'TSH élevée (hypothyroïdie primaire), T4L basse, auto-anticorps anti-TPO/anti-thyroglobuline',
            ],
            'Insuffisance surrénale (cortisol)' => [
                'signes'      => 'Asthénie, hypotension orthostatique, amaigrissement, mélanodermie, nausées, hypoglycémie',
                'diagnostics' => 'Cortisol 8h < 140 nmol/L, test Synacthène (cortisol < 500 nmol/L à 60min), ACTH élevée',
            ],

            // ==================== NEUROLOGIE ====================
            'Méningite bactérienne' => [
                'signes'      => 'Fièvre élevée, céphalées intenses, raideur de nuque, vomissements en jets, photophobie, purpura (méningocoque)',
                'diagnostics' => 'Ponction lombaire (LCR louche/purulent): hypercytose neutrophiles, hyperprotéinorachie, hypoglycorachie; cultures, PCR',
            ],
            'Méningite' => [
                'signes'      => 'Fièvre, syndrome méningé (céphalées, raideur nuque, photophobie), troubles de la conscience',
                'diagnostics' => 'PL: LCR, cytochimie (protein, glucose), examen direct + culture, PCR méningocoque/pneumocoque',
            ],
            'État de mal convulsif' => [
                'signes'      => 'Convulsions continues > 5 minutes ou répétées sans reprise de conscience entre crises',
                'diagnostics' => 'Glycémie (hypoglycémie!), ionogramme, EEG si dispo, NFS, bilan infectieux, TDM si traumatisme ou premier épisode',
            ],

            // ==================== ÉPILEPSIE ====================
            'Épilepsie (crises partielles)' => [
                'signes'      => 'Mouvements rythmiques localisés (un membre), automatismes, hallucinations sensorielles, déviation tête/yeux',
                'diagnostics' => 'EEG (activité épileptiforme focale), IRM cérébrale (étiologie), biologie standard',
            ],
            'Épilepsie (crises généralisées tonico-cloniques)' => [
                'signes'      => 'Perte de conscience, chute, phase tonique puis clonique, morsure de langue, perte d\'urine, confusion post-ictale',
                'diagnostics' => 'EEG (décharges généralisées), IRM cérébrale, biologie (Na, K, glycémie, calcémie, toxiques)',
            ],
            'Épilepsie (crises généralisées)' => [
                'signes'      => 'Perte de conscience soudaine, convulsions tonico-cloniques bilatérales, confusion post-critique',
                'diagnostics' => 'EEG intercritique, IRM cérébrale, bilan métabolique, toxiques',
            ],

            // ==================== DERMATOLOGIE ====================
            'Impétigo' => [
                'signes'      => 'Vésicules puis croûtes mielleuses jaunâtres sur visage (periorificielles) et membres, prurit',
                'diagnostics' => 'Clinique; prélèvement bactériologique si doute ou mauvaise réponse (Staphylococcus aureus, Streptococcus)',
            ],
            'Impétigo (superficiel)' => [
                'signes'      => 'Vésicules superficielles à paroi fine qui éclatent rapidement, croûtes mielleuses; pas de fièvre',
                'diagnostics' => 'Clinique; pas de bilan systématique (prélèvement si récidive ou résistance)',
            ],
            'Infection staphylococcique cutanée' => [
                'signes'      => 'Furoncle, anthrax, abcès sous-cutané douloureux, collection purulente, cellulite ou fasciite',
                'diagnostics' => 'Prélèvement pus (culture + antibiogramme), NFS si forme extensive, hémocultures si fièvre',
            ],
            'Sporotrichose' => [
                'signes'      => 'Lésion nodulolinéaire sur trajet lymphatique après piqûre végétale, extension lymphangitique',
                'diagnostics' => 'Culture sur milieu Sabouraud (Sporothrix schenckii), biopsie cutanée, sérologie',
            ],

            // ==================== LÈPRE ====================
            'Lèpre paucibacillaire (PB)' => [
                'signes'      => '1 à 5 lésions cutanées hypopigmentées avec anesthésie, névrite périphérique localisée',
                'diagnostics' => 'Examen clinique (classification OMS), examen direct frottis dermique (BAAR < 2+), biopsie',
            ],
            'Lèpre multibacillaire (MB)' => [
                'signes'      => '> 5 lésions cutanées, infiltration diffuse, lépromes, névrite multiplesr, bacilles nombreux',
                'diagnostics' => 'Frottis dermique (bacilles nombreux: score bacillaire ≥ 2+), biopsie cutanée, IDR à la lépromine (négative)',
            ],

            // ==================== TOXICOLOGIE ====================
            'Intoxication médicamenteuse aiguë' => [
                'signes'      => 'Variables selon substance: altération conscience, vomissements, bradycardie, convulsions, mydriase/myosis',
                'diagnostics' => 'Dosage toxicologique sanguin et urinaire, ECG, ionogramme, NFS, bilan hépatique et rénal, pH artériel',
            ],
            'Intoxication au paracétamol' => [
                'signes'      => 'Phase 1 (0-24h): nausées, vomissements; Phase 2 (24-72h): douleurs hépatiques; Phase 3: insuffisance hépatique',
                'diagnostics' => 'Paracétamolémie à H4 (nomogramme de Rumack-Matthew), ALAT/ASAT, TP, créatinine, glycémie',
            ],
            'Intoxication aux organophosphorés' => [
                'signes'      => 'Bradycardie, bronchospasme, hypersécrétion, myosis, vomissements, fasciculations, convulsions (syndrome cholinergique)',
                'diagnostics' => 'Activité cholinestérasique (érythrocytaire et plasmatique) effondrée, ECG, ionogramme',
            ],

            // ==================== ONCOLOGIE / EFFETS SECONDAIRES ====================
            'Nausées / vomissements (post-chimiothérapie)' => [
                'signes'      => 'Nausées et vomissements survenant dans les 24h (aigus) ou 2-5j (retardés) après chimiothérapie',
                'diagnostics' => 'Clinique; évaluation du potentiel émétisant du protocole de chimio (grille ASCO)',
            ],
            'Hyperuricémie (post-chimiothérapie)' => [
                'signes'      => 'Syndrome de lyse: hyperuricémie, hyperkaliémie, hyperphosphorémie, hypocalcémie, IRA oligurique',
                'diagnostics' => 'Uricémie (> 476 µmol/L), kaliémie, phosphorémie, calcémie, créatinine, LDH survenant 12-72h post-chimio',
            ],
            'Cystite hémorragique (post-cyclophosphamide)' => [
                'signes'      => 'Hématurie macroscopique (micro voir macroscopique), brûlures mictionnelles, après cyclophosphamide/ifosfamide',
                'diagnostics' => 'ECBU: hématurie sans infection (absence de germe), cytoscopie si hématurie persistante',
            ],
            'Cystite hémorragique (post‑cyclophosphamide)' => [
                'signes'      => 'Hématurie macroscopique, brûlures mictionnelles, après cyclophosphamide ou ifosfamide',
                'diagnostics' => 'ECBU: hématurie abactérienne, cytoscopie si hématurie persistante pour évaluer étendue lésions',
            ],

            // ==================== MATERNITÉ ====================
            'PTME (prévention mère‑enfant)' => [
                'signes'      => 'Femme enceinte VIH positive; prévention de la transmission au nourrisson',
                'diagnostics' => 'Sérologie VIH + pendant grossesse, charge virale, CD4; test PCR nourrisson à 6 semaines de vie',
            ],

            // ==================== PÉDIATRIE ====================
            'Hémorragie du nouveau-né (MK)' => [
                'signes'      => 'Saignements anormaux chez nourrisson: ombilical, crânien (hématome céphalique), digestif, cutanéomuqueux',
                'diagnostics' => 'TP allongé, TCA allongé, fibrinogène normal, facteurs II, VII, IX, X abaissés',
            ],
        ];

        $updated = 0;
        $notFound = 0;

        foreach ($data as $nomMaladie => $champs) {
            // Recherche exacte puis LIKE
            $maladie = DB::table('maladies')->where('nom', $nomMaladie)->first();
            if (!$maladie) {
                $pattern = '%' . mb_substr($nomMaladie, 0, 15) . '%';
                $maladie = DB::table('maladies')->where('nom', 'LIKE', $pattern)->first();
            }

            if (!$maladie) {
                $notFound++;
                continue;
            }

            $protocole = DB::table('protocole_traitements')
                ->where('maladie_id', $maladie->id)
                ->first();

            if ($protocole) {
                DB::table('protocole_traitements')
                    ->where('id', $protocole->id)
                    ->update([
                        'signes'      => $champs['signes'],
                        'diagnostics' => $champs['diagnostics'],
                        'updated_at'  => now(),
                    ]);
                $updated++;
            } else {
                $notFound++;
            }
        }

        $this->command->info("✅ Signes & Diagnostics : {$updated} protocoles mis à jour. {$notFound} non trouvés.");
    }
}
