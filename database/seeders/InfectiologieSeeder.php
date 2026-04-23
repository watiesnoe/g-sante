<?php

namespace Database\Seeders;
use Illuminate\Support\Str;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InfectiologieSeeder extends Seeder
{
    /**
     * Données spécialisées infectiologie avec protocoles étendus
     * (méningites, tuberculose, candidose systémique).
     *
     * Ce seeder est autonome:
     * - crée les unités/familles minimales si elles n'existent pas
     * - crée les médicaments nécessaires s'ils sont absents
     * - crée les symptômes et liaisons maladie/symptôme
     * - crée les protocoles et les liaisons protocole/médicament
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Unités minimales requises
        foreach (['comprimé', 'ampoule', 'capsule'] as $unite) {
            DB::table('unites')->updateOrInsert(
                ['nom' => $unite],
                ['uuid' => (string) Str::uuid(), 'created_at' => $now, 'uuid' => (string) Str::uuid(), 'updated_at' => $now]
            );
        }

        // Familles minimales requises
        foreach (['Antibiotiques', 'Antifongiques', 'Corticostéroïdes'] as $famille) {
            DB::table('familles')->updateOrInsert(
                ['nom' => $famille],
                ['uuid' => (string) Str::uuid(), 'created_at' => $now, 'uuid' => (string) Str::uuid(), 'updated_at' => $now]
            );
        }

        $uniteIds = DB::table('unites')->pluck('id', 'nom');
        $familleIds = DB::table('familles')->pluck('id', 'nom');

        // Médicaments requis pour les protocoles infectiologie
        $medicaments = [
            [
                'nom' => 'Ceftriaxone 1g Injectable',
                'description' => 'Céphalosporine de 3e génération pour infections sévères.',
                'stock' => 100,
                'stock_min' => 10,
                'prix_achat' => 2700,
                'prix_vente' => 4500,
                'unite' => 'ampoule',
                'famille' => 'Antibiotiques',
            ],
            [
                'nom' => 'Ampicilline 1g Injectable',
                'description' => 'Aminopénicilline injectable.',
                'stock' => 80,
                'stock_min' => 8,
                'prix_achat' => 1800,
                'prix_vente' => 3000,
                'unite' => 'ampoule',
                'famille' => 'Antibiotiques',
            ],
            [
                'nom' => 'Dexaméthasone 8mg Injectable',
                'description' => 'Corticoïde injectable adjuvant dans les infections sévères.',
                'stock' => 60,
                'stock_min' => 6,
                'prix_achat' => 1500,
                'prix_vente' => 2500,
                'unite' => 'ampoule',
                'famille' => 'Corticostéroïdes',
            ],
            [
                'nom' => 'Rifampicine 600mg',
                'description' => 'Antituberculeux de première ligne.',
                'stock' => 100,
                'stock_min' => 10,
                'prix_achat' => 2000,
                'prix_vente' => 3500,
                'unite' => 'comprimé',
                'famille' => 'Antibiotiques',
            ],
            [
                'nom' => 'Isoniazide 300mg',
                'description' => 'Antituberculeux bactéricide.',
                'stock' => 100,
                'stock_min' => 10,
                'prix_achat' => 900,
                'prix_vente' => 1500,
                'unite' => 'comprimé',
                'famille' => 'Antibiotiques',
            ],
            [
                'nom' => 'Pyrazinamide 500mg',
                'description' => 'Antituberculeux de la phase intensive.',
                'stock' => 80,
                'stock_min' => 8,
                'prix_achat' => 1200,
                'prix_vente' => 2000,
                'unite' => 'comprimé',
                'famille' => 'Antibiotiques',
            ],
            [
                'nom' => 'Ethambutol 400mg',
                'description' => 'Antituberculeux bactériostatique.',
                'stock' => 80,
                'stock_min' => 8,
                'prix_achat' => 1200,
                'prix_vente' => 2000,
                'unite' => 'comprimé',
                'famille' => 'Antibiotiques',
            ],
            [
                'nom' => 'Fluconazole 150mg',
                'description' => 'Antifongique azolé.',
                'stock' => 80,
                'stock_min' => 8,
                'prix_achat' => 2500,
                'prix_vente' => 4000,
                'unite' => 'capsule',
                'famille' => 'Antifongiques',
            ],
        ];

        foreach ($medicaments as $medicament) {
            DB::table('medicaments')->updateOrInsert(
                ['nom' => $medicament['nom']],
                [
                    'description' => $medicament['description'],
                    'stock' => $medicament['stock'],
                    'stock_min' => $medicament['stock_min'],
                    'prix_achat' => $medicament['prix_achat'],
                    'prix_vente' => $medicament['prix_vente'],
                    'unite_id' => $uniteIds[$medicament['unite']] ?? 1,
                    'famille_id' => $familleIds[$medicament['famille']] ?? 1,
                    'uuid' => (string) Str::uuid(), 'created_at' => $now,
                    'uuid' => (string) Str::uuid(), 'updated_at' => $now,
                ]
            );
        }

        $mId = DB::table('medicaments')->pluck('id', 'nom');

        // Symptômes utiles à l'affichage des pathologies infectieuses
        $symptomes = [
            'Fièvre' => 'Température corporelle élevée',
            'Céphalées' => 'Maux de tête',
            'Photophobie' => 'Gêne ou douleur à la lumière',
            'Raideur de la nuque' => 'Raideur cervicale évocatrice d’irritation méningée',
            'Toux' => 'Toux sèche ou productive',
            'Hémoptysies' => 'Rejet de sang dans les crachats',
            'Amaigrissement' => 'Perte de poids involontaire',
            'Sudations nocturnes' => 'Transpiration nocturne excessive',
            'Fièvre persistante' => 'Fièvre prolongée non expliquée',
            'Lésions muqueuses' => 'Atteinte des muqueuses type muguet ou plaques blanchâtres',
        ];

        foreach ($symptomes as $nom => $description) {
            DB::table('symptomes')->updateOrInsert(
                ['nom' => $nom],
                ['description' => $description, 'uuid' => (string) Str::uuid(), 'created_at' => $now, 'uuid' => (string) Str::uuid(), 'updated_at' => $now]
            );
        }

        $sId = DB::table('symptomes')->pluck('id', 'nom');

        // Protocoles infectiologie
        $protocoles = [
            [
                'maladie' => 'Méningites',
                'description' => 'Infection des méninges d’origine bactérienne ou virale, urgence thérapeutique.',
                'symptomes' => ['Fièvre', 'Céphalées', 'Photophobie', 'Raideur de la nuque'],
                'protocole' => [
                    'titre' => 'Protocole Méningites Bactériennes',
                    'signes' => 'Fièvre, céphalées intenses, photophobie, raideur de la nuque, purpura, altération de conscience',
                    'diagnostics' => 'Ponction lombaire: LCR purulent, cellularité > 1000 éléments/mm³, glycorachie basse. Hémocultures, PCR méningocoque/pneumocoque.',
                    'germes_nourrisson' => 'E. coli K1, Streptocoque B, Listeria monocytogenes, H. influenzae',
                    'germes_adulte' => 'Streptococcus pneumoniae, Neisseria meningitidis, Listeria (sujet âgé/immunodéprimé)',
                    'remarques' => "URGENCE ABSOLUE. Ne pas retarder l'antibiothérapie. Si purpura fulminans: Amoxicilline IV ou C3G immédiate avant tout examen. Dexaméthasone adjuvante réduit les séquelles.",
                    'traitements' => [
                        ['nom' => 'Ceftriaxone 1g Injectable', 'type' => 'principal', 'poso' => '100 mg/kg/j (max 4g) IV en 2 injections', 'duree' => '10 jours'],
                        ['nom' => 'Ampicilline 1g Injectable', 'type' => 'alternatif', 'poso' => '200 mg/kg/j IV en 4-6 injections', 'duree' => '14 jours'],
                        ['nom' => 'Dexaméthasone 8mg Injectable', 'type' => 'adjuvant', 'poso' => '0.15 mg/kg IV/6h (30 min avant l\'antibiothérapie)', 'duree' => '4 jours'],
                    ],
                ],
            ],
            [
                'maladie' => 'Tuberculose pulmonaire',
                'description' => 'Infection pulmonaire chronique à Mycobacterium tuberculosis.',
                'symptomes' => ['Toux', 'Hémoptysies', 'Amaigrissement', 'Sudations nocturnes'],
                'protocole' => [
                    'titre' => 'Protocole Tuberculose Pulmonaire (2RHZE/4RH)',
                    'signes' => 'Toux > 3 semaines, hémoptysies, sudations nocturnes, amaigrissement, fièvre vespérale',
                    'diagnostics' => 'IDR, Radiographie thoracique (infiltrats apicaux, cavernes), Crachat BK × 3, GeneXpert MTB/RIF',
                    'germes_nourrisson' => 'Mycobacterium tuberculosis (formes miliaires fréquentes)',
                    'germes_adulte' => 'Mycobacterium tuberculosis, M. bovis',
                    'remarques' => 'Phase intensive 2 mois (RHZE) puis phase de continuation 4 mois (RH). Surveillance mensuelle de la tolérance. Déclaration obligatoire.',
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
                'description' => 'Infection fongique invasive à Candida chez patients fragiles ou immunodéprimés.',
                'symptomes' => ['Fièvre persistante', 'Lésions muqueuses'],
                'protocole' => [
                    'titre' => 'Traitement Candidose Systémique',
                    'signes' => 'Fièvre persistante non expliquée, lésions muqueuses (muguet), infections récidivantes',
                    'diagnostics' => 'Hémocultures (Candida), Sérologie βD-glucane, Fond d\'œil',
                    'germes_nourrisson' => 'Candida albicans, Candida parapsilosis',
                    'germes_adulte' => 'Candida albicans, Candida glabrata, Candida tropicalis',
                    'remarques' => 'Retrait des cathéters si candidémie confirmée. Durée minimale 14 jours après la dernière hémoculture négative.',
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
                [
                    'description' => $p['description'],
                    'uuid' => (string) Str::uuid(), 'created_at' => $now,
                    'uuid' => (string) Str::uuid(), 'updated_at' => $now,
                ]
            );

            $maladieId = DB::table('maladies')->where('nom', $p['maladie'])->value('id');

            // Liaison symptômes
            foreach ($p['symptomes'] as $symptomeNom) {
                if (isset($sId[$symptomeNom])) {
                    DB::table('maladie_symptome')->updateOrInsert(
                        [
                            'maladie_id' => $maladieId,
                            'symptome_id' => $sId[$symptomeNom],
                        ],
                        [
                            'uuid' => (string) Str::uuid(), 'created_at' => $now,
                            'uuid' => (string) Str::uuid(), 'updated_at' => $now,
                        ]
                    );
                }
            }

            // Protocole
            DB::table('protocole_traitements')->updateOrInsert(
                ['maladie_id' => $maladieId],
                [
                    'titre' => $p['protocole']['titre'],
                    'signes' => $p['protocole']['signes'],
                    'diagnostics' => $p['protocole']['diagnostics'],
                    'germes_nourrisson' => $p['protocole']['germes_nourrisson'],
                    'germes_adulte' => $p['protocole']['germes_adulte'],
                    'remarques' => $p['protocole']['remarques'],
                    'uuid' => (string) Str::uuid(), 'created_at' => $now,
                    'uuid' => (string) Str::uuid(), 'updated_at' => $now,
                ]
            );

            $protocoleId = DB::table('protocole_traitements')->where('maladie_id', $maladieId)->value('id');

            // Médicaments liés
            foreach ($p['protocole']['traitements'] as $t) {
                if (isset($mId[$t['nom']])) {
                    DB::table('protocole_medicament')->updateOrInsert(
                        [
                            'protocole_id' => $protocoleId,
                            'medicament_id' => $mId[$t['nom']],
                        ],
                        [
                            'type' => $t['type'],
                            'posologie' => $t['poso'],
                            'duree' => $t['duree'],
                            'uuid' => (string) Str::uuid(), 'created_at' => $now,
                            'uuid' => (string) Str::uuid(), 'updated_at' => $now,
                        ]
                    );
                }
            }
        }
    }
}
