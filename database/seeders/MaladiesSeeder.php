<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Maladie;

/**
 * Source : Médecins Sans Frontières, Guide clinique et thérapeutique, Décembre 2024
 * (Chapitres 2 à 12). Descriptions synthétisées à usage de fiche clinique interne,
 * reformulées et non reproduites du texte original.
 */
class MaladiesSeeder extends Seeder
{
    public function run(): void
    {
        $maladies = [
            // ===== Chapitre 2 : Pathologie respiratoire =====
            ['nom' => "Obstruction aiguë des voies aériennes supérieures", 'description' => "Obstruction pouvant être due à l'inhalation d'un corps étranger, une infection (croup, épiglottite, trachéite), une réaction anaphylactique, une brûlure ou un traumatisme ; peut engager le pronostic vital."],
            ['nom' => "Rhinite (rhume) et rhinopharyngite", 'description' => "Inflammation bénigne, le plus souvent virale, de la muqueuse nasale et pharyngée, guérissant spontanément."],
            ['nom' => "Sinusite aiguë", 'description' => "Inflammation aiguë d'un ou plusieurs sinus, souvent secondaire à une rhinopharyngite."],
            ['nom' => "Angine (pharyngite) aiguë", 'description' => "Inflammation aiguë du pharynx et/ou des amygdales, d'origine virale ou bactérienne (streptococcique)."],
            ['nom' => "Diphtérie", 'description' => "Infection bactérienne (Corynebacterium diphtheriae) à transmission respiratoire, pouvant provoquer une obstruction laryngée et une toxine cardiaque et neurologique."],
            ['nom' => "Laryngotrachéite et laryngotrachéobronchite (croup)", 'description' => "Infection virale des voies aériennes hautes provoquant toux rauque, stridor et détresse respiratoire, principalement chez le jeune enfant."],
            ['nom' => "Épiglottite", 'description' => "Infection bactérienne aiguë de l'épiglotte, urgence vitale par risque d'obstruction complète des voies aériennes."],
            ['nom' => "Trachéite bactérienne", 'description' => "Surinfection bactérienne de la trachée avec sécrétions purulentes et détresse respiratoire sévère."],
            ['nom' => "Otite externe aiguë", 'description' => "Infection du conduit auditif externe, le plus souvent bactérienne ou fongique."],
            ['nom' => "Otite moyenne aiguë (OMA)", 'description' => "Infection aiguë de l'oreille moyenne, fréquente chez le jeune enfant, souvent secondaire à une rhinopharyngite."],
            ['nom' => "Otite moyenne chronique suppurée (OMCS)", 'description' => "Otorrhée purulente chronique par perforation tympanique persistante, avec risque de complications auditives."],
            ['nom' => "Coqueluche", 'description' => "Infection respiratoire bactérienne (Bordetella pertussis) très contagieuse, caractérisée par des quintes de toux prolongées."],
            ['nom' => "Bronchite aiguë", 'description' => "Inflammation aiguë des bronches, le plus souvent d'origine virale."],
            ['nom' => "Bronchite chronique", 'description' => "Toux productive chronique évoluant sur plusieurs mois, associée notamment au tabagisme ou à l'exposition à des fumées."],
            ['nom' => "Bronchiolite", 'description' => "Infection virale des voies aériennes basses du nourrisson, provoquant une distension thoracique et des sibilants."],
            ['nom' => "Pneumonie chez l'enfant de moins de 5 ans", 'description' => "Infection aiguë du parenchyme pulmonaire, cause majeure de mortalité infantile, principalement bactérienne ou virale."],
            ['nom' => "Pneumonie chez l'enfant de plus de 5 ans et l'adulte", 'description' => "Infection aiguë du parenchyme pulmonaire chez le grand enfant et l'adulte, le plus souvent à pneumocoque."],
            ['nom' => "Pneumonie traînante", 'description' => "Pneumonie dont l'évolution clinique ou radiologique reste anormale au-delà du délai habituel de guérison."],
            ['nom' => "Staphylococcie pleuro-pulmonaire", 'description' => "Infection pulmonaire sévère à Staphylococcus aureus, surtout chez le nourrisson, avec risque de pneumatocèles et pyopneumothorax."],
            ['nom' => "Asthme aigu (crise d'asthme)", 'description' => "Épisode aigu de bronchospasme réversible provoquant dyspnée sifflante, pouvant être sévère."],
            ['nom' => "Asthme chronique", 'description' => "Maladie inflammatoire chronique des voies aériennes avec épisodes récidivants de dyspnée sifflante."],
            ['nom' => "Tuberculose pulmonaire", 'description' => "Infection chronique à Mycobacterium tuberculosis touchant principalement les poumons, transmise par voie aérienne."],

            // ===== Chapitre 3 : Pathologie digestive =====
            ['nom' => "Diarrhée aiguë", 'description' => "Émission d'au moins 3 selles liquides par jour depuis moins de 2 semaines, virale, bactérienne ou parasitaire."],
            ['nom' => "Shigellose", 'description' => "Diarrhée bactérienne invasive à Shigella, cause la plus fréquente de diarrhée sanglante."],
            ['nom' => "Amibiase", 'description' => "Infection parasitaire intestinale (Entamoeba histolytica) pouvant provoquer une diarrhée sanglante ou un abcès hépatique."],
            ['nom' => "Reflux gastro-œsophagien", 'description' => "Remontée du contenu gastrique dans l'œsophage, provoquant pyrosis et régurgitations."],
            ['nom' => "Ulcères gastro-duodénaux chez l'adulte", 'description' => "Perte de substance de la muqueuse gastrique ou duodénale, souvent liée à Helicobacter pylori ou aux AINS."],
            ['nom' => "Troubles dyspeptiques", 'description' => "Douleurs ou inconfort épigastrique chronique sans lésion organique identifiée."],
            ['nom' => "Candidose orale ou oropharyngée", 'description' => "Infection fongique (Candida albicans) de la muqueuse buccale, fréquente chez le nourrisson et l'immunodéprimé."],
            ['nom' => "Herpès buccal", 'description' => "Infection virale récurrente (HSV) de la muqueuse buccale et péribuccale."],
            ['nom' => "Stomatite du scorbut (carence en vitamine C)", 'description' => "Atteinte gingivale et muqueuse liée à une carence sévère en vitamine C."],

            // ===== Chapitre 4 : Pathologie dermatologique =====
            ['nom' => "Gale", 'description' => "Parasitose cutanée contagieuse due à Sarcoptes scabiei, provoquant un prurit à recrudescence nocturne."],
            ['nom' => "Poux (pédiculoses)", 'description' => "Parasitose cutanée due à des poux (tête, corps, pubis), transmise par contact direct."],
            ['nom' => "Mycoses superficielles", 'description' => "Infections fongiques de la peau, des phanères ou des muqueuses (dermatophytoses, candidoses)."],
            ['nom' => "Impétigo", 'description' => "Infection cutanée bactérienne superficielle très contagieuse, à streptocoque et/ou staphylocoque."],
            ['nom' => "Furoncle et anthrax staphylococcique", 'description' => "Infection profonde d'un ou plusieurs follicules pileux à Staphylococcus aureus."],
            ['nom' => "Erysipèle et cellulite", 'description' => "Infection bactérienne aiguë du derme (érysipèle) ou du derme et de l'hypoderme (cellulite)."],
            ['nom' => "Charbon cutané", 'description' => "Infection bactérienne (Bacillus anthracis) transmise par contact avec des animaux infectés, provoquant une escarre nécrotique."],
            ['nom' => "Tréponématoses endémiques", 'description' => "Infections bactériennes chroniques non vénériennes du groupe des tréponématoses (pian, béjel)."],
            ['nom' => "Lèpre", 'description' => "Infection chronique à Mycobacterium leprae touchant la peau et les nerfs périphériques."],
            ['nom' => "Herpès cutané", 'description' => "Infection virale récurrente (HSV) de la peau, en dehors de la sphère buccale et génitale."],
            ['nom' => "Zona", 'description' => "Réactivation du virus varicelle-zona le long d'un trajet nerveux, provoquant une éruption vésiculeuse douloureuse."],
            ['nom' => "Eczéma", 'description' => "Dermatose inflammatoire prurigineuse chronique ou récidivante."],
            ['nom' => "Dermatite séborrhéique", 'description' => "Dermatose inflammatoire chronique touchant les zones riches en glandes sébacées."],
            ['nom' => "Urticaire", 'description' => "Éruption cutanée prurigineuse fugace liée à une réaction allergique ou non allergique."],
            ['nom' => "Pellagre", 'description' => "Carence en vitamine B3 (niacine) provoquant dermatite, diarrhée et troubles neuropsychiques."],

            // ===== Chapitre 5 : Pathologie ophtalmologique =====
            ['nom' => "Xérophtalmie (carence en vitamine A)", 'description' => "Ensemble des manifestations oculaires de la carence en vitamine A, pouvant évoluer vers la cécité."],
            ['nom' => "Conjonctivite du nouveau-né", 'description' => "Infection conjonctivale néonatale, souvent liée à une transmission materno-fœtale (gonocoque, chlamydia)."],
            ['nom' => "Kérato-conjonctivite virale épidémique", 'description' => "Infection virale très contagieuse de la conjonctive et de la cornée, à transmission épidémique."],
            ['nom' => "Trachome", 'description' => "Kérato-conjonctivite chronique à Chlamydia trachomatis, première cause infectieuse de cécité dans le monde."],
            ['nom' => "Cellulite périorbitaire et orbitaire", 'description' => "Infection bactérienne des tissus mous autour de l'œil, pouvant menacer la vision en cas d'atteinte orbitaire."],
            ['nom' => "Onchocercose (cécité des rivières)", 'description' => "Filariose cutanéo-oculaire transmise par simulies, pouvant entraîner prurit, lésions cutanées et cécité."],
            ['nom' => "Loase", 'description' => "Filariose transmise par le taon Chrysops, caractérisée par des œdèmes de Calabar et un passage sous-conjonctival du ver."],
            ['nom' => "Ptérygion", 'description' => "Prolifération fibrovasculaire conjonctivale bénigne pouvant recouvrir la cornée."],
            ['nom' => "Cataracte", 'description' => "Opacification du cristallin entraînant une baisse progressive de l'acuité visuelle."],

            // ===== Chapitre 6 : Maladies parasitaires =====
            ['nom' => "Paludisme", 'description' => "Infection parasitaire due à Plasmodium spp., transmise par piqûre d'anophèle, pouvant être non compliquée ou sévère (P. falciparum surtout)."],
            ['nom' => "Trypanosomiase humaine africaine (maladie du sommeil)", 'description' => "Infection parasitaire transmise par la mouche tsé-tsé, évoluant en une phase hémolymphatique puis neurologique."],
            ['nom' => "Trypanosomiase américaine (maladie de Chagas)", 'description' => "Infection parasitaire transmise par les punaises triatomes, avec phase aiguë puis chronique (cardiaque, digestive)."],
            ['nom' => "Leishmanioses", 'description' => "Infections parasitaires transmises par phlébotomes, cutanées, cutanéo-muqueuses ou viscérales."],
            ['nom' => "Protozooses intestinales (diarrhées parasitaires)", 'description' => "Diarrhées d'origine parasitaire (giardiase, cryptosporidiose, etc.)."],
            ['nom' => "Distomatoses (douves)", 'description' => "Infections parasitaires à trématodes affectant le foie, les poumons ou l'intestin."],
            ['nom' => "Schistosomiases", 'description' => "Infections parasitaires (bilharzioses) dues à des vers trématodes, urinaires ou intestinales/hépatiques."],
            ['nom' => "Cestodoses", 'description' => "Infections parasitaires à vers plats (ténias), intestinales ou tissulaires (cysticercose)."],
            ['nom' => "Nématodoses", 'description' => "Infections parasitaires intestinales à vers ronds (ascaris, ankylostomes, oxyures, trichocéphales)."],
            ['nom' => "Filarioses lymphatiques (FL)", 'description' => "Infections parasitaires transmises par moustiques, pouvant entraîner un lymphœdème ou une éléphantiasis."],

            // ===== Chapitre 7 : Maladies bactériennes =====
            ['nom' => "Méningite bactérienne", 'description' => "Infection aiguë des méninges, urgence médicale nécessitant une antibiothérapie parentérale précoce."],
            ['nom' => "Tétanos", 'description' => "Toxi-infection bactérienne (Clostridium tetani) provoquant des contractures musculaires généralisées, à haute létalité."],
            ['nom' => "Fièvres entériques (typhoïde et paratyphoïde)", 'description' => "Infections systémiques à Salmonella Typhi/Paratyphi, transmises par voie oro-fécale."],
            ['nom' => "Brucellose", 'description' => "Zoonose bactérienne transmise par contact avec des animaux infectés ou consommation de produits laitiers non pasteurisés."],
            ['nom' => "Peste", 'description' => "Zoonose bactérienne (Yersinia pestis) transmise par piqûre de puce ou voie aérienne, formes bubonique, septicémique ou pulmonaire."],
            ['nom' => "Leptospirose", 'description' => "Zoonose bactérienne transmise par l'eau contaminée par les urines d'animaux infectés."],
            ['nom' => "Fièvre récurrente à poux (FRP)", 'description' => "Borréliose transmise par les poux de corps, provoquant des épisodes fébriles récurrents."],
            ['nom' => "Fièvres récurrentes à tiques (FRT)", 'description' => "Borréliose transmise par les tiques, provoquant des épisodes fébriles récurrents."],
            ['nom' => "Rickettsioses éruptives", 'description' => "Infections bactériennes transmises par tiques, poux ou acariens, provoquant fièvre et éruption cutanée."],

            // ===== Chapitre 8 : Maladies virales =====
            ['nom' => "Rougeole", 'description' => "Infection virale très contagieuse à transmission aérienne, touchant surtout l'enfant, évitable par vaccination."],
            ['nom' => "Poliomyélite", 'description' => "Infection virale pouvant entraîner une paralysie flasque aiguë, évitable par vaccination."],
            ['nom' => "Rage", 'description' => "Infection virale transmise par morsure ou griffure d'animal infecté, mortelle en l'absence de prophylaxie post-exposition."],
            ['nom' => "Hépatites virales", 'description' => "Infections virales du foie (A, B, C, D, E), aiguës ou chroniques selon le virus en cause."],
            ['nom' => "Dengue", 'description' => "Arbovirose transmise par les moustiques Aedes, pouvant évoluer vers une forme sévère avec fuite plasmatique."],
            ['nom' => "Fièvres hémorragiques virales", 'description' => "Groupe d'infections virales sévères (Ebola, Marburg, Lassa, etc.) provoquant un syndrome hémorragique, à haut risque épidémique."],
            ['nom' => "Infection par le HIV et sida", 'description' => "Infection virale chronique par le VIH, pouvant évoluer vers le stade sida en l'absence de traitement antirétroviral."],

            // ===== Chapitre 9 : Pathologie génito-urinaire =====
            ['nom' => "Syndrome néphrotique chez l'enfant", 'description' => "Association d'œdèmes, protéinurie massive, hypoalbuminémie et hyperlipidémie, primaire ou secondaire."],
            ['nom' => "Lithiase urinaire", 'description' => "Formation de calculs dans les voies urinaires, provoquant des coliques néphrétiques."],
            ['nom' => "Cystite aiguë", 'description' => "Infection urinaire basse, principalement chez la femme, se manifestant par des brûlures mictionnelles."],
            ['nom' => "Pyélonéphrite aiguë", 'description' => "Infection urinaire haute avec atteinte du parenchyme rénal, fièvre et douleur lombaire."],
            ['nom' => "Prostatite aiguë", 'description' => "Infection aiguë de la prostate, provoquant fièvre, douleur pelvienne et troubles urinaires."],
            ['nom' => "Écoulement urétral", 'description' => "Syndrome clinique évocateur d'infection sexuellement transmissible chez l'homme (gonocoque, chlamydia)."],
            ['nom' => "Écoulement vaginal anormal", 'description' => "Syndrome clinique évocateur d'infection génitale basse (vaginose, candidose, trichomonase, IST)."],
            ['nom' => "Ulcérations génitales", 'description' => "Syndrome clinique évocateur d'infection sexuellement transmissible (syphilis, chancre mou, herpès génital)."],
            ['nom' => "Douleur abdominale basse chez la femme", 'description' => "Syndrome clinique pouvant révéler une infection génitale haute, une grossesse extra-utérine ou une pathologie digestive."],
            ['nom' => "Infections génitales hautes (IGH)", 'description' => "Infection de l'utérus, des trompes et/ou des ovaires, complication des infections sexuellement transmissibles non traitées."],
            ['nom' => "Condylomes", 'description' => "Lésions génitales dues au papillomavirus humain (HPV), à transmission sexuelle."],
            ['nom' => "Saignements utérins anormaux (en dehors de la grossesse)", 'description' => "Saignements gynécologiques anormaux en dehors du contexte de grossesse, d'étiologies diverses."],

            // ===== Chapitre 10 : Pathologie médico-chirurgicale =====
            ['nom' => "Plaie simple", 'description' => "Rupture limitée de la continuité cutanée, sans atteinte des tissus nobles, nécessitant nettoyage et éventuelle suture."],
            ['nom' => "Brûlures", 'description' => "Lésions tissulaires causées par la chaleur, l'électricité ou des agents chimiques, classées selon leur profondeur et étendue."],
            ['nom' => "Abcès cutané", 'description' => "Collection purulente localisée dans le derme ou l'hypoderme, nécessitant le plus souvent un drainage chirurgical."],
            ['nom' => "Pyomyosite", 'description' => "Infection bactérienne suppurée du muscle squelettique, souvent à Staphylococcus aureus."],
            ['nom' => "Ulcère de jambe", 'description' => "Perte de substance cutanée chronique du membre inférieur, d'origine vasculaire ou infectieuse."],
            ['nom' => "Infections nécrosantes de la peau et des tissus mous", 'description' => "Infections bactériennes graves et rapidement extensives des tissus mous, urgence chirurgicale et médicale."],
            ['nom' => "Morsures et piqûres venimeuses", 'description' => "Envenimations par morsures de serpent, piqûres de scorpion ou d'autres animaux venimeux, à prise en charge urgente."],
            ['nom' => "Infections dentaires", 'description' => "Infections d'origine dentaire (caries, abcès dentaires) pouvant se compliquer d'une extension cervico-faciale."],

            // ===== Chapitre 11 : Troubles psychiques chez l'adulte =====
            ['nom' => "Anxiété", 'description' => "Trouble associant manifestations psychiques, comportementales et somatiques d'inquiétude envahissante."],
            ['nom' => "Insomnie", 'description' => "Trouble du sommeil persistant depuis au moins un mois, isolé ou associé à un trouble anxieux ou dépressif."],
            ['nom' => "Agitation", 'description' => "Excitation psychomotrice pouvant accompagner une intoxication, un sevrage ou un trouble psychiatrique aigu."],
            ['nom' => "État confusionnel", 'description' => "Trouble aigu de la conscience et de l'attention, d'origine organique le plus souvent, nécessitant une recherche étiologique urgente."],
            ['nom' => "Syndromes post-traumatiques", 'description' => "Ensemble de troubles psychiques faisant suite à l'exposition à un événement traumatique."],
            ['nom' => "Dépression", 'description' => "Trouble de l'humeur caractérisé par une tristesse persistante, une perte d'intérêt et un ralentissement psychomoteur."],
            ['nom' => "Épisode psychotique aigu", 'description' => "Rupture aiguë avec la réalité (délire, hallucinations), nécessitant une prise en charge urgente."],
            ['nom' => "Psychoses chroniques", 'description' => "Troubles psychotiques évoluant sur le long terme, nécessitant un traitement antipsychotique prolongé."],
            ['nom' => "Troubles bipolaires", 'description' => "Trouble de l'humeur caractérisé par l'alternance d'épisodes maniaques et dépressifs."],

            // ===== Chapitre 12 : Autres pathologies =====
            ['nom' => "Drépanocytose", 'description' => "Maladie génétique grave de l'hémoglobine (HbS) entraînant hémolyse chronique, crises vaso-occlusives douloureuses et complications aiguës graves."],
            ['nom' => "Épilepsie", 'description' => "Maladie neurologique chronique caractérisée par des crises convulsives récurrentes."],
            ['nom' => "Diabète de type 2 chez l'adulte", 'description' => "Maladie métabolique chronique caractérisée par une hyperglycémie liée à une insulinorésistance."],
            ['nom' => "Hypertension artérielle essentielle de l'adulte (HTA)", 'description' => "Élévation chronique de la pression artérielle sans cause secondaire identifiée, facteur de risque cardiovasculaire majeur."],
            ['nom' => "Insuffisance cardiaque chronique", 'description' => "Incapacité chronique du cœur à assurer un débit sanguin suffisant, provoquant dyspnée et œdèmes."],
            ['nom' => "Insuffisance cardiaque aiguë (OAP)", 'description' => "Décompensation aiguë de la fonction cardiaque avec œdème aigu du poumon, urgence vitale."],
            ['nom' => "Goitre endémique et carence en iode", 'description' => "Augmentation de volume de la thyroïde liée à une carence chronique en iode."],
        ];

        foreach ($maladies as $m) {
            Maladie::firstOrCreate(
                ['nom' => $m['nom']],
                ['uuid' => (string) Str::uuid(), 'description' => $m['description']]
            );
        }
    }
}
