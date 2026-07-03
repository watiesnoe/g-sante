<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Symptome;

/**
 * Source : Médecins Sans Frontières, Guide clinique et thérapeutique, Décembre 2024.
 * Chapitre 1 : "Quelques symptômes ou syndromes" + symptômes cliniques transverses
 * rencontrés dans l'ensemble des chapitres du guide (respiratoire, digestif,
 * dermatologique, génito-urinaire, neuro-psychique, etc.).
 *
 * NB : Les descriptions sont des synthèses reformulées à usage de fiche clinique,
 * et non une reproduction du texte du guide.
 */
class SymptomesSeeder extends Seeder
{
    public function run(): void
    {
        $symptomes = [
            // --- Chapitre 1 : symptômes / syndromes majeurs ---
            ['nom' => 'État de choc', 'description' => "Réduction généralisée de la perfusion tissulaire et apport insuffisant en oxygène aux organes ; urgence vitale (distributif, cardiogénique, hypovolémique ou obstructif)."],
            ['nom' => 'Convulsions / état de mal convulsif', 'description' => "Crises généralisées tonico-cloniques, isolées ou répétées ; un état de mal convulsif est une urgence neurologique."],
            ['nom' => 'Hypoglycémie', 'description' => "Baisse anormale de la glycémie pouvant entraîner convulsions, troubles de conscience ou coma, notamment chez l'enfant et le patient dénutri."],
            ['nom' => 'Fièvre', 'description' => "Élévation de la température corporelle au-dessus de la normale, signe d'appel de très nombreuses pathologies infectieuses."],
            ['nom' => 'Douleur', 'description' => "Symptôme subjectif à évaluer systématiquement (intensité, type) afin d'adapter le traitement antalgique selon l'échelle de l'OMS."],
            ['nom' => 'Anémie', 'description' => "Baisse du taux d'hémoglobine se traduisant par pâleur, asthénie, dyspnée d'effort et parfois tachycardie."],
            ['nom' => 'Déshydratation', 'description' => "Perte hydro-électrolytique liée notamment aux diarrhées et vomissements ; classée en légère, modérée ou sévère selon les signes cliniques."],
            ['nom' => 'Malnutrition aiguë sévère', 'description' => "Amaigrissement extrême (marasme) et/ou œdèmes bilatéraux (kwashiorkor) mettant en jeu le pronostic vital, notamment chez l'enfant."],

            // --- Symptômes cliniques transverses ---
            ['nom' => 'Toux', 'description' => "Symptôme respiratoire fréquent, aiguë ou chronique, associé aux infections des voies respiratoires hautes ou basses."],
            ['nom' => 'Céphalées', 'description' => "Douleur crânienne, isolée ou associée à un syndrome fébrile, méningé ou neurologique."],
            ['nom' => 'Diarrhée', 'description' => "Émission d'au moins 3 selles liquides par jour ; aiguë (< 2 semaines) ou chronique, sanglante ou non."],
            ['nom' => 'Vomissements', 'description' => "Rejet actif du contenu gastrique, pouvant favoriser la déshydratation."],
            ['nom' => 'Douleur abdominale', 'description' => "Douleur localisée ou diffuse de l'abdomen, d'origine digestive, génito-urinaire ou parasitaire."],
            ['nom' => 'Éruption cutanée', 'description' => "Lésion(s) cutanée(s) visible(s) : macules, papules, vésicules, pustules selon la pathologie causale."],
            ['nom' => 'Prurit', 'description' => "Démangeaisons cutanées, évocatrices notamment de gale, mycose ou allergie."],
            ['nom' => 'Ictère', 'description' => "Coloration jaune des téguments et muqueuses liée à une hyperbilirubinémie (hépatite, paludisme sévère, hémolyse)."],
            ['nom' => 'Dyspnée', 'description' => "Difficulté respiratoire, signe de gravité devant faire rechercher une cause pulmonaire, cardiaque ou métabolique."],
            ['nom' => 'Frissons', 'description' => "Sensation de froid avec tremblements, souvent associée à un pic fébrile d'origine infectieuse."],
            ['nom' => 'Sueurs nocturnes', 'description' => "Transpiration excessive nocturne, évocatrice notamment de tuberculose ou d'infection chronique."],
            ['nom' => 'Amaigrissement', 'description' => "Perte de poids significative, signe d'appel de pathologies chroniques infectieuses, endocriniennes ou néoplasiques."],
            ['nom' => 'Œdèmes', 'description' => "Gonflement lié à une accumulation de liquide dans les tissus (rénal, cardiaque, nutritionnel)."],
            ['nom' => 'Raideur de la nuque', 'description' => "Résistance douloureuse à la flexion de la nuque, signe cardinal du syndrome méningé."],
            ['nom' => 'Photophobie', 'description' => "Gêne ou douleur oculaire provoquée par la lumière, associée aux syndromes méningés et à certaines atteintes oculaires."],
            ['nom' => 'Otalgie', 'description' => "Douleur de l'oreille, principal signe d'appel des otites."],
            ['nom' => 'Écoulement nasal', 'description' => "Rhinorrhée claire ou purulente, signe d'appel des infections rhino-sinusiennes."],
            ['nom' => 'Mal de gorge', 'description' => "Douleur pharyngée, signe d'appel des angines et pharyngites."],
            ['nom' => 'Adénopathies', 'description' => "Augmentation de volume des ganglions lymphatiques, réactionnelle à une infection locale ou systémique."],
            ['nom' => 'Stridor', 'description' => "Bruit inspiratoire aigu et anormal, signe de gravité d'une obstruction des voies aériennes supérieures."],
            ['nom' => 'Sifflements respiratoires', 'description' => "Sibilants expiratoires perçus à l'auscultation, évocateurs de bronchospasme (asthme, bronchiolite)."],
            ['nom' => 'Hématurie', 'description' => "Présence de sang dans les urines, signe d'appel néphro-urologique."],
            ['nom' => 'Brûlures mictionnelles', 'description' => "Douleur ou brûlure à la miction, signe d'appel d'une infection urinaire basse."],
            ['nom' => 'Écoulement urétral', 'description' => "Écoulement anormal au niveau du méat urétral, signe d'appel d'une infection sexuellement transmissible."],
            ['nom' => 'Écoulement vaginal anormal', 'description' => "Leucorrhées anormales en abondance, couleur ou odeur, signe d'appel d'une infection génitale."],
            ['nom' => 'Ulcération génitale', 'description' => "Perte de substance des muqueuses ou de la peau génitale, évocatrice d'une infection sexuellement transmissible."],
            ['nom' => 'Convulsions fébriles', 'description' => "Crises convulsives survenant dans un contexte de fièvre, principalement chez le jeune enfant."],
            ['nom' => 'Altération de la conscience', 'description' => "Baisse du niveau de vigilance, allant de la confusion au coma ; signe de gravité systématique."],
            ['nom' => 'Splénomégalie', 'description' => "Augmentation de volume de la rate, palpable, évocatrice notamment de paludisme chronique ou de drépanocytose."],
            ['nom' => 'Hépatomégalie', 'description' => "Augmentation de volume du foie, palpable, associée à diverses pathologies infectieuses ou hépatiques."],
            ['nom' => 'Polyurie / polydipsie', 'description' => "Augmentation du volume des urines associée à une soif excessive, évocatrice d'un diabète."],
            ['nom' => 'Anxiété', 'description' => "Inquiétude envahissante avec manifestations psychiques, comportementales et somatiques."],
            ['nom' => 'Insomnie', 'description' => "Trouble du sommeil (endormissement, réveils, sommeil non réparateur) durant au moins un mois."],
            ['nom' => 'Agitation', 'description' => "Excitation psychomotrice pouvant s'accompagner de comportements d'opposition ou de violence."],
            ['nom' => 'Tremblements', 'description' => "Mouvements involontaires rythmiques, évocateurs notamment de sevrage, d'intoxication ou de troubles métaboliques."],
        ];

        foreach ($symptomes as $s) {
            Symptome::firstOrCreate(
                ['nom' => $s['nom']],
                ['uuid' => (string) Str::uuid(), 'description' => $s['description']]
            );
        }
    }
}
