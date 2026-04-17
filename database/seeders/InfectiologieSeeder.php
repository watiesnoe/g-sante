<?php

namespace Database\Seeders;

use App\Models\Maladie;
use App\Models\ProtocoleTraitement;
use App\Models\Medicament;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InfectiologieSeeder extends Seeder
{
    /**
     * Données spécialisées infectiologie avec protocoles étendus
     * (méningites, septicémies, tuberculose, etc.)
     * Se base sur les données créées par PathologiesSeeder et ProtocoleTraitementsSeeder.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Médicaments supplémentaires spécifiques à l'infectiologie
        $medsInfectio = [
            ['nom' => 'Chloramphénicol 1g Injectable', 'famille' => 'Antibiotiques', 'unite' => 'ampoule', 'p_v' => 2500],
            ['nom' => 'Rifampicine 600mg', 'famille' => 'Antibiotiques', 'unite' => 'comprimé', 'p_v' => 3500],
            ['nom' => 'Isoniazide 300mg', 'famille' => 'Antibiotiques', 'unite' => 'comprimé', 'p_v' => 1500],
            ['nom' => 'Pyrazinamide 500mg', 'famille' => 'Antibiotiques', 'unite' => 'comprimé', 'p_v' => 2000],
            ['nom' => 'Ethambutol 400mg', 'famille' => 'Antibiotiques', 'unite' => 'comprimé', 'p_v' => 2000],
            ['nom' => 'Fluconazole 150mg', 'famille' => 'Antifongiques', 'unite' => 'capsule', 'p_v' => 4000],
            ['nom' => 'Ampicilline 1g Injectable', 'famille' => 'Antibiotiques', 'unite' => 'ampoule', 'p_v' => 3000],
            ['nom' => 'Gentamicine 80mg Injectable', 'famille' => 'Antibiotiques', 'unite' => 'ampoule', 'p_v' => 2000],
            ['nom' => 'Dexaméthasone 8mg Injectable', 'famille' => 'Corticostéroïdes', 'unite' => 'ampoule', 'p_v' => 2500],
        ];

        $unites  = DB::table('unites')->pluck('id', 'nom');
        $familles = DB::table('familles')->pluck('id', 'nom');

        foreach ($medsInfectio as $m) {
            DB::table('medicaments')->updateOrInsert(
                ['nom' => $m['nom']],
                [
                    'famille_id'  => $familles[$m['famille']] ?? 1,
                    'unite_id'    => $unites[$m['unite']] ?? 1,
                    'prix_achat'  => $m['p_v'] * 0.6,
                    'prix_vente'  => $m['p_v'],
                    'stock'       => 100,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]
            );
        }

        // Rafraîchir la liste des médicaments
        $mId = DB::table('medicaments')->pluck('id', 'nom');

        // --- PROTOCOLES INFECTIOLOGIE AVANCÉE ---
        $protocoles = [
            [
                'maladie' => 'Méningites',
                'protocole' => [
                    'titre'     => 'Protocole Méningites Bactériennes',
                    'signes'    => 'Fièvre, céphalées intenses, photophobie, raideur de la nuque, purpura, altération de conscience',
                    'diagnostics' => 'Ponction lombaire: LCR purulent, cellularité > 1000 éléments/mm³, glycorachie basse. Hémocultures, PCR méningocoque/pneumocoque.',
                    'germes_nourrisson' => 'E. coli K1, Streptocoque B, Listeria monocytogenes, H. influenzae',
                    'germes_adulte'     => 'Streptococcus pneumoniae, Neisseria meningitidis, Listeria (sujet âgé/immunodéprimé)',
                    'remarques'         => "URGENCE ABSOLUE. Ne pas retarder l'antibiothérapie. Si purpura fulminans: Amoxicilline IV ou C3G immédiate avant tout examen. Dexaméthasone adjuvante réduit les séquelles.",
                    'traitements' => [
                        ['nom' => 'Ceftriaxone 1g Injectable', 'type' => 'principal', 'poso' => '100 mg/kg/j (max 4g) IV en 2 injections', 'duree' => '10 jours'],
                        ['nom' => 'Ampicilline 1g Injectable', 'type' => 'alternatif', 'poso' => '200 mg/kg/j IV en 4-6 injections', 'duree' => '14 jours'],
                        ['nom' => 'Dexaméthasone 8mg Injectable', 'type' => 'adjuvant', 'poso' => '0.15 mg/kg IV/6h (30 min avant l\'antibiothérapie)', 'duree' => '4 jours'],
                    ],
                ],
            ],
            [
                'maladie' => 'Tuberculose pulmonaire',
                'protocole' => [
                    'titre'     => 'Protocole Tuberculose Pulmonaire (2RHZE/4RH)',
                    'signes'    => 'Toux > 3 semaines, hémoptysies, sudations nocturnes, amaigrissement, fièvre vespérale',
                    'diagnostics' => 'IDR, Radiographie thoracique (infiltrats apicaux, cavernes), Crachat BK × 3, GeneXpert MTB/RIF',
                    'germes_nourrisson' => 'Mycobacterium tuberculosis (formes miliaires fréquentes)',
                    'germes_adulte'     => 'Mycobacterium tuberculosis, M. bovis',
                    'remarques'         => "Phase intensive 2 mois (RHZE) puis phase de continuation 4 mois (RH). Surveillance mensuelle de la tolérance. Déclaration obligatoire.",
                    'traitements' => [
                        ['nom' => 'Rifampicine 600mg', 'type' => 'principal', 'poso' => '10 mg/kg/j (max 600mg) en 1 prise à jeun', 'duree' => '6 mois'],
                        ['nom' => 'Isoniazide 300mg', 'type' => 'principal', 'poso' => '5 mg/kg/j (max 300mg) en 1 prise', 'duree' => '6 mois'],
                        ['nom' => 'Pyrazinamide 500mg', 'type' => 'principal', 'poso' => '25 mg/kg/j en 1 prise', 'duree' => '2 mois (phase intensive)'],
                        ['nom' => 'Ethambutol 400mg', 'type' => 'principal', 'poso' => '15-20 mg/kg/j en 1 prise', 'duree' => '2 mois (phase intensive)'],
                    ],
                ],
            ],
            [
                'maladie' => 'Candidose systémique',
                'protocole' => [
                    'titre'     => 'Traitement Candidose Systémique',
                    'signes'    => 'Fièvre persistante non expliquée, lésions muqueuses (muguet), infections récidivantes',
                    'diagnostics' => 'Hémocultures (Candida), Sérologie βD-glucane, Fond d\'œil',
                    'germes_nourrisson' => 'Candida albicans, Candida parapsilosis',
                    'germes_adulte'     => 'Candida albicans, Candida glabrata, Candida tropicalis',
                    'remarques'         => "Retrait des cathéters si candidémie confirmée. Durée minimale 14 jours après la dernière hémoculture négative.",
                    'traitements' => [
                        ['nom' => 'Fluconazole 150mg', 'type' => 'principal', 'poso' => '400mg dose de charge J1, puis 200-400mg/j', 'duree' => '14-21 jours'],
                    ],
                ],
            ],
        ];

        foreach ($protocoles as $p) {
            // Maladie
            DB::table('maladies')->updateOrInsert(
                ['nom' => $p['maladie']],
                ['created_at' => $now, 'updated_at' => $now]
            );
            $maladieId = DB::table('maladies')->where('nom', $p['maladie'])->value('id');

            // Protocole
            DB::table('protocole_traitements')->updateOrInsert(
                ['maladie_id' => $maladieId],
                [
                    'titre'             => $p['protocole']['titre'],
                    'signes'            => $p['protocole']['signes'],
                    'diagnostics'       => $p['protocole']['diagnostics'],
                    'germes_nourrisson' => $p['protocole']['germes_nourrisson'],
                    'germes_adulte'     => $p['protocole']['germes_adulte'],
                    'remarques'         => $p['protocole']['remarques'],
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]
            );
            $protocoleId = DB::table('protocole_traitements')->where('maladie_id', $maladieId)->value('id');

            // Médicaments liés
            foreach ($p['protocole']['traitements'] as $t) {
                if (isset($mId[$t['nom']])) {
                    DB::table('protocole_medicament')->updateOrInsert(
                        [
                            'protocole_id'  => $protocoleId,
                            'medicament_id' => $mId[$t['nom']],
                        ],
                        [
                            'type'       => $t['type'],
                            'posologie'  => $t['poso'],
                            'duree'      => $t['duree'],
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]
                    );
                }
            }
        }
    }
}