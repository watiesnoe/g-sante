<?php

namespace Database\Seeders;
use Illuminate\Support\Str;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PathologiesSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        // 1. UNITÉS
        $unites = [
            'comprimé', 'ampoule', 'ml', 'goutte', 'sachet', 'tube', 'capsule', 
            'perfusion', 'gel', 'suppositoire', 'puff', 'patch'
        ];
        foreach ($unites as $u) {
            DB::table('unites')->updateOrInsert(['nom' => $u]);
        }
        $uId = DB::table('unites')->pluck('id', 'nom');

        // 2. FAMILLES
        $familles = [
            'Antibiotiques', 'Antalgiques', 'Anti-inflammatoires', 'Antipaludiques', 
            'Antifongiques', 'Antiarythmiques', 'Antihypertenseurs', 'Antidiabétiques',
            'Antiviraux', 'Antianémiques', 'Corticostéroïdes', 'Bronchodilatateurs'
        ];
        foreach ($familles as $f) {
            DB::table('familles')->updateOrInsert(['nom' => $f]);
        }
        $fId = DB::table('familles')->pluck('id', 'nom');

        // 3. SYMPTÔMES
        $symptomesData = [
            // Respiratoires
            'Toux productive', 'Toux sèche', 'Dyspnée', 'Douleur thoracique', 'Râles crépitants', 'Sifflements respiratoires',
            // Généraux
            'Fièvre', 'Frissons', 'Asthénie', 'Céphalées', 'Courbatures', 'Sudations nocturnes',
            // Digestifs
            'Vomissements', 'Diarrhée liquide', 'Douleurs abdominales', 'Nausées', 'Perte d\'appétit',
            // Urinaires
            'Brûlures mictionnelles', 'Dysurie', 'Ecoulement urétral', 'Hématurie',
            // Cardiovasculaires
            'Palpitations', 'Oedèmes des membres inférieurs', 'Vertiges',
            // Autres
            'Ictère', 'Pâleur cutanéo-muqueuse', 'Prurit', 'Chancre indolore'
        ];
        foreach ($symptomesData as $nom) {
            DB::table('symptomes')->updateOrInsert(['nom' => $nom], ['uuid' => (string) Str::uuid(), 'created_at' => $now]);
        }
        $sId = DB::table('symptomes')->pluck('id', 'nom');
 
        // 4. RÉCUPÉRATION DES MÉDICAMENTS (déjà seedés par MedicamentsSeeder)
        $mId = DB::table('medicaments')->pluck('id', 'nom');
        // 5. PATHOLOGIES & PROTOCOLES
        $pathologies = [
            [
                'nom' => 'Paludisme simple',
                'symptomes' => ['Fièvre', 'Céphalées', 'Frissons', 'Fatigue'],
                'protocole' => [
                    'titre' => 'Protocole Paludisme National',
                    'signes' => 'Fièvre, test TDR positif',
                    'traitements' => [
                        ['nom' => 'Artéméther + Luméfantrine (Coartem)', 'type' => 'principal', 'poso' => '1 cp 2x/j pendant 3j', 'duree' => '3j'],
                        ['nom' => 'Paracétamol 500mg', 'type' => 'adjuvant', 'poso' => '1 cp 3x/j si fièvre', 'duree' => '3j'],
                    ]
                ]
            ],
            [
                'nom' => 'Paludisme grave',
                'symptomes' => ['Fièvre', 'Vomissements', 'Asthénie', 'Vertiges', 'Ictère'],
                'protocole' => [
                    'titre' => 'Hospitalisation Palu Grave',
                    'signes' => 'Signes neurologiques, vomissements incoercibles',
                    'traitements' => [
                        ['nom' => 'Artésunate Injectable', 'type' => 'principal', 'poso' => '2.4 mg/kg IV à H0, H12, H24', 'duree' => '1j'],
                        ['nom' => 'Quinine 600mg', 'type' => 'relais', 'poso' => '500mg en perfusion / 8h', 'duree' => '3j'],
                    ]
                ]
            ],
            [
                'nom' => 'Pneumopathie bactérienne',
                'symptomes' => ['Toux productive', 'Fièvre', 'Douleur thoracique', 'Dyspnée', 'Râles crépitants'],
                'protocole' => [
                    'titre' => 'Traitement Pneumonie Adulte',
                    'signes' => 'Expectoration purulente, foyer à la radio',
                    'traitements' => [
                        ['nom' => 'Amoxicilline + Acide Clavulanique (Augmentin)', 'type' => 'principal', 'poso' => '1g 2x/j', 'duree' => '7j'],
                        ['nom' => 'Azithromycine 500mg', 'type' => 'adjuvant', 'poso' => '500mg/j', 'duree' => '3j'],
                    ]
                ]
            ],
            [
                'nom' => 'Infection Urinaire (Cystite)',
                'symptomes' => ['Brûlures mictionnelles', 'Dysurie', 'Douleurs abdominales'],
                'protocole' => [
                    'titre' => 'Traitement Minute Cystite',
                    'signes' => 'Bandelette urinaire positive (Leuco/Nitrites)',
                    'traitements' => [
                        ['nom' => 'Ciprofloxacine 500mg', 'type' => 'principal', 'poso' => '500mg 2x/j', 'duree' => '5j'],
                    ]
                ]
            ],
            [
                'nom' => 'Gonococcie (IST)',
                'symptomes' => ['Ecoulement urétral', 'Brûlures mictionnelles'],
                'protocole' => [
                    'titre' => 'Protocole National IST',
                    'signes' => 'Ecoulement meatique purulent',
                    'traitements' => [
                        ['nom' => 'Ceftriaxone 1g Injectable', 'type' => 'principal', 'poso' => '1g IM dose unique', 'duree' => '1j'],
                        ['nom' => 'Azithromycine 500mg', 'type' => 'assos', 'poso' => '1g dose unique (chlamydia)', 'duree' => '1j'],
                    ]
                ]
            ],
            [
                'nom' => 'Syphilis primaire',
                'symptomes' => ['Chancre indolore'],
                'protocole' => [
                    'titre' => 'Traitement Syphilis',
                    'signes' => 'Chancre induré indolore, adénopathie latérale',
                    'traitements' => [
                        ['nom' => 'Ceftriaxone 1g Injectable', 'type' => 'principal', 'poso' => '1g IM/j', 'duree' => '10j'],
                    ]
                ]
            ],
            [
                'nom' => 'Gastro-entérite aiguë',
                'symptomes' => ['Diarrhée liquide', 'Vomissements', 'Douleurs abdominales', 'Nausées'],
                'protocole' => [
                    'titre' => 'Réhydratation & Anti-biothérapie GEA',
                    'signes' => 'Déshydratation clinique possible',
                    'traitements' => [
                        ['nom' => 'Métronidazole 500mg', 'type' => 'principal', 'poso' => '500mg 3x/j', 'duree' => '5j'],
                        ['nom' => 'Paracétamol 500mg', 'type' => 'adjuvant', 'poso' => '1g 3x/j si douleurs', 'duree' => '3j'],
                    ]
                ]
            ],
            [
                'nom' => 'Crise d\'Asthme simple',
                'symptomes' => ['Dyspnée', 'Sifflements respiratoires', 'Toux sèche'],
                'protocole' => [
                    'titre' => 'Urgence respiratoire Asthme',
                    'signes' => 'Bradypnée expiratoire sibilante',
                    'traitements' => [
                        ['nom' => 'Salbutamol Puff', 'type' => 'principal', 'poso' => '2 bouffées / 15 min pendant 1h', 'duree' => '1j'],
                    ]
                ]
            ],
            [
                'nom' => 'Anémie ferriprive',
                'symptomes' => ['Asthénie', 'Pâleur cutanéo-muqueuse', 'Vertiges', 'Palpitations'],
                'protocole' => [
                    'titre' => 'Supplémentation Martiale',
                    'signes' => 'Taux d\'hémoglobine < 10g/dl',
                    'traitements' => [
                        ['nom' => 'Fumarate de Fer', 'type' => 'principal', 'poso' => '1 cp 2x/j', 'duree' => '30j'],
                    ]
                ]
            ]
        ];

        foreach ($pathologies as $p) {
            // 1. Maladie
            DB::table('maladies')->updateOrInsert(
                ['nom' => $p['nom']],
                ['uuid' => (string) Str::uuid(), 'created_at' => $now, 'uuid' => (string) Str::uuid(), 'updated_at' => $now]
            );
            $maladieId = DB::table('maladies')->where('nom', $p['nom'])->value('id');

            // 2. Lier Symptômes
            foreach ($p['symptomes'] as $sName) {
                if (isset($sId[$sName])) {
                    DB::table('maladie_symptome')->updateOrInsert([
                        'maladie_id' => $maladieId,
                        'symptome_id' => $sId[$sName]
                    ]);
                }
            }

            // 3. Protocole
            DB::table('protocole_traitements')->updateOrInsert(
                ['maladie_id' => $maladieId],
                [
                    'titre' => $p['protocole']['titre'],
                    'signes' => $p['protocole']['signes'],
                    'uuid' => (string) Str::uuid(), 'created_at' => $now,
                    'uuid' => (string) Str::uuid(), 'updated_at' => $now
                ]
            );
            $protocoleId = DB::table('protocole_traitements')->where('maladie_id', $maladieId)->value('id');

            // 4. Lier Médicaments
            foreach ($p['protocole']['traitements'] as $t) {
                if (isset($mId[$t['nom']])) {
                    DB::table('protocole_medicament')->updateOrInsert(
                        [
                            'protocole_id' => $protocoleId,
                            'medicament_id' => $mId[$t['nom']]
                        ],
                        [
                            'type' => $t['type'],
                            'posologie' => $t['poso'],
                            'duree' => $t['duree'],
                            'uuid' => (string) Str::uuid(), 'created_at' => $now,
                            'uuid' => (string) Str::uuid(), 'updated_at' => $now
                        ]
                    );
                }
            }
        }
    }
}