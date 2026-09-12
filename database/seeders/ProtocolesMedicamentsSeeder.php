<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Complète par des liaisons médicament↔protocole réelles et structurées les
 * protocoles créés par WhoGuidelinesSeeder::enrichWithPdfGuidelines(), qui ne
 * générait qu'un texte descriptif (maladies.description) sans jamais peupler
 * la table pivot protocole_medicament. Corrige aussi le régime de la
 * tuberculose multirésistante (MDR-TB), resté sur un ancien schéma injectable
 * pré-2022 au lieu du régime BPaLM recommandé par l'OMS depuis 2022 (mis à
 * jour en juin 2024).
 *
 * Sources vérifiées (recherche effectuée en septembre 2026) : WHO consolidated
 * guidelines on tuberculosis, module 4 (BPaLM) ; WHO HEARTS technical package
 * (hypertension) ; WHO mhGAP intervention guide ; recommandations OMS
 * paludisme, VIH/PTME, hépatites B/C, nutrition, santé maternelle référencées
 * dans who_guidelines.tsv. Idempotent : peut être exécuté plusieurs fois sans
 * dupliquer les liaisons (updateOrInsert sur protocole_medicament).
 */
class ProtocolesMedicamentsSeeder extends Seeder
{
    private $now;

    public function run(): void
    {
        $this->now = Carbon::now();

        DB::transaction(function () {
            $this->fixMdrTb();
            $this->fillOrphanProtocols();
        });

        $this->command?->info('Médicaments structurés liés aux protocoles OMS restants (ProtocolesMedicamentsSeeder).');
    }

    private function familleId(string $nom): int
    {
        DB::table('familles')->updateOrInsert(
            ['nom' => $nom],
            ['uuid' => (string) Str::uuid(), 'updated_at' => $this->now]
        );

        return DB::table('familles')->where('nom', $nom)->value('id');
    }

    private function getOrCreateMedicament(string $nom, string $familleNom, ?string $description = null): int
    {
        $row = DB::table('medicaments')->where('nom', $nom)->first();
        if ($row) {
            return $row->id;
        }

        $medId = DB::table('medicaments')->insertGetId([
            'nom' => $nom,
            'description' => $description,
            'stock' => 50,
            'stock_min' => 5,
            'famille_id' => $this->familleId($familleNom),
            'uuid' => (string) Str::uuid(),
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ]);

        DB::table('unites')->insert([
            'nom' => 'Unité',
            'symbole' => 'U',
            'prix_achat' => 500,
            'prix_vente' => 1000,
            'facteur' => 1.0,
            'is_default' => true,
            'medicament_id' => $medId,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ]);

        return $medId;
    }

    private function link(int $protocoleId, ?int $medicamentId, string $type, string $posologie, ?string $duree = null): void
    {
        if (! $medicamentId) {
            return;
        }

        DB::table('protocole_medicament')->updateOrInsert(
            ['protocole_id' => $protocoleId, 'medicament_id' => $medicamentId],
            [
                'uuid' => (string) Str::uuid(),
                'type' => $type,
                'posologie' => $posologie,
                'duree' => $duree,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]
        );
    }

    private function protocoleIdFor(string $maladieNom): ?int
    {
        $maladieId = DB::table('maladies')->where('nom', $maladieNom)->value('id');
        if (! $maladieId) {
            return null;
        }

        return DB::table('protocole_traitements')->where('maladie_id', $maladieId)->value('id');
    }

    private function medicamentId(string $nom): ?int
    {
        return DB::table('medicaments')->where('nom', $nom)->value('id');
    }

    private function updateMaladieDescription(string $maladieNom, string $nouvelleDescription): void
    {
        DB::table('maladies')->where('nom', $maladieNom)->update([
            'description' => $nouvelleDescription,
            'updated_at' => $this->now,
        ]);
    }

    /**
     * Remplace l'ancien régime MDR-TB (Amikacine, Ofloxacine, Éthionamide,
     * Cyclosérine — abandonné par l'OMS) par le régime BPaLM 100% oral de
     * 6 mois recommandé depuis 2022 (mise à jour juin 2024).
     */
    private function fixMdrTb(): void
    {
        $protoId = $this->protocoleIdFor('Tuberculose multirésistante (MDR-TB)');
        if (! $protoId) {
            return;
        }

        DB::table('protocole_medicament')->where('protocole_id', $protoId)->delete();

        $bedaquiline = $this->getOrCreateMedicament('Bédaquiline (oral)', 'Antituberculeux', 'Diarylquinoline, composant du régime BPaLM (OMS 2022/2024) pour la tuberculose multirésistante.');
        $pretomanid = $this->getOrCreateMedicament('Prétomanid (oral)', 'Antituberculeux', 'Nitroimidazooxazine, composant du régime BPaLM (OMS 2022/2024).');
        $linezolide = $this->getOrCreateMedicament('Linézolide (oral)', 'Antituberculeux', 'Oxazolidinone, composant du régime BPaLM (OMS 2022/2024).');
        $moxifloxacine = $this->getOrCreateMedicament('Moxifloxacine (oral)', 'Fluoroquinolones', 'Fluoroquinolone respiratoire, composant du régime BPaLM (OMS 2022/2024).');

        $this->link($protoId, $bedaquiline, 'principal', '400 mg/jour pendant 2 semaines, puis 200 mg 3×/semaine', '6 mois (26 semaines)');
        $this->link($protoId, $pretomanid, 'principal', '200 mg 1×/jour', '6 mois (26 semaines)');
        $this->link($protoId, $linezolide, 'principal', '600 mg 1×/jour (réduction à 300 mg possible selon toxicité)', '6 mois (26 semaines)');
        $this->link($protoId, $moxifloxacine, 'principal', '400 mg 1×/jour', '6 mois (26 semaines)');

        $this->updateMaladieDescription(
            'Tuberculose multirésistante (MDR-TB)',
            "Recommandation OMS 2024 : régime BPaLM (Bédaquiline + Prétomanide + Linézolide + Moxifloxacine), 100% oral, 6 mois, pour les adultes et adolescents ≥14 ans atteints de TB-MR/RR sans résistance étendue aux fluoroquinolones. Remplace les anciens régimes injectables plus longs (9-20 mois)."
        );
    }

    /**
     * Les 41 protocoles générés par enrichWithPdfGuidelines() sans aucun
     * médicament lié. La quasi-totalité des molécules nécessaires existent
     * déjà au catalogue (créées lors du seed d'autres maladies) : elles sont
     * réutilisées par leur nom ; seules les molécules réellement absentes
     * sont créées.
     */
    private function fillOrphanProtocols(): void
    {
        if ($pid = $this->protocoleIdFor('Paludisme grave')) {
            $artesunate = $this->getOrCreateMedicament('Artésunate (IV)', 'Antipaludiques');
            $arthemeterIM = $this->getOrCreateMedicament('Artéméther (IM)', 'Antipaludiques', 'Voie IM, alternative à l\'artésunate IV si celui-ci est indisponible.');
            $quinine = $this->getOrCreateMedicament('Quinine (perfusion)', 'Antipaludiques');
            $this->link($pid, $artesunate, 'principal', '2,4 mg/kg IV à H0, H12, H24 puis 1×/jour', 'Minimum 24h IV, puis relais oral par ACT pour compléter 3 jours au total');
            $this->link($pid, $arthemeterIM, 'alternatif', '3,2 mg/kg IM à H0 puis 1,6 mg/kg/jour', 'Jusqu\'à relais oral possible');
            $this->link($pid, $quinine, 'alternatif', '20 mg/kg (dose de charge) puis 10 mg/kg toutes les 8h, perfusion lente (≥4h)', 'Jusqu\'à relais oral, max 7 jours');
        }

        if ($pid = $this->protocoleIdFor('Paludisme chez la femme enceinte')) {
            $quinine = $this->getOrCreateMedicament('Quinine (perfusion)', 'Antipaludiques');
            $clindamycine = $this->getOrCreateMedicament('Clindamycine (oral)', 'Lincosamides');
            $al = $this->getOrCreateMedicament('Artéméther + Luméfantrine (AL) (oral)', 'ACT');
            $this->link($pid, $quinine, 'principal', '10 mg/kg (sel) toutes les 8h', '7 jours — réservé au 1er trimestre');
            $this->link($pid, $clindamycine, 'principal', '20 mg/kg/jour en 2 prises', '7 jours — associé à la quinine au 1er trimestre');
            $this->link($pid, $al, 'principal', 'Selon poids, 6 prises réparties sur 3 jours', '3 jours — indiqué aux 2e et 3e trimestres');
        }

        if ($pid = $this->protocoleIdFor("Paludisme saisonnier chez l'enfant (SMC)")) {
            $spaq = $this->getOrCreateMedicament('Sulfadoxine-Pyriméthamine + Amodiaquine (SP+AQ)', 'Antipaludiques', 'Chimioprévention du paludisme saisonnier (CPS), OMS.');
            $this->link($pid, $spaq, 'principal', 'Dose unique mensuelle selon l\'âge (comprimés dispersibles)', '4 cycles pendant la saison de transmission (3-4 mois)');
        }

        if ($pid = $this->protocoleIdFor('Tuberculose pulmonaire (1ère ligne)')) {
            $rhze = $this->medicamentId('Isoniazide + Rifampicine + Pyrazinamide + Éthambutol (oral)');
            $rh = $this->getOrCreateMedicament('Isoniazide + Rifampicine (oral)', 'Antituberculeux', 'Phase de continuation du traitement antituberculeux de 1ère ligne.');
            $this->link($pid, $rhze, 'principal', 'Selon poids (comprimés combinés)', 'Phase intensive : 2 mois');
            $this->link($pid, $rh, 'principal', 'Selon poids (comprimés combinés)', 'Phase de continuation : 4 mois');
        }

        if ($pid = $this->protocoleIdFor('VIH adulte 1ère ligne (TLD)')) {
            $this->link($pid, $this->medicamentId('Ténofovir + Lamivudine + Dolutégravir (TLD)'), 'principal', '1 comprimé/jour', 'À vie');
        }

        if ($pid = $this->protocoleIdFor('VIH adulte 2ème ligne')) {
            $drv = $this->getOrCreateMedicament('Darunavir + Ritonavir (DRV/r) (oral)', 'Inhibiteurs de protéase VIH');
            $this->link($pid, $drv, 'principal', '800 mg/100 mg 1×/jour, associé à 2 INTI actifs choisis selon test de résistance', 'À vie, après échec confirmé de la 1ère ligne');
        }

        if ($pid = $this->protocoleIdFor('VIH pédiatrique')) {
            $this->link($pid, $this->medicamentId('Abacavir + Lamivudine + Dolutégravir (nourrisson)'), 'principal', 'Selon poids (enfant > 1 mois et > 3 kg)', 'À vie');
        }

        if ($pid = $this->protocoleIdFor('PTME / Prevention of mother-to-child transmission')) {
            $nvpSirop = $this->getOrCreateMedicament('Névirapine (sirop nourrisson)', 'INNTI VIH', 'Prophylaxie post-natale du nouveau-né exposé au VIH.');
            $this->link($pid, $this->medicamentId('Ténofovir + Lamivudine + Dolutégravir (TLD)'), 'principal', '1 comprimé/jour, dès le diagnostic', 'À vie');
            $this->link($pid, $nvpSirop, 'principal', '10-15 mg 1×/jour (selon poids)', '6 semaines chez le nourrisson allaité');
        }

        if ($pid = $this->protocoleIdFor('Prophylaxie post-exposition VIH')) {
            $this->link($pid, $this->medicamentId('Ténofovir + Lamivudine + Dolutégravir (TLD)'), 'principal', '1 comprimé/jour', '28 jours — débuté au plus tôt, idéalement < 72h après exposition');
        }

        if ($pid = $this->protocoleIdFor('Hépatite B chronique')) {
            $tdf = $this->getOrCreateMedicament('Ténofovir disoproxil fumarate (TDF) (oral)', 'Antiviraux');
            $entecavir = $this->getOrCreateMedicament('Entécavir (oral)', 'Antiviraux');
            $this->link($pid, $tdf, 'principal', '300 mg 1×/jour', 'Traitement à vie (ou jusqu\'à séroconversion HBs selon critères OMS)');
            $this->link($pid, $entecavir, 'alternatif', '0,5 mg 1×/jour', 'À vie');
            $this->updateMaladieDescription('Hépatite B chronique', "Recommandation OMS : Ténofovir (TDF) 300 mg/jour ou Entécavir 0,5 mg/jour. Traitement à vie. Dépistage carcinome hépatocellulaire (échographie/AFP semestrielle chez les patients à risque).");
        }

        if ($pid = $this->protocoleIdFor('Hépatite C chronique')) {
            $sofvel = $this->getOrCreateMedicament('Sofosbuvir/Velpatasvir (SOF/VEL)', 'Antiviraux', 'Antiviral d\'action directe pangénotypique.');
            $this->link($pid, $sofvel, 'principal', '400 mg/100 mg 1×/jour', '12 semaines');
        }

        if ($pid = $this->protocoleIdFor('Pneumonie infantile (IMCI)')) {
            $this->link($pid, $this->medicamentId('Amoxicilline (oral)'), 'principal', '40 mg/kg 2×/jour', '5 jours');
        }

        if ($pid = $this->protocoleIdFor("Diarrhée aiguë de l'enfant")) {
            $sro = DB::table('medicaments')->where('nom', 'like', 'SRO%')->orWhere('nom', 'like', '%Sels de réhydratation%')->value('id');
            $zinc = $this->medicamentId('Sulfate de zinc (oral)');
            $this->link($pid, $sro, 'principal', 'Selon poids, après chaque selle liquide', "Jusqu'à résolution de la diarrhée");
            $this->link($pid, $zinc, 'principal', '20 mg/jour (10 mg si < 6 mois)', '10 à 14 jours');
        }

        if ($pid = $this->protocoleIdFor('Diarrhée sanglante / dysenterie')) {
            $this->link($pid, $this->medicamentId('Ciprofloxacine (oral)'), 'principal', '15 mg/kg 2×/jour', '3 jours (shigellose)');
            $this->link($pid, $this->medicamentId('Sulfate de zinc (oral)'), 'principal', '20 mg/jour (10 mg si < 6 mois)', '10 à 14 jours');
        }

        if ($pid = $this->protocoleIdFor('Grippe saisonnière')) {
            $this->link($pid, $this->medicamentId('Oseltamivir (oral)'), 'principal', '75 mg 2×/jour', '5 jours — si initié < 48h après le début des symptômes, chez les groupes à risque');
        }

        if ($pid = $this->protocoleIdFor('Méningite bactérienne (adultes)')) {
            $dexa = DB::table('medicaments')->where('nom', 'like', 'Dexaméthasone%')->value('id');
            $this->link($pid, $this->medicamentId('Ceftriaxone (IV)'), 'principal', '2 g 2×/jour IV', '7 à 14 jours selon le germe');
            $this->link($pid, $dexa, 'principal', '0,15 mg/kg toutes les 6h, débutée avant ou avec la 1ère dose d\'antibiotique', '4 jours');
        }

        if ($pid = $this->protocoleIdFor('Infections sexuellement transmissibles')) {
            $this->link($pid, $this->medicamentId('Benzathine benzylpénicilline (IM)'), 'principal', '2,4 millions UI IM dose unique', 'Syphilis précoce (dose unique)');
            $this->link($pid, $this->medicamentId('Ceftriaxone (IV)'), 'principal', '500 mg IM dose unique', 'Gonorrhée non compliquée');
            $this->link($pid, $this->medicamentId('Azithromycine (oral)'), 'principal', '1 g par voie orale, dose unique', 'Associé à la ceftriaxone (gonorrhée) ou en traitement de la chlamydiose');
        }

        if ($pid = $this->protocoleIdFor('Infections urinaires (IU)')) {
            $fosfo = $this->getOrCreateMedicament('Fosfomycine trométamol (oral)', 'Fosfomycine');
            $this->link($pid, $this->medicamentId('Nitrofurantoïne (oral)'), 'principal', '100 mg 2×/jour', '5 jours — cystite non compliquée');
            $this->link($pid, $fosfo, 'alternatif', '3 g, dose unique', 'Cystite non compliquée');
            $this->link($pid, $this->medicamentId('Ciprofloxacine (oral)'), 'principal', '500 mg 2×/jour', '7 jours — pyélonéphrite');
        }

        if ($pid = $this->protocoleIdFor('Leishmaniose viscérale')) {
            $this->link($pid, $this->medicamentId('Amphotéricine B liposomale (IV)'), 'principal', '10 mg/kg à J1, J2, J3, J7, J14, J21 (schéma "SHORT")', 'Dose totale cumulée 30 mg/kg');
        }

        if ($pid = $this->protocoleIdFor('Schistosomiase (bilharziose)')) {
            $this->link($pid, $this->medicamentId('Praziquantel (oral)'), 'principal', '40 à 60 mg/kg (dose unique ou répartie en 2 prises)', 'Dose unique — répéter selon programme MDA');
        }

        if ($pid = $this->protocoleIdFor('Onchocercose (cécité des rivières)')) {
            $this->link($pid, $this->medicamentId('Ivermectine (oral)'), 'principal', '150 µg/kg', '1 à 2 fois par an, à vie ou jusqu\'à élimination de la transmission');
        }

        if ($pid = $this->protocoleIdFor('Gale')) {
            $this->link($pid, $this->medicamentId('Perméthrine 5% crème'), 'principal', 'Application unique sur tout le corps, à laisser 8-14h', 'À répéter à J7-14');
            $this->link($pid, $this->medicamentId('Ivermectine (oral)'), 'alternatif', '200 µg/kg', 'À J1 et J8, ou traitement de masse si prévalence > 10%');
        }

        if ($pid = $this->protocoleIdFor('Diabète de type 2')) {
            $this->link($pid, $this->medicamentId('Metformine (oral)'), 'principal', '500 mg 1×/jour, titration progressive jusqu\'à 1 g 2×/jour', 'Traitement continu ; insuline si échec des antidiabétiques oraux');
        }

        if ($pid = $this->protocoleIdFor('Diabète gestationnel')) {
            $insuline = $this->getOrCreateMedicament('Insuline humaine (SC)', 'Insulines', 'Utilisée si les mesures hygiéno-diététiques ne suffisent pas à contrôler la glycémie chez la femme enceinte (metformine évitée par principe de précaution par l\'OMS en 1ère intention).');
            $this->link($pid, $insuline, 'principal', 'Selon profil glycémique (schéma basal-bolus)', 'Jusqu\'à l\'accouchement, réévaluation post-partum');
            $this->updateMaladieDescription('Diabète gestationnel', "Recommandation OMS : mesures hygiéno-diététiques en 1ère intention. Insuline si glycémie non contrôlée. Dépistage par HGPO 75g entre 24 et 28 semaines d'aménorrhée.");
        }

        if ($pid = $this->protocoleIdFor('Hypertension (adultes)')) {
            $amlodipine = $this->getOrCreateMedicament('Amlodipine (oral)', 'Inhibiteurs calciques');
            $hctz = $this->getOrCreateMedicament('Hydrochlorothiazide (oral)', 'Diurétiques');
            $this->link($pid, $amlodipine, 'principal', '5 mg 1×/jour, titrable à 10 mg', 'Continu');
            $this->link($pid, $hctz, 'alternatif', '12,5 à 25 mg 1×/jour', 'Continu');
            $this->updateMaladieDescription('Hypertension (adultes)', "Recommandation OMS (programme HEARTS) 2023 : traitement si PA ≥ 140/90. Objectif < 130/80 si risque cardiovasculaire élevé. Classes recommandées en 1ère intention : inhibiteur calcique, diurétique thiazidique ou IEC/ARA2. Monothérapie possible si PA < 160/100.");
        }

        if ($pid = $this->protocoleIdFor('Asthme')) {
            $this->link($pid, $this->medicamentId('Budésonide (inhalation)'), 'principal', '200 à 400 µg 2×/jour (traitement de fond)', 'Continu, ajusté selon le contrôle (step-up/down)');
            $this->link($pid, $this->medicamentId('Salbutamol (aérosol)'), 'principal', '100 à 200 µg à la demande', 'Traitement de secours');
        }

        if ($pid = $this->protocoleIdFor('Malnutrition aiguë sévère (MAS)')) {
            $this->link($pid, $this->medicamentId('Amoxicilline (oral)'), 'principal', '40 mg/kg 2×/jour', '5 jours (antibiothérapie systématique OMS)');
            $this->updateMaladieDescription('Malnutrition aiguë sévère (MAS)', "Recommandation OMS : aliments thérapeutiques prêts à l'emploi (ATPE) pour la renutrition. Antibiothérapie systématique (amoxicilline 5 jours). Renutrition progressive avec surveillance du syndrome de renutrition inappropriée.");
        }

        if ($pid = $this->protocoleIdFor('Carence en vitamine A')) {
            $vitA = DB::table('medicaments')->where('nom', 'like', 'Rétinol%')->value('id');
            $this->link($pid, $vitA, 'principal', '200 000 UI dose unique (100 000 UI si 6-11 mois)', 'À répéter tous les 6 mois en zone d\'endémie');
        }

        if ($pid = $this->protocoleIdFor('Pré-éclampsie / Éclampsie')) {
            $mgso4 = $this->getOrCreateMedicament('Sulfate de magnésium (IV/IM)', 'Solutés', 'Prévention et traitement des convulsions éclamptiques (protocole de Pritchard/Zuspan).');
            $labetalol = $this->getOrCreateMedicament('Labétalol (IV/oral)', 'Bêta-bloquants');
            $methyldopa = $this->getOrCreateMedicament('Méthyldopa (oral)', 'Antihypertenseurs centraux');
            $this->link($pid, $mgso4, 'principal', 'Dose de charge 4g IV + 10g IM (5g/fesse), puis 5g IM toutes les 4h (ou 1g/h IV en entretien)', '24h après l\'accouchement ou la dernière convulsion');
            $this->link($pid, $labetalol, 'principal', '20 mg IV, doses croissantes selon la PA (max 300mg/jour)', 'Jusqu\'à stabilisation de la PA');
            $this->link($pid, $methyldopa, 'alternatif', '250 mg 2 à 3×/jour', 'Continu jusqu\'à l\'accouchement');
        }

        if ($pid = $this->protocoleIdFor('Hémorragie du post-partum')) {
            $oxytocine = $this->getOrCreateMedicament('Oxytocine (IM/IV)', 'Solutés', 'Utérotonique de 1ère intention pour la prévention et le traitement de l\'hémorragie du post-partum.');
            $this->link($pid, $oxytocine, 'principal', '10 UI IM ou IV lente', 'Dose unique, répétable');
            $this->link($pid, $this->medicamentId('Misoprostol'), 'alternatif', '800 µg par voie sublinguale', 'Dose unique — si oxytocine indisponible');
        }

        if ($pid = $this->protocoleIdFor('Infection du post-partum')) {
            $this->link($pid, $this->medicamentId('Ampicilline + Gentamicine (IV)'), 'principal', 'Ampicilline 2g IV toutes les 6h + Gentamicine 5mg/kg IV 1×/jour', '7 à 10 jours');
            $this->link($pid, $this->medicamentId('Métronidazole (oral/IV)'), 'principal', '500 mg IV/oral 3×/jour', '7 à 10 jours');
        }

        if ($pid = $this->protocoleIdFor('Avortement incomplet')) {
            $this->link($pid, $this->medicamentId('Misoprostol'), 'principal', '600 µg par voie orale (ou 400 µg par voie sublinguale)', 'Dose unique — réévaluation à J7-14 ; alternative : aspiration manuelle intra-utérine (AMIU)');
        }

        if ($pid = $this->protocoleIdFor('Syndrome néphrotique (enfant)')) {
            $this->link($pid, $this->medicamentId('Prednisolone (oral)'), 'principal', '60 mg/m²/jour (max 60 mg/j)', '4 à 6 semaines, puis dégression progressive (60mg/m² un jour sur deux x4-6 sem.)');
        }

        if ($pid = $this->protocoleIdFor('Méningite virale')) {
            $this->link($pid, $this->medicamentId('Aciclovir (oral)'), 'alternatif', '10 mg/kg IV toutes les 8h', '14 à 21 jours — uniquement si méningo-encéphalite herpétique (HSV) suspectée ou confirmée');
        }

        if ($pid = $this->protocoleIdFor('Cancers courants (soins palliatifs)')) {
            $this->link($pid, $this->medicamentId('Morphine (oral/injection)'), 'principal', 'Titration individuelle selon l\'échelle de la douleur OMS (dose initiale orale 5-10 mg toutes les 4h)', 'Continu, ajustement régulier');
        }

        if ($pid = $this->protocoleIdFor('Troubles mentaux courants')) {
            $halo = $this->getOrCreateMedicament('Halopéridol (oral)', 'Divers', 'Antipsychotique de 1ère génération, mhGAP OMS.');
            $chlorpro = $this->getOrCreateMedicament('Chlorpromazine (oral)', 'Divers', 'Antipsychotique de 1ère génération, mhGAP OMS.');
            $this->link($pid, $this->medicamentId('Amitriptyline (oral)'), 'principal', '25 à 150 mg/jour, débutée à faible dose', 'Dépression — au moins 6 mois après rémission');
            $this->link($pid, $this->medicamentId('Fluoxétine'), 'alternatif', '20 mg/jour', 'Dépression — alternative à l\'amitriptyline (meilleure tolérance)');
            $this->link($pid, $halo, 'principal', '1,5 à 5 mg/jour, débutée à faible dose', 'Troubles psychotiques — ajustement selon réponse et tolérance');
            $this->link($pid, $chlorpro, 'alternatif', '75 à 300 mg/jour', 'Troubles psychotiques — alternative à l\'halopéridol');
            $this->updateMaladieDescription('Troubles mentaux courants', "Recommandation OMS (mhGAP) : Dépression : amitriptyline ou fluoxétine. Troubles psychotiques : halopéridol ou chlorpromazine. Prise en charge psychosociale associée systématiquement.");
        }

        if ($pid = $this->protocoleIdFor('Intoxication aux opiacés')) {
            $this->link($pid, $this->medicamentId('Naloxone (IV)'), 'principal', '0,4 mg IV, répétable toutes les 2-3 min jusqu\'à 2 mg au total', 'Surveillance 24h (durée d\'action plus courte que la plupart des opiacés)');
        }

        if ($pid = $this->protocoleIdFor('Rage (post-exposition)')) {
            $vaccin = $this->getOrCreateMedicament('Vaccin antirabique (schéma Essen)', 'Antiviraux', 'Prophylaxie post-exposition rabique, schéma OMS Essen (IM, 1 dose/site).');
            $rig = $this->getOrCreateMedicament('Immunoglobulines antirabiques humaines', 'Antiviraux', 'Indiquées en catégorie III d\'exposition (morsure/griffure ayant traversé la peau), infiltrées autour de la plaie.');
            $this->link($pid, $vaccin, 'principal', '1 dose IM à J0, J3, J7, J14, J28', 'Schéma Essen (5 doses)');
            $this->link($pid, $rig, 'principal', '20 UI/kg, infiltrée autour de la plaie', 'Dose unique — uniquement catégorie III d\'exposition');
        }

        if ($pid = $this->protocoleIdFor('Tétanos')) {
            $this->link($pid, $this->medicamentId('Antitoxine tétanique humaine'), 'principal', '500 UI IM', 'Dose unique');
            $this->link($pid, $this->medicamentId('Métronidazole (oral/IV)'), 'principal', '500 mg IV/oral 3×/jour', '7 à 10 jours, associé au parage chirurgical de la plaie');
        }

        if ($pid = $this->protocoleIdFor('Charbon (anthrax cutané)')) {
            $this->link($pid, $this->medicamentId('Ciprofloxacine (oral)'), 'principal', '500 mg 2×/jour', '7 à 10 jours (forme cutanée non compliquée acquise naturellement ; 60 jours si exposition par inhalation à des spores)');
        }

        // Bronchiolite (nourrisson) : l'OMS ne recommande aucun médicament de
        // 1ère intention (soins de support seuls) — la description est
        // clarifiée mais volontairement laissée sans médicament lié.
        $this->updateMaladieDescription('Bronchiolite (nourrisson)', "Recommandation OMS : soins de support (hydratation, désobstruction nasale, oxygénothérapie si besoin). Aucun médicament recommandé en 1ère intention : ni bronchodilatateurs, ni corticoïdes, ni antibiotiques (sauf surinfection bactérienne confirmée).");
    }
}
