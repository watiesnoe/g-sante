<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Complète les protocoles de traitement manquants identifiés lors de l'audit
 * du catalogue Maladies (60 pathologies sans protocole après la fusion des
 * doublons OMS). Sources : recommandations OMS, littérature médicale récente
 * (recherche effectuée en septembre 2026) et essentiels génériques disponibles
 * en contexte de ressources limitées (Afrique de l'Ouest).
 *
 * Les pathologies purement chirurgicales/procédurales (appendicite, hernie
 * inguinale, cataracte, cancers, grossesse extra-utérine, kyste ovarien) sont
 * volontairement exclues : il n'existe pas de "médicament principal" réel et
 * générique à leur attribuer sans risque de donner une fausse indication.
 */
class RemainingProtocolsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Familles minimales requises (réutilisées si déjà existantes)
        foreach ([
            'Antibiotiques', 'Antiparasitaires', 'Antifongiques', 'Antiviraux',
            'Antalgiques', 'Antipyrétiques', 'Anti-inflammatoires', 'Antihistaminiques',
            'Antidépresseurs', 'Anxiolytiques', 'Anticonvulsivants', 'Solutés',
            'Vitamines', 'Dermatologiques', 'Gastro-entérologie', 'Divers',
        ] as $famille) {
            DB::table('familles')->updateOrInsert(
                ['nom' => $famille],
                ['uuid' => (string) Str::uuid(), 'created_at' => $now, 'updated_at' => $now]
            );
        }
        $familleIds = DB::table('familles')->pluck('id', 'nom');

        $data = $this->protocoles();

        foreach ($data as $entry) {
            $maladie = DB::table('maladies')->where('nom', $entry['maladie'])->first();
            if (!$maladie) {
                // Ne devrait pas arriver (toutes ces maladies existent déjà), sécurité seulement
                continue;
            }
            $maladieId = $maladie->id;

            // Protocole (créé une seule fois par maladie)
            $existingProto = DB::table('protocole_traitements')->where('maladie_id', $maladieId)->first();
            if ($existingProto) {
                continue; // déjà traité par un seeder précédent, on ne touche pas
            }

            $protocoleId = DB::table('protocole_traitements')->insertGetId([
                'maladie_id' => $maladieId,
                'uuid' => (string) Str::uuid(),
                'titre' => $entry['titre'],
                'signes' => $entry['signes'] ?? null,
                'diagnostics' => $entry['diagnostics'] ?? null,
                'remarques' => $entry['remarques'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($entry['medicaments'] as $med) {
                $medRow = DB::table('medicaments')->where('nom', $med['nom'])->first();
                if (!$medRow) {
                    $famId = $familleIds[$med['famille']] ?? $familleIds['Divers'];
                    $medUuid = (string) Str::uuid();
                    $medId = DB::table('medicaments')->insertGetId([
                        'nom' => $med['nom'],
                        'description' => $med['description'] ?? null,
                        'stock' => 50,
                        'stock_min' => 5,
                        'famille_id' => $famId,
                        'uuid' => $medUuid,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    DB::table('unites')->insert([
                        'nom' => 'Unité',
                        'symbole' => 'U',
                        'prix_achat' => 500,
                        'prix_vente' => 1000,
                        'facteur' => 1.0,
                        'is_default' => true,
                        'medicament_id' => $medId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                } else {
                    $medId = $medRow->id;
                }

                DB::table('protocole_medicament')->updateOrInsert(
                    ['protocole_id' => $protocoleId, 'medicament_id' => $medId],
                    [
                        'uuid' => (string) Str::uuid(),
                        'type' => $med['type'] ?? 'principal',
                        'posologie' => $med['posologie'],
                        'duree' => $med['duree'] ?? null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }

        $this->command?->info('Protocoles restants seedés avec succès (RemainingProtocolsSeeder).');
    }

    private function protocoles(): array
    {
        return [
            // ===== PATHOLOGIES INFECTIEUSES / TROPICALES =====
            [
                'maladie' => 'Dracunculose (ver de Guinée)',
                'titre' => 'Prise en charge Dracunculose (Ver de Guinée)',
                'signes' => 'Papule douloureuse évoluant vers une ulcération avec émergence du ver filaire',
                'diagnostics' => 'Diagnostic clinique (émergence visible du ver)',
                'remarques' => "Aucun médicament ne guérit la dracunculose : extraction manuelle progressive et lente du ver (quelques cm/jour, enroulé sur un bâtonnet) sur plusieurs semaines. Prévention = filtration de l'eau de boisson. Déclaration obligatoire (maladie en voie d'éradication).",
                'medicaments' => [
                    ['nom' => 'Ibuprofène', 'famille' => 'Anti-inflammatoires', 'posologie' => '400mg 3x/jour si douleur/inflammation locale', 'duree' => 'Selon besoin', 'type' => 'adjuvant'],
                ],
            ],
            [
                'maladie' => 'Ulcère de Buruli',
                'titre' => 'Protocole Ulcère de Buruli (Mycobacterium ulcerans)',
                'signes' => 'Nodule ou plaque indolore évoluant vers une ulcération nécrotique aux berges décollées',
                'diagnostics' => 'PCR IS2404 sur écouvillon/biopsie, examen direct',
                'remarques' => 'Antibiothérapie 8 semaines. Chirurgie (excision, greffe) associée pour les lésions étendues ou séquellaires. Soins de plaie réguliers.',
                'medicaments' => [
                    ['nom' => 'Rifampicine', 'famille' => 'Antibiotiques', 'posologie' => '10 mg/kg/jour en 1 prise', 'duree' => '8 semaines', 'type' => 'principal'],
                    ['nom' => 'Clarithromycine', 'famille' => 'Antibiotiques', 'description' => 'Macrolide, associé à la rifampicine dans l\'ulcère de Buruli', 'posologie' => '7,5 mg/kg 2 fois/jour', 'duree' => '8 semaines', 'type' => 'principal'],
                ],
            ],
            [
                'maladie' => 'Toxoplasmose',
                'titre' => 'Protocole Toxoplasmose (forme symptomatique)',
                'signes' => 'Adénopathies, asthénie, fièvre modérée ; formes graves chez l\'immunodéprimé (encéphalite) et la femme enceinte (risque foetal)',
                'diagnostics' => 'Sérologie IgG/IgM, PCR liquide amniotique si grossesse',
                'remarques' => 'Traitement réservé aux formes symptomatiques, à la grossesse et à l\'immunodépression. Association à l\'acide folinique obligatoire pour prévenir la toxicité hématologique.',
                'medicaments' => [
                    ['nom' => 'Pyriméthamine', 'famille' => 'Antiparasitaires', 'posologie' => 'Charge 100mg/j 2 jours puis 25-50mg/jour', 'duree' => '4 à 6 semaines', 'type' => 'principal'],
                    ['nom' => 'Sulfadiazine', 'famille' => 'Antibiotiques', 'posologie' => '1g 4 fois/jour', 'duree' => '4 à 6 semaines', 'type' => 'principal'],
                    ['nom' => 'Acide folinique', 'famille' => 'Vitamines', 'posologie' => '10 à 25mg/jour', 'duree' => 'Pendant toute la durée du traitement', 'type' => 'adjuvant'],
                ],
            ],
            [
                'maladie' => 'Rougeole',
                'titre' => 'Prise en charge Rougeole',
                'signes' => 'Fièvre élevée, catarrhe oculo-nasal, éruption maculopapuleuse descendante, signe de Koplik',
                'diagnostics' => 'Diagnostic clinique, sérologie IgM si confirmation nécessaire',
                'remarques' => "Pas d'antiviral spécifique. La vitamine A réduit la morbi-mortalité et le risque de complications oculaires (recommandation OMS systématique chez l'enfant). Isolement respiratoire.",
                'medicaments' => [
                    ['nom' => 'Rétinol (Vitamine A)', 'famille' => 'Vitamines', 'posologie' => '<12 mois: 100 000 UI ; >=12 mois: 200 000 UI à J1 et J2', 'duree' => '2 jours', 'type' => 'principal'],
                    ['nom' => 'Paracétamol', 'famille' => 'Antipyrétiques', 'posologie' => '15 mg/kg toutes les 6h si fièvre', 'duree' => 'Selon besoin', 'type' => 'adjuvant'],
                ],
            ],
            [
                'maladie' => 'Rubéole',
                'titre' => 'Prise en charge Rubéole',
                'signes' => 'Fièvre modérée, éruption maculopapuleuse discrète, adénopathies rétro-auriculaires',
                'diagnostics' => 'Diagnostic clinique, sérologie si grossesse (risque de rubéole congénitale)',
                'remarques' => "Traitement symptomatique uniquement, pas d'antiviral. Vigilance particulière si contact avec une femme enceinte non immunisée.",
                'medicaments' => [
                    ['nom' => 'Paracétamol', 'famille' => 'Antipyrétiques', 'posologie' => '15 mg/kg toutes les 6h si fièvre', 'duree' => 'Selon besoin', 'type' => 'principal'],
                ],
            ],
            [
                'maladie' => 'Oreillons',
                'titre' => 'Prise en charge Oreillons',
                'signes' => 'Tuméfaction parotidienne douloureuse, fièvre ; complications possibles (orchite, méningite)',
                'diagnostics' => 'Diagnostic clinique',
                'remarques' => "Traitement symptomatique. Surveiller les complications (orchite chez l'adolescent/adulte, méningite virale).",
                'medicaments' => [
                    ['nom' => 'Paracétamol', 'famille' => 'Antipyrétiques', 'posologie' => '15 mg/kg toutes les 6h si fièvre/douleur', 'duree' => 'Selon besoin', 'type' => 'principal'],
                ],
            ],
            [
                'maladie' => 'Hépatite virale A',
                'titre' => 'Prise en charge Hépatite virale A',
                'signes' => 'Ictère, asthénie, douleurs abdominales, nausées ; guérison spontanée habituelle',
                'diagnostics' => 'Sérologie IgM anti-VHA, bilan hépatique',
                'remarques' => "Pas d'antiviral spécifique. Repos, éviction de l'alcool et des médicaments hépatotoxiques (dont le paracétamol à dose réduite). Guérison spontanée en 2 à 6 semaines dans la majorité des cas.",
                'medicaments' => [
                    ['nom' => 'Paracétamol', 'famille' => 'Antipyrétiques', 'posologie' => 'Dose réduite si besoin (hépatotoxicité) : max 2g/jour', 'duree' => 'Selon besoin', 'type' => 'adjuvant'],
                ],
            ],
            [
                'maladie' => 'Trypanosomiase',
                'titre' => 'Protocole Trypanosomiase humaine africaine (maladie du sommeil)',
                'signes' => 'Fièvre, adénopathies (signe de Winterbottom), puis troubles neurologiques/du sommeil au stade avancé (atteinte du SNC)',
                'diagnostics' => 'Examen parasitologique (sang, ganglion), ponction lombaire pour stadification (atteinte du SNC ou non)',
                'remarques' => "Recommandation OMS 2024 (mise à jour majeure) : le fexinidazole remplace désormais la suramine et le mélarsoprol comme traitement de 1ère ligne pour la forme gambiense chez les patients de 6 ans et plus, pesant au moins 20 kg, quel que soit le stade. Pour les patients plus jeunes/légers ou la forme rhodesiense, se référer aux schémas spécialisés (pentamidine, NECT). Prise en charge en centre spécialisé recommandée.",
                'medicaments' => [
                    ['nom' => 'Fexinidazole', 'famille' => 'Antiparasitaires', 'description' => 'Traitement oral de 1ère ligne (OMS 2024) pour la trypanosomiase à T. b. gambiense', 'posologie' => 'Selon poids : 1800mg/j (J1-J4) puis 1200mg/j (J5-J10), avec repas', 'duree' => '10 jours', 'type' => 'principal'],
                    ['nom' => 'Pentamidine', 'famille' => 'Antiparasitaires', 'posologie' => '4 mg/kg/jour IM', 'duree' => '7 jours (stade précoce, alternative)', 'type' => 'alternatif'],
                ],
            ],
            [
                'maladie' => 'Prostatite',
                'titre' => 'Prise en charge Prostatite bactérienne',
                'signes' => 'Douleur périnéale/pelvienne, brûlures mictionnelles, fièvre (forme aiguë), pollakiurie',
                'diagnostics' => 'ECBU, toucher rectal, échographie si suspicion d\'abcès',
                'remarques' => "Antibiothérapie prolongée nécessaire du fait de la faible pénétration tissulaire prostatique. Forme aiguë fébrile : évaluer une hospitalisation initiale si signes de gravité.",
                'medicaments' => [
                    ['nom' => 'Ciprofloxacine', 'famille' => 'Antibiotiques', 'posologie' => '500mg 2 fois/jour', 'duree' => '14 à 21 jours (aiguë), jusqu\'à 4-6 semaines (chronique)', 'type' => 'principal'],
                ],
            ],
            [
                'maladie' => 'Varicelle',
                'titre' => 'Prise en charge Varicelle',
                'signes' => 'Éruption vésiculeuse prurigineuse évoluant par poussées, fièvre modérée',
                'diagnostics' => 'Diagnostic clinique',
                'remarques' => "Traitement symptomatique dans les formes non compliquées. Aciclovir réservé aux formes sévères, aux nouveau-nés et aux immunodéprimés. Ne jamais utiliser l'aspirine chez l'enfant (risque de syndrome de Reye).",
                'medicaments' => [
                    ['nom' => 'Paracétamol', 'famille' => 'Antipyrétiques', 'posologie' => '15 mg/kg toutes les 6h si fièvre', 'duree' => 'Selon besoin', 'type' => 'principal'],
                    ['nom' => 'Aciclovir', 'famille' => 'Antiviraux', 'posologie' => '20 mg/kg 4 fois/jour (formes sévères/immunodéprimés)', 'duree' => '5 à 7 jours', 'type' => 'alternatif'],
                ],
            ],
            [
                'maladie' => 'COVID-19',
                'titre' => 'Prise en charge COVID-19 (forme ambulatoire)',
                'signes' => 'Fièvre, toux, asthénie, anosmie/agueusie, parfois dyspnée',
                'diagnostics' => 'Test antigénique ou PCR SARS-CoV-2',
                'remarques' => "Traitement symptomatique pour les formes légères. Antiviral (Nirmatrelvir/Ritonavir) à envisager dans les 5 jours suivant le début des symptômes chez les patients à haut risque de forme grave. Référer en urgence si signes de détresse respiratoire (SpO2 < 94%).",
                'medicaments' => [
                    ['nom' => 'Paracétamol', 'famille' => 'Antipyrétiques', 'posologie' => '15 mg/kg (max 1g) toutes les 6h si fièvre', 'duree' => 'Selon besoin', 'type' => 'principal'],
                    ['nom' => 'Nirmatrelvir/Ritonavir', 'famille' => 'Antiviraux', 'posologie' => '300mg/100mg 2 fois/jour (patient à haut risque, <5j de symptômes)', 'duree' => '5 jours', 'type' => 'alternatif'],
                ],
            ],
            [
                'maladie' => 'Fièvre jaune',
                'titre' => 'Prise en charge Fièvre jaune',
                'signes' => 'Fièvre, céphalées, myalgies, puis ictère, hémorragies dans les formes graves',
                'diagnostics' => 'Sérologie, PCR, diagnostic clinique en zone d\'endémie',
                'remarques' => "Aucun antiviral spécifique. Traitement uniquement symptomatique et de support (réhydratation, surveillance hépatique/rénale). Prévention = vaccination. Maladie à déclaration obligatoire.",
                'medicaments' => [
                    ['nom' => 'Paracétamol', 'famille' => 'Antipyrétiques', 'posologie' => '15 mg/kg toutes les 6h (éviter AINS et aspirine : risque hémorragique)', 'duree' => 'Selon besoin', 'type' => 'principal'],
                ],
            ],
            [
                'maladie' => 'VIH/SIDA',
                'titre' => 'Protocole VIH — 1ère ligne adulte (référence générale)',
                'signes' => 'Amaigrissement, infections opportunistes récidivantes, fièvre prolongée, candidose orale',
                'diagnostics' => 'Sérologie VIH (test rapide + confirmation), charge virale, CD4',
                'remarques' => "Protocole de référence pour l'entrée générale ; se référer aux protocoles spécifiques déjà disponibles (VIH nourrisson, enfant, 2ème ligne, PTME) selon le profil du patient. Suivi biologique régulier indispensable (CV, CD4).",
                'medicaments' => [
                    ['nom' => 'Ténofovir + Lamivudine + Dolutégravir (TLD)', 'famille' => 'Antiviraux', 'description' => 'Trithérapie ARV de 1ère ligne recommandée par l\'OMS (2023)', 'posologie' => '1 comprimé une fois par jour', 'duree' => 'Traitement à vie', 'type' => 'principal'],
                ],
            ],
            [
                'maladie' => 'Lèpre',
                'titre' => 'Protocole Lèpre — orientation générale (à confirmer par classification OMS)',
                'signes' => 'Lésions cutanées hypopigmentées ou érythémateuses avec troubles de la sensibilité, atteinte nerveuse périphérique',
                'diagnostics' => 'Examen clinique, classification OMS (nombre de lésions/frottis), biopsie cutanée',
                'remarques' => "Classer précisément le patient en paucibacillaire (PB, 1 à 5 lésions) ou multibacillaire (MB, >5 lésions) dès que possible : les protocoles complets et corrects sont déjà disponibles dans le système sous ces noms. Ce protocole générique donne le schéma MB par précaution (le plus complet) en attendant la classification.",
                'medicaments' => [
                    ['nom' => 'Dapsone', 'famille' => 'Antibiotiques', 'posologie' => 'Selon blister OMS ajusté à l\'âge', 'duree' => '12 mois (MB) ou 6 mois (PB)', 'type' => 'principal'],
                    ['nom' => 'Rifampicine', 'famille' => 'Antibiotiques', 'posologie' => 'Dose mensuelle supervisée selon blister OMS', 'duree' => '6 à 12 mois selon forme', 'type' => 'principal'],
                    ['nom' => 'Clofazimine', 'famille' => 'Antibiotiques', 'posologie' => 'Selon blister OMS (forme MB uniquement)', 'duree' => '12 mois', 'type' => 'principal'],
                ],
            ],
            [
                'maladie' => 'Épilepsie',
                'titre' => 'Protocole Épilepsie — 1ère ligne en soins primaires (à ajuster selon type de crise)',
                'signes' => 'Crises convulsives récurrentes, pertes de connaissance, mouvements anormaux',
                'diagnostics' => 'EEG, imagerie cérébrale si disponible, bilan étiologique',
                'remarques' => "Le phénobarbital reste, selon l'OMS, l'antiépileptique de 1ère ligne recommandé en soins primaires dans les contextes à ressources limitées, en raison de son faible coût et de sa large disponibilité. Si le type de crise est précisé, utiliser le protocole spécifique déjà disponible (partielles/généralisées/absences), plus ciblé.",
                'medicaments' => [
                    ['nom' => 'Phénobarbital', 'famille' => 'Anticonvulsivants', 'posologie' => '3 à 5 mg/kg/jour en 1 ou 2 prises', 'duree' => 'Traitement de fond, réévaluation à 2 ans sans crise', 'type' => 'principal'],
                ],
            ],

            // ===== INFECTIONS COURANTES =====
            [
                'maladie' => 'Bronchite aiguë',
                'titre' => 'Prise en charge Bronchite aiguë',
                'signes' => 'Toux (sèche puis productive), fièvre modérée, gêne thoracique',
                'diagnostics' => 'Diagnostic clinique ; radiographie si suspicion de pneumonie',
                'remarques' => "Origine virale dans plus de 90% des cas : pas d'antibiotique systématique. Antibiothérapie réservée aux surinfections bactériennes confirmées ou terrain à risque (BPCO, âge avancé).",
                'medicaments' => [
                    ['nom' => 'Paracétamol', 'famille' => 'Antipyrétiques', 'posologie' => '1g toutes les 6h si fièvre/douleur', 'duree' => 'Selon besoin', 'type' => 'principal'],
                    ['nom' => 'Amoxicilline', 'famille' => 'Antibiotiques', 'posologie' => '1g 3 fois/jour (si surinfection bactérienne confirmée)', 'duree' => '7 jours', 'type' => 'alternatif'],
                ],
            ],
            [
                'maladie' => 'Sinusite chronique',
                'titre' => 'Prise en charge Sinusite (poussée aiguë sur fond chronique)',
                'signes' => 'Douleur/pesanteur faciale, rhinorrhée purulente, obstruction nasale prolongée (>12 semaines pour la forme chronique)',
                'diagnostics' => 'Examen ORL, imagerie (scanner des sinus) si résistance au traitement',
                'remarques' => "Corticoïde nasal en traitement de fond. Antibiothérapie réservée aux poussées avec signes de surinfection bactérienne (fièvre, douleur intense, pus).",
                'medicaments' => [
                    ['nom' => 'Budésonide', 'famille' => 'Anti-inflammatoires', 'posologie' => '1 à 2 pulvérisations par narine 2 fois/jour', 'duree' => 'Traitement de fond prolongé', 'type' => 'principal'],
                    ['nom' => 'Amoxicilline + acide clavulanique', 'famille' => 'Antibiotiques', 'posologie' => '1g 2 à 3 fois/jour (poussée bactérienne)', 'duree' => '7 à 10 jours', 'type' => 'alternatif'],
                ],
            ],
            [
                'maladie' => 'Otite chronique',
                'titre' => 'Prise en charge Otite moyenne chronique (à écoulement)',
                'signes' => 'Otorrhée chronique ou récidivante, hypoacousie, perforation tympanique',
                'diagnostics' => 'Otoscopie, audiométrie, référence ORL si échec du traitement',
                'remarques' => "Assèchement de l'oreille (mèches, évitement de l'eau) associé à l'antibiothérapie locale. Référer en ORL si persistance au-delà de plusieurs semaines (risque de cholestéatome).",
                'medicaments' => [
                    ['nom' => 'Ciprofloxacine 0,3% (gouttes auriculaires)', 'famille' => 'Antibiotiques', 'posologie' => '3 à 4 gouttes dans l\'oreille atteinte 2 fois/jour', 'duree' => '2 à 4 semaines', 'type' => 'principal'],
                ],
            ],
            [
                'maladie' => 'Infection respiratoire aiguë',
                'titre' => 'Prise en charge Infection respiratoire aiguë (voies hautes)',
                'signes' => 'Rhinorrhée, toux, fièvre modérée, mal de gorge',
                'diagnostics' => 'Diagnostic clinique',
                'remarques' => "Le plus souvent virale : traitement symptomatique. Rechercher des signes de gravité (tachypnée, tirage) évocateurs d'une atteinte des voies basses (référer vers le protocole Pneumonie si présents).",
                'medicaments' => [
                    ['nom' => 'Paracétamol', 'famille' => 'Antipyrétiques', 'posologie' => '15 mg/kg toutes les 6h si fièvre', 'duree' => 'Selon besoin', 'type' => 'principal'],
                ],
            ],
            [
                'maladie' => 'Infection néonatale',
                'titre' => 'Prise en charge Infection néonatale (suspicion de sepsis)',
                'signes' => 'Léthargie, instabilité thermique, refus de téter, détresse respiratoire chez un nouveau-né',
                'diagnostics' => 'Bilan infectieux, hémoculture, PL si signes méningés',
                'remarques' => "Urgence néonatale : hospitalisation systématique. Bithérapie probabiliste large spectre dans l'attente des résultats bactériologiques.",
                'medicaments' => [
                    ['nom' => 'Ampicilline', 'famille' => 'Antibiotiques', 'posologie' => '50 mg/kg toutes les 8 à 12h IV selon âge', 'duree' => '7 à 10 jours', 'type' => 'principal'],
                    ['nom' => 'Gentamicine', 'famille' => 'Antibiotiques', 'posologie' => '4 à 5 mg/kg une fois par jour IV', 'duree' => '7 à 10 jours', 'type' => 'principal'],
                ],
            ],
            [
                'maladie' => 'Gastro-entérite',
                'titre' => 'Prise en charge Gastro-entérite aiguë',
                'signes' => 'Diarrhée, vomissements, douleurs abdominales, fièvre variable',
                'diagnostics' => 'Diagnostic clinique ; coproculture si signes de gravité ou dysenterie',
                'remarques' => "Réhydratation en 1ère intention (SRO). Le zinc réduit la durée et la sévérité chez l'enfant. Antibiotique réservé aux formes bactériennes spécifiques (moins de 10% des cas).",
                'medicaments' => [
                    ['nom' => 'Sels de Réhydratation Orale', 'famille' => 'Solutés', 'posologie' => '50 à 100 ml/kg sur 4h puis selon pertes', 'duree' => 'Jusqu\'à résolution', 'type' => 'principal'],
                    ['nom' => 'Sulfate de zinc', 'famille' => 'Vitamines', 'posologie' => '<6 mois : 10mg/j ; >=6 mois : 20mg/j', 'duree' => '10 à 14 jours', 'type' => 'principal'],
                ],
            ],
            [
                'maladie' => 'Intoxication alimentaire',
                'titre' => 'Prise en charge Intoxication alimentaire',
                'signes' => 'Nausées, vomissements, diarrhée, douleurs abdominales, dans les heures suivant un repas',
                'diagnostics' => 'Diagnostic clinique, enquête alimentaire, coproculture si collectivité/gravité',
                'remarques' => "Prise en charge identique à la gastro-entérite aiguë : réhydratation prioritaire. Antibiothérapie non systématique.",
                'medicaments' => [
                    ['nom' => 'Sels de Réhydratation Orale', 'famille' => 'Solutés', 'posologie' => '50 à 100 ml/kg sur 4h puis selon pertes', 'duree' => 'Jusqu\'à résolution', 'type' => 'principal'],
                    ['nom' => 'Sulfate de zinc', 'famille' => 'Vitamines', 'posologie' => '<6 mois : 10mg/j ; >=6 mois : 20mg/j', 'duree' => '10 à 14 jours', 'type' => 'adjuvant'],
                ],
            ],
            [
                'maladie' => 'Colite',
                'titre' => 'Prise en charge Colite infectieuse aiguë',
                'signes' => 'Diarrhée parfois glairo-sanglante, douleurs abdominales, ténesme, fièvre',
                'diagnostics' => 'Coproculture, examen parasitologique des selles',
                'remarques' => "Réhydratation systématique. Antibiothérapie ciblée si dysenterie bactérienne confirmée ou suspectée (Shigella).",
                'medicaments' => [
                    ['nom' => 'Sels de Réhydratation Orale', 'famille' => 'Solutés', 'posologie' => 'Selon pertes hydriques', 'duree' => 'Jusqu\'à résolution', 'type' => 'principal'],
                    ['nom' => 'Ciprofloxacine', 'famille' => 'Antibiotiques', 'posologie' => '500mg 2 fois/jour (dysenterie bactérienne)', 'duree' => '3 jours', 'type' => 'alternatif'],
                ],
            ],
            [
                'maladie' => 'Infection à Helicobacter pylori',
                'titre' => 'Éradication Helicobacter pylori',
                'signes' => 'Douleurs épigastriques, dyspepsie, associé à l\'ulcère gastroduodénal et à la gastrite chronique',
                'diagnostics' => 'Test respiratoire à l\'urée, recherche antigène fécal, biopsie gastrique',
                'remarques' => "Trithérapie standard (IPP + 2 antibiotiques) pendant 14 jours. Contrôle d'éradication recommandé à distance du traitement.",
                'medicaments' => [
                    ['nom' => 'Oméprazole', 'famille' => 'Gastro-entérologie', 'posologie' => '20mg 2 fois/jour', 'duree' => '14 jours', 'type' => 'principal'],
                    ['nom' => 'Amoxicilline', 'famille' => 'Antibiotiques', 'posologie' => '1g 2 fois/jour', 'duree' => '14 jours', 'type' => 'principal'],
                    ['nom' => 'Clarithromycine', 'famille' => 'Antibiotiques', 'posologie' => '500mg 2 fois/jour', 'duree' => '14 jours', 'type' => 'principal'],
                ],
            ],
            [
                'maladie' => 'Gastrite chronique',
                'titre' => 'Prise en charge Gastrite chronique',
                'signes' => 'Douleurs épigastriques, dyspepsie, nausées',
                'diagnostics' => 'Endoscopie digestive haute, recherche H. pylori',
                'remarques' => "Traiter l'Helicobacter pylori si positif (voir protocole dédié). IPP en traitement symptomatique de fond sinon.",
                'medicaments' => [
                    ['nom' => 'Oméprazole', 'famille' => 'Gastro-entérologie', 'posologie' => '20mg une fois par jour', 'duree' => '4 à 8 semaines', 'type' => 'principal'],
                ],
            ],
            [
                'maladie' => 'Reflux gastro-œsophagien',
                'titre' => 'Prise en charge Reflux gastro-œsophagien',
                'signes' => 'Pyrosis, régurgitations acides, parfois toux chronique nocturne',
                'diagnostics' => 'Diagnostic clinique ; endoscopie si signes d\'alarme',
                'remarques' => "Mesures hygiéno-diététiques associées (surélévation de la tête de lit, éviter repas tardifs).",
                'medicaments' => [
                    ['nom' => 'Oméprazole', 'famille' => 'Gastro-entérologie', 'posologie' => '20mg une fois par jour avant le repas', 'duree' => '4 à 8 semaines', 'type' => 'principal'],
                ],
            ],
            [
                'maladie' => 'Candidose cutanée',
                'titre' => 'Prise en charge Candidose cutanée',
                'signes' => 'Placards érythémateux suintants des plis, prurit, bordure desquamative',
                'diagnostics' => 'Diagnostic clinique, prélèvement mycologique si doute',
                'remarques' => "Traitement topique suffisant dans la grande majorité des cas. Garder les plis cutanés au sec.",
                'medicaments' => [
                    ['nom' => 'Clotrimazole', 'famille' => 'Antifongiques', 'posologie' => 'Application 2 fois/jour sur les lésions', 'duree' => '2 à 4 semaines', 'type' => 'principal'],
                ],
            ],
            [
                'maladie' => 'Teigne',
                'titre' => 'Prise en charge Teigne (Tinea capitis)',
                'signes' => 'Plaques d\'alopécie avec squames sur le cuir chevelu, parfois prurit',
                'diagnostics' => 'Examen en lumière de Wood, prélèvement mycologique',
                'remarques' => "Traitement systémique indispensable (le topique seul est insuffisant sur le cuir chevelu). Éviction scolaire et traitement de l'entourage/animaux si besoin.",
                'medicaments' => [
                    ['nom' => 'Griséofulvine', 'famille' => 'Antifongiques', 'posologie' => '20 mg/kg/jour en 1 prise', 'duree' => '6 à 8 semaines', 'type' => 'principal'],
                    ['nom' => 'Terbinafine', 'famille' => 'Antifongiques', 'posologie' => '<25kg: 125mg/j ; 25-35kg: 187,5mg/j', 'duree' => '4 semaines', 'type' => 'alternatif'],
                ],
            ],

            // ===== ALLERGOLOGIE / DERMATOLOGIE =====
            [
                'maladie' => 'Urticaire',
                'titre' => 'Prise en charge Urticaire',
                'signes' => 'Papules érythémateuses prurigineuses, fugaces et migratrices',
                'diagnostics' => 'Diagnostic clinique ; recherche de la cause si récidivant/chronique',
                'remarques' => "Éviction de l'allergène si identifié. Corticoïde oral bref réservé aux formes étendues/résistantes.",
                'medicaments' => [
                    ['nom' => 'Cétirizine', 'famille' => 'Antihistaminiques', 'description' => 'Antihistaminique de 2e génération, privilégié en 1ère ligne (meilleur profil de sécurité que les molécules de 1ère génération)', 'posologie' => '10mg une fois par jour (adulte)', 'duree' => '5 à 7 jours, jusqu\'à 6 semaines si chronique', 'type' => 'principal'],
                    ['nom' => 'Chlorphénamine', 'famille' => 'Antihistaminiques', 'posologie' => '0,1 mg/kg toutes les 6 à 8h (max 12mg/j)', 'duree' => '5 à 7 jours', 'type' => 'alternatif'],
                ],
            ],
            [
                'maladie' => 'Dermatite atopique',
                'titre' => 'Prise en charge Dermatite atopique (Eczéma atopique)',
                'signes' => 'Plaques érythémato-squameuses prurigineuses, xérose cutanée, localisation typique selon l\'âge',
                'diagnostics' => 'Diagnostic clinique',
                'remarques' => "Émollients quotidiens en traitement de fond, indispensables même hors poussée. Corticoïde topique uniquement lors des poussées, en couche mince.",
                'medicaments' => [
                    ['nom' => 'Hydrocortisone', 'famille' => 'Dermatologiques', 'posologie' => '1 à 2 applications par jour en couche mince', 'duree' => '1 à 2 semaines (poussée)', 'type' => 'principal'],
                ],
            ],
            [
                'maladie' => 'Brûlure cutanée',
                'titre' => 'Prise en charge Brûlure cutanée superficielle',
                'signes' => 'Douleur, érythème, phlyctènes selon le degré',
                'diagnostics' => 'Évaluation clinique du degré et de la surface (règle des 9)',
                'remarques' => "Refroidissement immédiat à l'eau tempérée (10-15 min). Référer si brûlure étendue (>10% surface corporelle), profonde, ou localisation à risque (visage, mains, organes génitaux).",
                'medicaments' => [
                    ['nom' => 'Sulfadiazine argentique 1% crème', 'famille' => 'Dermatologiques', 'posologie' => '1 application par jour sous pansement stérile', 'duree' => 'Jusqu\'à cicatrisation', 'type' => 'principal'],
                    ['nom' => 'Paracétamol', 'famille' => 'Antalgiques', 'posologie' => '15 mg/kg toutes les 6h', 'duree' => 'Selon douleur', 'type' => 'adjuvant'],
                ],
            ],

            // ===== ENVENIMATIONS / URGENCES =====
            [
                'maladie' => 'Morsure de serpent',
                'titre' => 'Prise en charge Morsure de serpent venimeux',
                'signes' => 'Douleur, œdème local extensif, troubles de la coagulation ou neurologiques selon l\'espèce',
                'diagnostics' => 'Diagnostic clinique (identification du serpent si possible), bilan de coagulation',
                'remarques' => "Immobiliser le membre, ne pas inciser/aspirer/garrotter. Transfert urgent vers une structure disposant de sérum antivenimeux adapté à la faune locale. Le sérum antivenimeux est le seul traitement efficace des envenimations.",
                'medicaments' => [
                    ['nom' => 'Sérum antivenimeux polyvalent', 'famille' => 'Divers', 'description' => 'À choisir selon l\'espèce locale et la disponibilité nationale', 'posologie' => 'Selon protocole national et gravité de l\'envenimation', 'duree' => 'Dose(s) unique(s), à réévaluer cliniquement', 'type' => 'principal'],
                    ['nom' => 'Antitoxine tétanique humaine', 'famille' => 'Divers', 'posologie' => '500 UI IM (rappel antitétanique)', 'duree' => 'Dose unique', 'type' => 'adjuvant'],
                ],
            ],
            [
                'maladie' => 'Piqûre de scorpion',
                'titre' => 'Prise en charge Piqûre de scorpion',
                'signes' => 'Douleur locale intense immédiate, parfois signes systémiques (sueurs, vomissements, troubles cardiovasculaires) selon l\'espèce',
                'diagnostics' => 'Diagnostic clinique',
                'remarques' => "Soins de support dans tous les cas. Sérum antiscorpionique réservé aux formes sévères, à administrer idéalement dans les 2-3 premières heures ; son efficacité et sa tolérance varient. Surveillance cardiovasculaire dans les formes sévères.",
                'medicaments' => [
                    ['nom' => 'Paracétamol', 'famille' => 'Antalgiques', 'posologie' => '15 mg/kg toutes les 6h pour la douleur', 'duree' => 'Selon besoin', 'type' => 'principal'],
                    ['nom' => 'Sérum antiscorpionique', 'famille' => 'Divers', 'posologie' => 'Selon protocole national (formes sévères uniquement, <2-3h après piqûre)', 'duree' => 'Dose unique', 'type' => 'alternatif'],
                ],
            ],
            [
                'maladie' => 'Coup de chaleur',
                'titre' => 'Prise en charge Coup de chaleur',
                'signes' => 'Hyperthermie sévère (>40°C), troubles de conscience, peau chaude et sèche',
                'diagnostics' => 'Diagnostic clinique, contexte d\'exposition à la chaleur',
                'remarques' => "Urgence vitale : refroidissement immédiat (eau tempérée, ventilation) et réhydratation. Pas de paracétamol (inefficace sur l'hyperthermie d'origine non infectieuse et risque hépatotoxique en cas de déshydratation).",
                'medicaments' => [
                    ['nom' => 'Ringer lactate ou NaCl 0,9%', 'famille' => 'Solutés', 'posologie' => 'Bolus de 10 à 20 ml/kg en 15-30 minutes, à réévaluer', 'duree' => 'Selon état hémodynamique', 'type' => 'principal'],
                ],
            ],

            // ===== NEUROLOGIE / PSYCHIATRIE =====
            [
                'maladie' => 'Migraine',
                'titre' => 'Prise en charge Migraine',
                'signes' => 'Céphalée pulsatile unilatérale, photophobie, phonophobie, nausées, parfois aura visuelle',
                'diagnostics' => 'Diagnostic clinique (critères IHS)',
                'remarques' => "Traiter précocement dès le début de la crise. Triptans réservés aux crises modérées à sévères ne répondant pas aux antalgiques simples.",
                'medicaments' => [
                    ['nom' => 'Ibuprofène', 'famille' => 'Anti-inflammatoires', 'posologie' => '400 à 600mg à la crise', 'duree' => 'À la demande', 'type' => 'principal'],
                    ['nom' => 'Sumatriptan', 'famille' => 'Divers', 'posologie' => '50 à 100mg par voie orale à la crise', 'duree' => 'À la demande (max 2 prises/24h)', 'type' => 'alternatif'],
                ],
            ],
            [
                'maladie' => 'Dépression',
                'titre' => 'Prise en charge Dépression (épisode dépressif caractérisé)',
                'signes' => 'Humeur triste persistante, anhédonie, troubles du sommeil/de l\'appétit, idées noires',
                'diagnostics' => 'Évaluation clinique (critères diagnostiques), échelles (PHQ-9)',
                'remarques' => "Toujours évaluer le risque suicidaire. Effet thérapeutique différé (2 à 4 semaines). Associer un suivi psychothérapeutique si possible. Ne jamais arrêter brutalement.",
                'medicaments' => [
                    ['nom' => 'Fluoxétine', 'famille' => 'Antidépresseurs', 'posologie' => '20mg une fois par jour le matin', 'duree' => 'Au moins 6 mois après rémission', 'type' => 'principal'],
                ],
            ],
            [
                'maladie' => 'Trouble anxieux',
                'titre' => 'Prise en charge Trouble anxieux généralisé',
                'signes' => 'Anxiété excessive et persistante, tension musculaire, troubles du sommeil, irritabilité',
                'diagnostics' => 'Évaluation clinique',
                'remarques' => "Les ISRS sont le traitement de fond de référence. Les benzodiazépines, si utilisées, doivent l'être sur une durée courte (risque de dépendance).",
                'medicaments' => [
                    ['nom' => 'Paroxétine', 'famille' => 'Antidépresseurs', 'posologie' => '20mg une fois par jour', 'duree' => 'Au moins 6 mois', 'type' => 'principal'],
                ],
            ],

            // ===== AUTRES =====
            [
                'maladie' => 'Accident vasculaire cérébral',
                'titre' => 'Prise en charge AVC ischémique (hors thrombolyse)',
                'signes' => 'Déficit neurologique brutal (hémiplégie, troubles du langage, asymétrie faciale)',
                'diagnostics' => 'Imagerie cérébrale en urgence (scanner/IRM) pour différencier ischémique/hémorragique',
                'remarques' => "URGENCE ABSOLUE : référer immédiatement vers une structure disposant d'imagerie, la thrombolyse (si <4h30) et la prise en charge spécialisée changent le pronostic. L'aspirine n'est indiquée qu'après exclusion formelle d'un AVC hémorragique par imagerie.",
                'medicaments' => [
                    ['nom' => 'Acide acétylsalicylique', 'famille' => 'Anti-inflammatoires', 'posologie' => '300mg dose de charge puis 75-100mg/jour (uniquement après exclusion d\'une hémorragie)', 'duree' => 'Traitement de fond prolongé', 'type' => 'principal'],
                ],
            ],
            [
                'maladie' => 'Entorse',
                'titre' => 'Prise en charge Entorse',
                'signes' => 'Douleur, œdème et impotence fonctionnelle après traumatisme articulaire',
                'diagnostics' => 'Examen clinique, radiographie pour éliminer une fracture associée',
                'remarques' => "Protocole RICE (Repos, Glace, Compression, Élévation) dans les 48 premières heures.",
                'medicaments' => [
                    ['nom' => 'Ibuprofène', 'famille' => 'Anti-inflammatoires', 'posologie' => '400mg 3 fois/jour', 'duree' => '3 à 5 jours', 'type' => 'principal'],
                ],
            ],
            [
                'maladie' => 'Fracture osseuse',
                'titre' => 'Prise en charge Fracture osseuse (analgésie et immobilisation)',
                'signes' => 'Douleur intense, déformation, impotence fonctionnelle après traumatisme',
                'diagnostics' => 'Radiographie standard',
                'remarques' => "Immobilisation et référence en orthopédie pour réduction/ostéosynthèse selon le type de fracture. L'antalgie ne remplace pas la prise en charge chirurgicale.",
                'medicaments' => [
                    ['nom' => 'Paracétamol', 'famille' => 'Antalgiques', 'posologie' => '1g toutes les 6h', 'duree' => 'Selon douleur', 'type' => 'principal'],
                    ['nom' => 'Morphine', 'famille' => 'Antalgiques', 'posologie' => '0,1 mg/kg (douleur intense, avant transfert)', 'duree' => 'Selon besoin', 'type' => 'alternatif'],
                ],
            ],
            [
                'maladie' => 'Arthrose',
                'titre' => 'Prise en charge Arthrose',
                'signes' => 'Douleur mécanique, raideur matinale brève, limitation fonctionnelle articulaire progressive',
                'diagnostics' => 'Radiographie standard, diagnostic clinique',
                'remarques' => "Prise en charge non médicamenteuse associée (activité physique adaptée, perte de poids). AINS à utiliser à la dose minimale efficace et sur la durée la plus courte possible.",
                'medicaments' => [
                    ['nom' => 'Paracétamol', 'famille' => 'Antalgiques', 'posologie' => '1g 3 fois/jour', 'duree' => 'Traitement de fond', 'type' => 'principal'],
                    ['nom' => 'Ibuprofène', 'famille' => 'Anti-inflammatoires', 'posologie' => '400mg 3 fois/jour si insuffisant', 'duree' => 'Cures courtes', 'type' => 'alternatif'],
                ],
            ],
            [
                'maladie' => 'Ostéoporose',
                'titre' => 'Prise en charge Ostéoporose',
                'signes' => 'Fractures à faible traumatisme, tassements vertébraux ; souvent asymptomatique avant fracture',
                'diagnostics' => 'Ostéodensitométrie (DMO)',
                'remarques' => "Supplémentation calcium/vitamine D systématique. Bisphosphonate indiqué si T-score bas ou antécédent de fracture ostéoporotique.",
                'medicaments' => [
                    ['nom' => 'Calcium + Vitamine D3', 'famille' => 'Vitamines', 'posologie' => '1000mg calcium + 800 UI vitamine D par jour', 'duree' => 'Traitement continu', 'type' => 'principal'],
                    ['nom' => 'Alendronate', 'famille' => 'Divers', 'posologie' => '70mg une fois par semaine, à jeun', 'duree' => '3 à 5 ans', 'type' => 'principal'],
                ],
            ],
            [
                'maladie' => 'Hémorroïdes',
                'titre' => 'Prise en charge Hémorroïdes',
                'signes' => 'Douleur anale, rectorragies, prurit, tuméfaction périanale',
                'diagnostics' => 'Examen proctologique',
                'remarques' => "Mesures hygiéno-diététiques (fibres, hydratation) en 1ère intention. Chirurgie si échec du traitement médical ou complication (thrombose, prolapsus).",
                'medicaments' => [
                    ['nom' => 'Topique anti-hémorroïdaire', 'famille' => 'Dermatologiques', 'posologie' => '1 à 2 applications par jour', 'duree' => '7 à 10 jours', 'type' => 'principal'],
                ],
            ],
            [
                'maladie' => 'Lithiase biliaire',
                'titre' => 'Prise en charge Lithiase biliaire (colique hépatique)',
                'signes' => 'Douleur de l\'hypochondre droit, souvent post-prandiale, parfois irradiation scapulaire',
                'diagnostics' => 'Échographie abdominale',
                'remarques' => "Traitement symptomatique de la crise ; la cholécystectomie est le traitement de fond en cas de lithiase symptomatique récidivante.",
                'medicaments' => [
                    ['nom' => 'Ibuprofène', 'famille' => 'Anti-inflammatoires', 'posologie' => '400mg à la crise', 'duree' => 'À la demande', 'type' => 'principal'],
                ],
            ],
            [
                'maladie' => 'Malnutrition chronique',
                'titre' => 'Prise en charge Malnutrition chronique (retard de croissance)',
                'signes' => 'Retard staturo-pondéral, retard de développement',
                'diagnostics' => 'Anthropométrie (taille/âge), courbes de croissance',
                'remarques' => "Prise en charge nutritionnelle prolongée associée à la supplémentation en micronutriments et à la prise en charge des causes sous-jacentes (infections répétées, insécurité alimentaire).",
                'medicaments' => [
                    ['nom' => 'Sulfate ferreux', 'famille' => 'Vitamines', 'posologie' => '3 à 6 mg/kg/jour de fer élément', 'duree' => '3 mois', 'type' => 'principal'],
                    ['nom' => 'Rétinol (Vitamine A)', 'famille' => 'Vitamines', 'posologie' => 'Selon âge, dose unique tous les 6 mois', 'duree' => 'Selon calendrier national', 'type' => 'adjuvant'],
                ],
            ],
            [
                'maladie' => 'Ictère néonatal',
                'titre' => 'Prise en charge Ictère néonatal',
                'signes' => 'Coloration jaune de la peau et des muqueuses apparaissant dans les premiers jours de vie',
                'diagnostics' => 'Bilirubinémie transcutanée puis sanguine, courbes selon âge en heures',
                'remarques' => "La photothérapie est le traitement de référence (non médicamenteux) selon le seuil de bilirubine. L'exsanguino-transfusion est réservée aux formes sévères. Rechercher une cause hémolytique (incompatibilité materno-foetale).",
                'medicaments' => [
                    ['nom' => 'Phytoménadione (Vitamine K1)', 'famille' => 'Vitamines', 'description' => 'Systématique à la naissance, sans lien direct avec l\'ictère mais fait partie des soins néonataux', 'posologie' => '1 mg IM à la naissance', 'duree' => 'Dose unique', 'type' => 'adjuvant'],
                ],
            ],
            [
                'maladie' => 'Prématurité',
                'titre' => 'Prise en charge du nouveau-né prématuré (soins de base)',
                'signes' => 'Naissance avant 37 semaines d\'aménorrhée, immaturité des fonctions vitales',
                'diagnostics' => 'Âge gestationnel, poids de naissance',
                'remarques' => "Prise en charge spécialisée en néonatologie (thermorégulation, alimentation, surveillance respiratoire). Vitamine K1 systématique. Se référer aux protocoles spécifiques déjà disponibles (détresse respiratoire, apnée du prématuré) selon les complications.",
                'medicaments' => [
                    ['nom' => 'Phytoménadione (Vitamine K1)', 'famille' => 'Vitamines', 'posologie' => '1 mg IM à la naissance', 'duree' => 'Dose unique', 'type' => 'principal'],
                ],
            ],
            [
                'maladie' => 'Fausse couche',
                'titre' => 'Prise en charge Fausse couche (avortement spontané incomplet)',
                'signes' => 'Métrorragies, douleurs pelviennes, expulsion partielle de produits de conception',
                'diagnostics' => 'Échographie pelvienne, dosage bêta-hCG',
                'remarques' => "Trois options possibles selon le contexte clinique : expectative, traitement médical (misoprostol) ou aspiration chirurgicale. Antibioprophylaxie si signes d'infection. Prévoir prophylaxie anti-D si patiente Rhésus négatif.",
                'medicaments' => [
                    ['nom' => 'Misoprostol', 'famille' => 'Divers', 'posologie' => '<14 SA : 600 µg par voie orale (ou 400 µg sublinguale) ; >=14 SA : 400 µg sublingual/vaginal toutes les 3h', 'duree' => 'Dose unique (<14 SA), à réévaluer à 48h', 'type' => 'principal'],
                ],
            ],
            [
                'maladie' => 'Fibrome utérin',
                'titre' => 'Prise en charge Fibrome utérin (forme symptomatique)',
                'signes' => 'Ménorragies, pesanteur pelvienne, parfois troubles de la fertilité',
                'diagnostics' => 'Échographie pelvienne',
                'remarques' => "Traitement médical pour les ménorragies en 1ère intention ; la chirurgie (myomectomie/hystérectomie) est indiquée selon la taille, la localisation et le désir de fertilité.",
                'medicaments' => [
                    ['nom' => 'Acide tranexamique', 'famille' => 'Divers', 'posologie' => '1g 3 à 4 fois/jour pendant les règles', 'duree' => 'Durant les menstruations', 'type' => 'principal'],
                ],
            ],
            [
                'maladie' => 'Insuffisance rénale chronique',
                'titre' => 'Prise en charge Insuffisance rénale chronique (stades précoces)',
                'signes' => 'Souvent asymptomatique au stade précoce ; oedèmes, hypertension, asthénie aux stades avancés',
                'diagnostics' => 'Créatininémie, débit de filtration glomérulaire estimé (DFG), protéinurie',
                'remarques' => "Ralentir la progression : contrôle tensionnel strict, protection rénale, éviction des néphrotoxiques. Référer en néphrologie dès le stade 3B ou en cas de complication. La prise en charge dépend fortement du stade (non résumable à un seul médicament).",
                'medicaments' => [
                    ['nom' => 'Énalapril', 'famille' => 'Divers', 'posologie' => '5 à 10mg une fois par jour (effet néphroprotecteur si protéinurie)', 'duree' => 'Traitement au long cours, sous surveillance biologique', 'type' => 'principal'],
                ],
            ],
        ];
    }
}
