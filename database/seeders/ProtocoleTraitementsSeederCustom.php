<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Maladie;
use App\Models\ProtocoleTraitement;

class ProtocoleTraitementsSeederCustom extends Seeder
{
    public function run(): void
    {
        $protocoles = [
            "Obstruction aiguë des voies aériennes supérieures" => [
                'titre' => "Prise en charge de l'obstruction aiguë des voies aériennes supérieures",
                'signes' => "Stridor, tirage, battement des ailes du nez, cyanose ; grades de légère à complète selon la sévérité.",
                'diagnostics' => "Corps étranger, croup viral, épiglottite, trachéite bactérienne, anaphylaxie, brûlure.",
                'traitement_principal' => "Oxygénothérapie, positionnement confortable, hospitalisation si obstruction modérée à sévère ; manœuvre de Heimlich si corps étranger.",
                'posologie_principale' => "Oxygène au débit nécessaire pour SpO2 94-98%.",
                'remarques' => "Trachéotomie en dernier recours si ventilation impossible.",
            ],
            "Rhinite (rhume) et rhinopharyngite" => [
                'titre' => "Prise en charge de la rhinite/rhinopharyngite",
                'signes' => "Écoulement/obstruction nasale, toux, fièvre modérée, mal de gorge.",
                'diagnostics' => "Diagnostic clinique ; vérifier les tympans chez l'enfant de moins de 5 ans.",
                'traitement_principal' => "Traitement symptomatique uniquement, pas d'antibiotique.",
                'posologie_principale' => "Lavage nasal au chlorure de sodium 0,9% ; paracétamol si fièvre/douleur.",
                'remarques' => "L'antibiothérapie n'accélère pas la guérison.",
            ],
            "Sinusite aiguë" => [
                'titre' => "Prise en charge de la sinusite aiguë",
                'signes' => "Douleur/pression faciale, écoulement nasal purulent, céphalées, fièvre.",
                'diagnostics' => "Clinique ; distinguer d'une rhinopharyngite simple par la durée et l'intensité des symptômes.",
                'traitement_principal' => "Traitement symptomatique ; antibiothérapie si signes de surinfection bactérienne persistants.",
                'posologie_principale' => "Paracétamol contre la douleur/fièvre.",
                'traitement_alternatif' => "amoxicilline PO",
                'posologie_alternative' => "Adulte : 1 g 3 fois/jour pendant 7 jours",
            ],
            "Angine (pharyngite) aiguë" => [
                'titre' => "Prise en charge de l'angine aiguë",
                'signes' => "Mal de gorge, fièvre, adénopathies cervicales, amygdales inflammatoires.",
                'diagnostics' => "Distinguer angine virale (majorité) et streptococcique (à traiter par antibiotique).",
                'traitement_principal' => "Traitement symptomatique dans les formes virales.",
                'posologie_principale' => "Paracétamol contre la douleur/fièvre.",
                'traitement_alternatif' => "benzathine benzylpénicilline IM (dose unique) si forme streptococcique",
                'posologie_alternative' => "Enfant < 30 kg : 600 000 UI ; ≥ 30 kg et adulte : 1 200 000 UI, dose unique",
            ],
            "Diphtérie" => [
                'titre' => "Prise en charge de la diphtérie",
                'signes' => "Angine avec fausses membranes adhérentes, fièvre, adénopathies cervicales (« cou proconsulaire »), risque d'obstruction laryngée.",
                'diagnostics' => "Clinique ; isolement du patient obligatoire.",
                'traitement_principal' => "Sérothérapie antidiphtérique en urgence + antibiothérapie + isolement respiratoire.",
                'posologie_principale' => "benzathine benzylpénicilline IM dose unique, ou érythromycine PO 7-14 jours",
                'remarques' => "Vaccination de l'entourage à vérifier/compléter.",
            ],
            "Laryngotrachéite et laryngotrachéobronchite (croup)" => [
                'titre' => "Prise en charge du croup",
                'signes' => "Toux rauque, voix enrouée, stridor, détresse respiratoire d'intensité variable.",
                'diagnostics' => "Clinique ; distinguer de l'épiglottite (fièvre élevée, hypersalivation) et de la trachéite bactérienne.",
                'traitement_principal' => "Surveillance hospitalière si sévère ; corticoïde en dose unique + adrénaline en nébulisation si sévère.",
                'posologie_principale' => "dexaméthasone IM 0,6 mg/kg dose unique",
                'traitement_alternatif' => "épinéphrine (adrénaline) en nébulisation",
                'posologie_alternative' => "0,5 ml/kg (max 5 ml)",
            ],
            "Épiglottite" => [
                'titre' => "Prise en charge de l'épiglottite",
                'signes' => "Fièvre élevée, stridor, hypersalivation, préférence pour la position assise, détresse respiratoire sévère.",
                'diagnostics' => "Urgence vitale ; ne pas examiner la gorge sans matériel de sécurisation des voies aériennes disponible.",
                'traitement_principal' => "Antibiothérapie parentérale en urgence + sécurisation des voies aériennes.",
                'posologie_principale' => "ceftriaxone IV/IM",
                'posologie_alternative' => "Enfant : 50-80 mg/kg/jour ; Adulte : 1-2 g/jour pendant 7 à 10 jours",
            ],
            "Otite moyenne aiguë (OMA)" => [
                'titre' => "Prise en charge de l'otite moyenne aiguë",
                'signes' => "Otalgie, fièvre, tympan inflammatoire ou bombé, parfois otorrhée.",
                'diagnostics' => "Otoscopie.",
                'traitement_principal' => "Antalgique systématique ; antibiotique selon l'âge et la sévérité.",
                'posologie_principale' => "amoxicilline PO",
                'posologie_alternative' => "Enfant : 80-90 mg/kg/jour en 2 prises pendant 5 jours",
            ],
            "Coqueluche" => [
                'titre' => "Prise en charge de la coqueluche",
                'signes' => "Quintes de toux prolongées avec reprise inspiratoire sifflante, vomissements post-tussifs.",
                'diagnostics' => "Clinique, contexte épidémique.",
                'traitement_principal' => "Antibiothérapie précoce pour réduire la contagiosité + soins de soutien.",
                'posologie_principale' => "azithromycine PO",
                'posologie_alternative' => "Enfant/Adulte : 10 mg/kg/jour à J1 puis 5 mg/kg/jour J2-J5",
                'remarques' => "Isolement respiratoire les 5 premiers jours de traitement.",
            ],
            "Bronchite aiguë" => [
                'titre' => "Prise en charge de la bronchite aiguë",
                'signes' => "Toux, parfois fièvre modérée, absence de signe de gravité respiratoire.",
                'diagnostics' => "Clinique ; éliminer une pneumonie.",
                'traitement_principal' => "Traitement symptomatique, pas d'antibiotique systématique (origine virale).",
                'posologie_principale' => "paracétamol si fièvre",
            ],
            "Bronchiolite" => [
                'titre' => "Prise en charge de la bronchiolite",
                'signes' => "Toux, dyspnée sifflante, distension thoracique chez le nourrisson.",
                'diagnostics' => "Clinique ; évaluer les signes de gravité (SpO2, alimentation).",
                'traitement_principal' => "Désobstruction nasale, oxygénothérapie si besoin, fractionnement des repas ; pas d'antibiotique ni de bronchodilatateur systématique.",
                'posologie_principale' => "Oxygène si SpO2 < 90-92%",
            ],
            "Pneumonie chez l'enfant de moins de 5 ans" => [
                'titre' => "Prise en charge de la pneumonie de l'enfant < 5 ans",
                'signes' => "Toux, polypnée, tirage, parfois fièvre ; signes de danger : cyanose, impossibilité de boire.",
                'diagnostics' => "Clinique selon la fréquence respiratoire par âge et signes de gravité (OMS).",
                'traitement_principal' => "Antibiothérapie orale en ambulatoire si non sévère ; hospitalisation et antibiothérapie IV si signes de gravité.",
                'posologie_principale' => "amoxicilline PO",
                'posologie_alternative' => "80-90 mg/kg/jour en 2 prises pendant 5 jours",
                'traitement_alternatif' => "ceftriaxone IV/IM + oxygène si forme sévère",
            ],
            "Pneumonie chez l'enfant de plus de 5 ans et l'adulte" => [
                'titre' => "Prise en charge de la pneumonie chez le grand enfant et l'adulte",
                'signes' => "Toux, fièvre, douleur thoracique, dyspnée, râles crépitants en foyer.",
                'diagnostics' => "Clinique +/- radiographie thoracique.",
                'traitement_principal' => "Antibiothérapie probabiliste ciblant le pneumocoque.",
                'posologie_principale' => "amoxicilline PO",
                'posologie_alternative' => "Adulte : 1 g 3 fois/jour pendant 7 jours",
            ],
            "Asthme aigu (crise d'asthme)" => [
                'titre' => "Prise en charge de la crise d'asthme",
                'signes' => "Dyspnée sifflante, tirage, difficulté à parler dans les formes sévères.",
                'diagnostics' => "Clinique ; évaluer la sévérité (SpO2, débit expiratoire de pointe si disponible).",
                'traitement_principal' => "Bronchodilatateur en urgence + corticoïde si crise modérée à sévère.",
                'posologie_principale' => "salbutamol inhalé (chambre d'inhalation ou nébulisation), répété selon la réponse",
                'traitement_alternatif' => "prednisolone PO",
                'posologie_alternative' => "1-2 mg/kg/jour pendant 3 à 5 jours",
            ],
            "Asthme chronique" => [
                'titre' => "Prise en charge de l'asthme chronique",
                'signes' => "Épisodes récidivants de dyspnée sifflante, toux nocturne.",
                'diagnostics' => "Clinique, antécédents.",
                'traitement_principal' => "Traitement de fond par corticoïde inhalé si disponible + bronchodilatateur de secours.",
                'posologie_principale' => "salbutamol inhalé à la demande",
                'remarques' => "Éducation du patient à la technique d'inhalation et à l'éviction des facteurs déclenchants.",
            ],
            "Tuberculose pulmonaire" => [
                'titre' => "Prise en charge de la tuberculose pulmonaire",
                'signes' => "Toux chronique > 2 semaines, sueurs nocturnes, amaigrissement, hémoptysies parfois.",
                'diagnostics' => "Recherche de bacilles (crachats, GeneXpert), radiographie thoracique.",
                'traitement_principal' => "Quadrithérapie antituberculeuse standard (phase intensive puis phase d'entretien).",
                'posologie_principale' => "isoniazide + rifampicine + pyrazinamide + éthambutol PO pendant 2 mois",
                'traitement_alternatif' => "isoniazide + rifampicine PO",
                'posologie_alternative' => "Phase d'entretien : 4 mois",
                'remarques' => "Traitement supervisé, suivi de l'observance essentiel.",
            ],

            "Diarrhée aiguë" => [
                'titre' => "Prise en charge de la diarrhée aiguë",
                'signes' => "≥ 3 selles liquides/jour, signes de déshydratation associés.",
                'diagnostics' => "Rechercher sang dans les selles, signes de déshydratation, fièvre.",
                'traitement_principal' => "Réhydratation orale (ou IV si sévère) + supplémentation en zinc chez l'enfant < 5 ans ; pas d'antibiotique systématique.",
                'posologie_principale' => "SRO selon plan de réhydratation OMS",
                'traitement_alternatif' => "sulfate de zinc PO",
                'posologie_alternative' => "10-20 mg/jour pendant 10 jours selon l'âge",
                'remarques' => "Pas d'anti-diarrhéique ni d'anti-émétique.",
            ],
            "Shigellose" => [
                'titre' => "Prise en charge de la shigellose",
                'signes' => "Diarrhée sanglante, fièvre, douleurs abdominales, ténesme.",
                'diagnostics' => "Clinique ; diarrhée sanglante la plus fréquente d'origine bactérienne.",
                'traitement_principal' => "Antibiothérapie de première intention + réhydratation.",
                'posologie_principale' => "ciprofloxacine PO",
                'posologie_alternative' => "Adulte : 500 mg 2 fois/jour pendant 3 jours",
            ],
            "Amibiase" => [
                'titre' => "Prise en charge de l'amibiase intestinale",
                'signes' => "Diarrhée glairo-sanglante, douleurs abdominales, sans fièvre franche typiquement.",
                'diagnostics' => "Mise en évidence d'amibes hématophages mobiles aux selles, ou échec d'un traitement de shigellose bien conduit.",
                'traitement_principal' => "Traitement antiparasitaire uniquement en cas de confirmation ou d'échec du traitement de shigellose.",
                'posologie_principale' => "métronidazole PO",
                'posologie_alternative' => "Adulte : 500-750 mg 3 fois/jour pendant 7-10 jours",
            ],
            "Candidose orale ou oropharyngée" => [
                'titre' => "Prise en charge de la candidose orale",
                'signes' => "Dépôts blanchâtres sur la muqueuse buccale, gêne à l'alimentation.",
                'diagnostics' => "Clinique.",
                'traitement_principal' => "Antifongique topique en première intention.",
                'posologie_principale' => "nystatine suspension PO",
                'posologie_alternative' => "100 000 UI 4 fois/jour pendant 7 jours",
                'traitement_alternatif' => "fluconazole PO en cas d'échec ou forme étendue",
            ],

            "Gale" => [
                'titre' => "Prise en charge de la gale",
                'signes' => "Prurit à recrudescence nocturne, sillons scabieux (mains, poignets), nodules scabieux.",
                'diagnostics' => "Clinique ; distinguer forme commune et forme hyperkératosique.",
                'traitement_principal' => "Scabicide topique appliqué sur tout le corps ; traitement simultané de l'entourage.",
                'posologie_principale' => "perméthrine crème 5%, application unique laissée 8-12h, à renouveler à J7-14",
                'traitement_alternatif' => "benzoate de benzyle lotion",
                'remarques' => "Décontamination du linge (lavage ≥ 60°C ou exposition 72h).",
            ],
            "Poux (pédiculoses)" => [
                'titre' => "Prise en charge des pédiculoses",
                'signes' => "Prurit du cuir chevelu ou du corps, lentes visibles.",
                'diagnostics' => "Clinique.",
                'traitement_principal' => "Traitement topique pédiculicide, répété à J7-10.",
                'posologie_principale' => "perméthrine lotion 1%",
                'remarques' => "Traitement simultané de l'entourage proche.",
            ],
            "Impétigo" => [
                'titre' => "Prise en charge de l'impétigo",
                'signes' => "Lésions croûteuses jaunâtres (« mélicériques »), souvent péri-orificielles.",
                'diagnostics' => "Clinique.",
                'traitement_principal' => "Antibiothérapie topique si lésions limitées, orale si étendues.",
                'posologie_principale' => "amoxicilline/acide clavulanique PO si forme étendue",
                'posologie_alternative' => "pendant 7 jours",
                'remarques' => "Hygiène locale (lavage à l'eau et au savon).",
            ],
            "Furoncle et anthrax staphylococcique" => [
                'titre' => "Prise en charge du furoncle / anthrax staphylococcique",
                'signes' => "Nodule inflammatoire douloureux centré sur un follicule pileux, évoluant vers la collection.",
                'diagnostics' => "Clinique.",
                'traitement_principal' => "Drainage chirurgical si collecté + antibiothérapie si signes généraux ou localisation à risque.",
                'posologie_principale' => "cloxacilline PO ou IV selon sévérité",
                'posologie_alternative' => "pendant 7 jours",
            ],
            "Erysipèle et cellulite" => [
                'titre' => "Prise en charge de l'érysipèle et de la cellulite",
                'signes' => "Placard cutané inflammatoire, chaud, douloureux, à limites nettes (érysipèle) ou diffuses (cellulite), fièvre.",
                'diagnostics' => "Clinique.",
                'traitement_principal' => "Antibiothérapie ciblant streptocoque et staphylocoque.",
                'posologie_principale' => "amoxicilline/acide clavulanique PO ou cloxacilline IV si sévère",
                'posologie_alternative' => "pendant 7 à 10 jours",
            ],
            "Lèpre" => [
                'titre' => "Prise en charge de la lèpre",
                'signes' => "Macules hypopigmentées anesthésiques, nodules, épaississement des nerfs périphériques.",
                'diagnostics' => "Clinique, frottis cutané si disponible.",
                'traitement_principal' => "Polychimiothérapie standardisée OMS selon la forme (pauci- ou multibacillaire).",
                'posologie_principale' => "rifampicine + dapsone (+ clofazimine si multibacillaire) PO",
                'posologie_alternative' => "6 à 12 mois selon la forme",
            ],
            "Zona" => [
                'titre' => "Prise en charge du zona",
                'signes' => "Éruption vésiculeuse unilatérale suivant un trajet nerveux, douleur souvent intense.",
                'diagnostics' => "Clinique.",
                'traitement_principal' => "Antiviral si prescrit précocement + antalgique.",
                'posologie_principale' => "aciclovir PO",
                'posologie_alternative' => "800 mg 5 fois/jour pendant 7 jours",
            ],
            "Eczéma" => [
                'titre' => "Prise en charge de l'eczéma",
                'signes' => "Prurit, lésions érythémateuses vésiculeuses puis squameuses.",
                'diagnostics' => "Clinique.",
                'traitement_principal' => "Émollients + corticoïde topique sur les poussées.",
                'posologie_principale' => "hydrocortisone crème (ou dermocorticoïde disponible) 1 à 2 fois/jour",
            ],
            "Urticaire" => [
                'titre' => "Prise en charge de l'urticaire",
                'signes' => "Papules ou plaques érythémateuses prurigineuses fugaces.",
                'diagnostics' => "Clinique ; rechercher un facteur déclenchant.",
                'traitement_principal' => "Antihistaminique ; corticoïde en cas de forme sévère ou d'œdème de Quincke.",
                'posologie_principale' => "hydroxyzine PO 25 mg 2 fois/jour",
                'traitement_alternatif' => "prednisolone PO si forme sévère",
            ],

            "Xérophtalmie (carence en vitamine A)" => [
                'titre' => "Prise en charge de la xérophtalmie",
                'signes' => "Héméralopie, xérose conjonctivale, taches de Bitot, jusqu'à kératomalacie au stade terminal.",
                'diagnostics' => "Clinique.",
                'traitement_principal' => "Supplémentation en vitamine A à tous les stades ; protection oculaire et antibiotique local si atteinte cornéenne.",
                'posologie_principale' => "rétinol PO 200 000 UI (adapté à l'âge) à J1, J2 et J8",
                'traitement_alternatif' => "tétracycline ophtalmique 1% si atteinte cornéenne",
                'remarques' => "Dose réduite chez la femme enceinte sans atteinte cornéenne (risque tératogène).",
            ],
            "Conjonctivite du nouveau-né" => [
                'titre' => "Prise en charge de la conjonctivite du nouveau-né",
                'signes' => "Sécrétions purulentes oculaires dans les premiers jours de vie, œdème palpébral.",
                'diagnostics' => "Clinique ; suspecter gonocoque ou chlamydia.",
                'traitement_principal' => "Antibiothérapie locale et générale selon le germe suspecté, en urgence.",
                'posologie_principale' => "ceftriaxone IM dose unique + tétracycline ophtalmique",
            ],
            "Trachome" => [
                'titre' => "Prise en charge du trachome",
                'signes' => "Kérato-conjonctivite chronique, follicules conjonctivaux, à terme cicatrices et entropion.",
                'diagnostics' => "Clinique, classification OMS des stades.",
                'traitement_principal' => "Antibiothérapie de masse dans les zones endémiques + hygiène du visage.",
                'posologie_principale' => "azithromycine PO dose unique",
                'posologie_alternative' => "20 mg/kg (max 1 g)",
            ],
            "Cellulite périorbitaire et orbitaire" => [
                'titre' => "Prise en charge de la cellulite périorbitaire/orbitaire",
                'signes' => "Œdème et rougeur palpébrale, fièvre ; signes de gravité si atteinte orbitaire (exophtalmie, limitation oculomotrice).",
                'diagnostics' => "Clinique ; distinguer forme périorbitaire (préseptale) de la forme orbitaire (urgence).",
                'traitement_principal' => "Antibiothérapie orale si forme légère, IV en hospitalisation si forme orbitaire.",
                'posologie_principale' => "amoxicilline/acide clavulanique PO ou ceftriaxone IV si sévère",
            ],

            "Paludisme" => [
                'titre' => "Prise en charge du paludisme",
                'signes' => "Fièvre, frissons, céphalées, myalgies ; signes de gravité (coma, convulsions, détresse respiratoire, choc) définissant le paludisme sévère.",
                'diagnostics' => "Test de diagnostic rapide et/ou frottis-goutte épaisse ; ne pas retarder le traitement en l'absence de test.",
                'traitement_principal' => "Combinaison thérapeutique à base d'artémisinine (ACT) PO pendant 3 jours pour les formes non compliquées à P. falciparum.",
                'posologie_principale' => "artéméther/luméfantrine PO selon le poids, 2 prises/jour pendant 3 jours",
                'traitement_alternatif' => "artésunate IV pour le paludisme sévère",
                'posologie_alternative' => "2,4 mg/kg à H0, H12, H24 puis 1 fois/jour",
                'remarques' => "Chloroquine réservée à P. vivax/ovale/malariae/knowlesi en l'absence de résistance.",
            ],
            "Leishmanioses" => [
                'titre' => "Prise en charge des leishmanioses",
                'signes' => "Forme cutanée : ulcération chronique indolore. Forme viscérale : fièvre prolongée, splénomégalie, amaigrissement, pancytopénie.",
                'diagnostics' => "Frottis/biopsie de la lésion (forme cutanée), ponction splénique ou sérologie (forme viscérale).",
                'traitement_principal' => "Traitement spécifique selon la forme et la zone géographique, en milieu spécialisé.",
                'posologie_principale' => "Antimoniés pentavalents ou amphotéricine B liposomale selon protocole national",
                'remarques' => "Se référer aux protocoles nationaux/OMS spécifiques à la zone d'endémie.",
            ],
            "Schistosomiases" => [
                'titre' => "Prise en charge des schistosomiases",
                'signes' => "Forme urinaire : hématurie terminale. Forme intestinale/hépatique : douleurs abdominales, hépatosplénomégalie.",
                'diagnostics' => "Recherche d'œufs dans les urines ou les selles.",
                'traitement_principal' => "Traitement antiparasitaire en dose unique.",
                'posologie_principale' => "praziquantel PO 40 mg/kg dose unique",
            ],
            "Nématodoses" => [
                'titre' => "Prise en charge des nématodoses intestinales",
                'signes' => "Souvent asymptomatiques ; douleurs abdominales, retard de croissance, parfois prurit anal (oxyures).",
                'diagnostics' => "Examen parasitologique des selles.",
                'traitement_principal' => "Traitement antiparasitaire en dose unique ou courte.",
                'posologie_principale' => "albendazole PO 400 mg dose unique",
                'traitement_alternatif' => "mébendazole PO 100 mg 2 fois/jour pendant 3 jours",
            ],
            "Filarioses lymphatiques (FL)" => [
                'titre' => "Prise en charge des filarioses lymphatiques",
                'signes' => "Lymphœdème, hydrocèle, épisodes de lymphangite aiguë fébrile.",
                'diagnostics' => "Recherche de microfilaires (goutte épaisse nocturne), sérologie antigénique.",
                'traitement_principal' => "Traitement antiparasitaire + soins locaux du lymphœdème (hygiène, drainage).",
                'posologie_principale' => "ivermectine + albendazole PO dose unique (traitement de masse en zone endémique)",
            ],

            "Méningite bactérienne" => [
                'titre' => "Prise en charge de la méningite bactérienne",
                'signes' => "Fièvre, céphalées intenses, raideur de la nuque, photophobie, purpura possible ; chez le nourrisson : irritabilité, fontanelle bombée.",
                'diagnostics' => "Ponction lombaire avec analyse macroscopique, cytologique et bactériologique du LCR.",
                'traitement_principal' => "Antibiothérapie parentérale probabiliste en urgence, débutée dès la suspicion diagnostique.",
                'posologie_principale' => "ceftriaxone IV/IM",
                'posologie_alternative' => "Enfant : 100 mg/kg/jour ; Adulte : 2 g 2 fois/jour pendant 7 à 10 jours",
                'remarques' => "Ne pas retarder l'antibiothérapie en attendant les résultats de la ponction lombaire.",
            ],
            "Tétanos" => [
                'titre' => "Prise en charge du tétanos",
                'signes' => "Trismus, contractures musculaires généralisées, spasmes déclenchés par les stimuli, sans altération de la conscience.",
                'diagnostics' => "Clinique.",
                'traitement_principal' => "Sérothérapie/immunoglobulines antitétaniques + antibiothérapie + sédation et contrôle des spasmes en soins intensifs.",
                'posologie_principale' => "métronidazole IV/PO + diazépam pour contrôler les spasmes",
                'remarques' => "Vaccination complète après guérison (le tétanos ne confère pas d'immunité naturelle).",
            ],
            "Fièvres entériques (typhoïde et paratyphoïde)" => [
                'titre' => "Prise en charge des fièvres entériques",
                'signes' => "Fièvre progressive, douleurs abdominales, troubles digestifs, splénomégalie possible.",
                'diagnostics' => "Hémoculture si disponible.",
                'traitement_principal' => "Antibiothérapie adaptée à la sensibilité locale.",
                'posologie_principale' => "ceftriaxone IV/IM ou azithromycine PO selon disponibilité et résistances locales",
                'posologie_alternative' => "pendant 7 à 14 jours",
            ],
            "Brucellose" => [
                'titre' => "Prise en charge de la brucellose",
                'signes' => "Fièvre ondulante, sueurs nocturnes, douleurs articulaires, asthénie.",
                'diagnostics' => "Sérologie, contexte d'exposition (élevage, produits laitiers non pasteurisés).",
                'traitement_principal' => "Bithérapie antibiotique prolongée.",
                'posologie_principale' => "doxycycline PO + gentamicine ou rifampicine",
                'posologie_alternative' => "6 semaines pour la doxycycline",
            ],

            "Rougeole" => [
                'titre' => "Prise en charge de la rougeole",
                'signes' => "Fièvre élevée, toux, écoulement nasal, conjonctivite, puis éruption maculopapuleuse descendante.",
                'diagnostics' => "Clinique.",
                'traitement_principal' => "Traitement symptomatique + supplémentation en vitamine A + prise en charge des complications ; antibioprophylaxie chez l'enfant < 5 ans.",
                'posologie_principale' => "rétinol PO à J1 et J2 + paracétamol si fièvre",
                'traitement_alternatif' => "amoxicilline PO 5 jours à titre préventif chez l'enfant < 5 ans",
                'remarques' => "Isolement du patient ; vaccination des sujets contacts non immunisés.",
            ],
            "Hépatites virales" => [
                'titre' => "Prise en charge des hépatites virales",
                'signes' => "Ictère, asthénie, douleurs abdominales, urines foncées ; formes chroniques souvent asymptomatiques longtemps.",
                'diagnostics' => "Sérologies virales spécifiques (A, B, C, D, E), bilan hépatique.",
                'traitement_principal' => "Traitement symptomatique pour les hépatites aiguës virales A/E ; traitement antiviral spécifique pour les formes chroniques B/C selon protocole national.",
                'remarques' => "Prévention par la vaccination (hépatite A et B) et l'hygiène de l'eau (hépatite A et E).",
            ],
            "Dengue" => [
                'titre' => "Prise en charge de la dengue",
                'signes' => "Fièvre élevée, céphalées, douleurs rétro-orbitaires, myalgies, éruption ; signes d'alarme évoquant une forme sévère.",
                'diagnostics' => "Clinique, contexte épidémique, test antigénique NS1 si disponible.",
                'traitement_principal' => "Traitement symptomatique et surveillance des signes d'alarme (pas d'AINS ni d'aspirine, risque hémorragique).",
                'posologie_principale' => "paracétamol contre la fièvre/douleur",
                'remarques' => "Hospitalisation en urgence en cas de signes d'alarme (douleur abdominale, saignement, hypotension).",
            ],
            "Infection par le HIV et sida" => [
                'titre' => "Prise en charge de l'infection par le VIH",
                'signes' => "Souvent asymptomatique au stade initial ; amaigrissement, fièvre prolongée, infections opportunistes aux stades avancés.",
                'diagnostics' => "Test de dépistage sérologique/rapide, confirmation selon algorithme national.",
                'traitement_principal' => "Traitement antirétroviral (ARV) à vie, initié dès le diagnostic, associé à la prophylaxie des infections opportunistes selon le stade.",
                'posologie_principale' => "Trithérapie ARV selon protocole national (ex. ténofovir + lamivudine + dolutégravir)",
                'remarques' => "Suivi biologique régulier (charge virale, CD4) et soutien à l'observance.",
            ],

            "Cystite aiguë" => [
                'titre' => "Prise en charge de la cystite aiguë",
                'signes' => "Brûlures mictionnelles, pollakiurie, sans fièvre ni douleur lombaire.",
                'diagnostics' => "Bandelette urinaire ; clinique suffisante en l'absence de signe de gravité.",
                'traitement_principal' => "Antibiothérapie courte.",
                'posologie_principale' => "ciprofloxacine PO",
                'posologie_alternative' => "500 mg 2 fois/jour pendant 3 jours (femme) ou 7 jours (homme)",
            ],
            "Pyélonéphrite aiguë" => [
                'titre' => "Prise en charge de la pyélonéphrite aiguë",
                'signes' => "Fièvre, douleur lombaire, signes de cystite associés.",
                'diagnostics' => "Bandelette urinaire, ECBU si disponible.",
                'traitement_principal' => "Antibiothérapie, voie parentérale si signes de gravité.",
                'posologie_principale' => "ceftriaxone IV/IM puis relais PO",
                'posologie_alternative' => "pendant 10 à 14 jours",
            ],
            "Syndrome néphrotique chez l'enfant" => [
                'titre' => "Prise en charge du syndrome néphrotique de l'enfant",
                'signes' => "Œdèmes déclives ou périorbitaires, protéinurie massive à la bandelette.",
                'diagnostics' => "Bandelette urinaire (protéinurie ≥ +++), recherche d'une cause secondaire.",
                'traitement_principal' => "Corticothérapie prolongée après exclusion/traitement des infections aiguës associées.",
                'posologie_principale' => "prednisolone PO 60 mg/m²/jour (phase d'attaque), selon algorithme, 2 à 4 mois au total",
                'remarques' => "Régime sans sel ajouté ; surveillance de la protéinurie et du poids.",
            ],

            "Brûlures" => [
                'titre' => "Prise en charge des brûlures",
                'signes' => "Lésions cutanées classées selon la profondeur (1er, 2e, 3e degré) et l'étendue (% surface corporelle).",
                'diagnostics' => "Évaluation clinique de la profondeur et de la surface brûlée.",
                'traitement_principal' => "Refroidissement immédiat, analgésie, pansements adaptés, réanimation liquidienne si brûlure étendue.",
                'posologie_principale' => "Antalgique adapté à l'intensité de la douleur (paracétamol +/- opioïde)",
                'remarques' => "Prévention systématique du tétanos (vérifier le statut vaccinal).",
            ],
            "Abcès cutané" => [
                'titre' => "Prise en charge de l'abcès cutané",
                'signes' => "Tuméfaction douloureuse, fluctuante, inflammatoire, parfois fièvre.",
                'diagnostics' => "Clinique.",
                'traitement_principal' => "Incision-drainage chirurgical, antibiotique si signes généraux ou terrain à risque.",
                'posologie_principale' => "cloxacilline PO si antibiothérapie nécessaire",
                'posologie_alternative' => "pendant 5 à 7 jours",
            ],
            "Morsures et piqûres venimeuses" => [
                'titre' => "Prise en charge des morsures et piqûres venimeuses",
                'signes' => "Douleur locale, œdème, signes systémiques selon l'espèce (troubles de la coagulation, neurotoxicité).",
                'diagnostics' => "Clinique ; identification de l'animal si possible.",
                'traitement_principal' => "Immobilisation du membre, analgésie, sérum antivenimeux spécifique si disponible et indiqué.",
                'posologie_principale' => "Antalgique + surveillance rapprochée ; sérum antivenimeux selon protocole spécifique",
                'remarques' => "Ne pas inciser ni aspirer la plaie ; vérifier la vaccination antitétanique.",
            ],

            "Anxiété" => [
                'titre' => "Prise en charge de l'anxiété",
                'signes' => "Inquiétude envahissante, nervosité, plaintes somatiques, troubles du sommeil.",
                'diagnostics' => "Clinique ; rechercher un trouble dépressif ou post-traumatique sous-jacent.",
                'traitement_principal' => "Prise en charge psychosociale en première intention ; traitement médicamenteux court si besoin.",
                'posologie_principale' => "hydroxyzine PO 25-50 mg 2 fois/jour",
                'traitement_alternatif' => "diazépam PO 2,5-5 mg 2 fois/jour, maximum 2-3 semaines",
                'remarques' => "Antidépresseur (fluoxétine) si trouble anxieux généralisé > 2 mois résistant aux mesures psychosociales.",
            ],
            "Insomnie" => [
                'titre' => "Prise en charge de l'insomnie",
                'signes' => "Difficulté d'endormissement, réveils nocturnes, sommeil non réparateur depuis au moins un mois.",
                'diagnostics' => "Clinique ; rechercher une cause organique, toxique ou un trouble psychique sous-jacent.",
                'traitement_principal' => "Traitement de la cause sous-jacente ; sédatif court en cas d'événement de vie ponctuel.",
                'posologie_principale' => "prométhazine PO 25 mg au coucher pendant 7 à 10 jours",
                'traitement_alternatif' => "diazépam PO en dernier recours, max 7 jours",
            ],
            "Dépression" => [
                'titre' => "Prise en charge de la dépression",
                'signes' => "Tristesse persistante, perte d'intérêt, troubles du sommeil et de l'appétit, ralentissement.",
                'diagnostics' => "Clinique ; évaluer systématiquement le risque suicidaire.",
                'traitement_principal' => "Soutien psychosocial en première intention ; antidépresseur si dépression modérée à sévère.",
                'posologie_principale' => "fluoxétine PO 20 mg une fois/jour",
                'remarques' => "Effet thérapeutique différé de plusieurs semaines ; surveillance rapprochée en début de traitement.",
            ],
            "Épisode psychotique aigu" => [
                'titre' => "Prise en charge de l'épisode psychotique aigu",
                'signes' => "Délire, hallucinations, agitation, désorganisation du comportement.",
                'diagnostics' => "Clinique ; éliminer une cause organique ou toxique aiguë.",
                'traitement_principal' => "Antipsychotique + sédation si agitation importante, en milieu sécurisé.",
                'posologie_principale' => "halopéridol IM",
                'traitement_alternatif' => "diazépam en association si agitation majeure",
            ],

            "Drépanocytose" => [
                'titre' => "Prise en charge de la drépanocytose",
                'signes' => "Crises vaso-occlusives douloureuses, anémie chronique, splénomégalie, complications aiguës graves (AVC, syndrome thoracique aigu, séquestration splénique).",
                'diagnostics' => "Test de falciformation (test d'Emmel) ou électrophorèse de l'hémoglobine.",
                'traitement_principal' => "Antalgiques adaptés à l'intensité de la crise vaso-occlusive, hydratation, traitement de toute infection déclenchante ; prise en charge spécialisée des complications aiguës.",
                'posologie_principale' => "paracétamol +/- opioïde selon l'intensité de la douleur",
                'remarques' => "Prophylaxie par acide folique et surveillance annuelle de l'hémoglobine recommandées.",
            ],
            "Épilepsie" => [
                'titre' => "Prise en charge de l'épilepsie",
                'signes' => "Crises convulsives récurrentes, avec ou sans perte de connaissance.",
                'diagnostics' => "Clinique ; description précise des crises par l'entourage.",
                'traitement_principal' => "Traitement antiépileptique de fond au long cours.",
                'posologie_principale' => "phénobarbital PO",
                'posologie_alternative' => "Enfant : 3-5 mg/kg/jour ; Adulte : 2-3 mg/kg/jour en 1 prise",
                'remarques' => "Ne jamais interrompre brutalement le traitement (risque d'état de mal).",
            ],
            "Diabète de type 2 chez l'adulte" => [
                'titre' => "Prise en charge du diabète de type 2",
                'signes' => "Polyurie, polydipsie, amaigrissement, parfois asymptomatique (découverte fortuite).",
                'diagnostics' => "Glycémie à jeun ou aléatoire selon critères diagnostiques standards.",
                'traitement_principal' => "Mesures hygiéno-diététiques + traitement médicamenteux si besoin.",
                'posologie_principale' => "metformine PO",
                'posologie_alternative' => "500 mg 2 à 3 fois/jour, à augmenter progressivement",
                'traitement_alternatif' => "insuline si échec ou contre-indication aux antidiabétiques oraux",
            ],
            "Hypertension artérielle essentielle de l'adulte (HTA)" => [
                'titre' => "Prise en charge de l'hypertension artérielle essentielle",
                'signes' => "Élévation chronique de la tension artérielle, souvent asymptomatique, parfois céphalées.",
                'diagnostics' => "Mesures répétées de la tension artérielle.",
                'traitement_principal' => "Mesures hygiéno-diététiques + traitement antihypertenseur selon le niveau tensionnel.",
                'posologie_principale' => "énalapril PO",
                'posologie_alternative' => "5-20 mg une fois/jour",
            ],
            "Insuffisance cardiaque chronique" => [
                'titre' => "Prise en charge de l'insuffisance cardiaque chronique",
                'signes' => "Dyspnée d'effort puis de repos, œdèmes des membres inférieurs, fatigue.",
                'diagnostics' => "Clinique ; recherche de la cause sous-jacente.",
                'traitement_principal' => "Diurétique + IEC en traitement de fond, régime pauvre en sel.",
                'posologie_principale' => "furosémide PO + énalapril PO",
                'posologie_alternative' => "Doses à adapter selon la réponse clinique",
            ],
        ];

        // 1. Charger et parser public/index.html pour trouver les protocoles
        $htmlPath = base_path('public/index.html');
        if (file_exists($htmlPath)) {
            $html = file_get_contents($htmlPath);
            if (preg_match('/const DATA = \[(.*?)\];/s', $html, $matches)) {
                $dataStr = trim($matches[1]);
                $lines = explode("\n", $dataStr);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    
                    $nom = '';
                    $category = '';
                    $diag = '';
                    $tx = '';
                    if (preg_match('/t:"([^"]*)"/', $line, $mTitle)) $nom = $mTitle[1];
                    if (preg_match('/c:"([^"]*)"/', $line, $mCat)) $category = $mCat[1];
                    if (preg_match('/d:"([^"]*)"/', $line, $mDiag)) $diag = $mDiag[1];
                    if (preg_match('/tx:"([^"]*)"/', $line, $mTx)) $tx = $mTx[1];
                    
                    if (!empty($nom)) {
                        $exists = false;
                        foreach ($protocoles as $maladieNom => $p) {
                            if (strcasecmp($maladieNom, $nom) === 0) {
                                $exists = true;
                                break;
                            }
                        }
                        if (!$exists) {
                            $protocoles[$nom] = [
                                'titre' => "Protocole : " . $nom,
                                'signes' => "Selon présentation clinique.",
                                'diagnostics' => $diag,
                                'traitement_principal' => $tx,
                                'posologie_principale' => "Voir détails",
                                'remarques' => "Catégorie : " . $category
                            ];
                        }
                    }
                }
            }
        }

        foreach ($protocoles as $maladieNom => $p) {
            $maladie = Maladie::where('nom', $maladieNom)->first();
            if (!$maladie) {
                continue;
            }

            ProtocoleTraitement::firstOrCreate(
                ['maladie_id' => $maladie->id],
                array_merge(['uuid' => (string) Str::uuid()], [
                    'titre' => $p['titre'] ?? $maladieNom,
                    'signes' => $p['signes'] ?? null,
                    'diagnostics' => $p['diagnostics'] ?? null,
                    'germes_nourrisson' => $p['germes_nourrisson'] ?? null,
                    'germes_adulte' => $p['germes_adulte'] ?? null,
                    'traitement_principal' => $p['traitement_principal'] ?? null,
                    'posologie_principale' => $p['posologie_principale'] ?? null,
                    'traitement_alternatif' => $p['traitement_alternatif'] ?? null,
                    'posologie_alternative' => $p['posologie_alternative'] ?? null,
                    'remarques' => $p['remarques'] ?? null,
                ])
            );
        }
    }
}
