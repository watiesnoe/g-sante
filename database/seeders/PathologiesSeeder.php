<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PathologiesSeeder extends Seeder
{
    /**
     * Seeder complet : Symptômes, Maladies, Pivot maladie_symptome, Protocoles, Médicaments.
     * Adapté au schéma g-santé existant.
     */
    public function run()
    {
        $now = Carbon::now();

        // ╔═══════════════════════════════════════════════════════════╗
        // ║  1. SYMPTÔMES                                            ║
        // ╚═══════════════════════════════════════════════════════════╝
        $symptomesData = [
            // Respiratoires
            'Toux productive', 'Toux sèche', 'Dyspnée', 'Expectorations purulentes',
            'Douleur thoracique', 'Hémoptysie', 'Wheezing/Sibilants',
            // Fièvre / Généraux
            'Fièvre', 'Frissons', 'Asthénie', 'Anorexie', 'Amaigrissement',
            'Sueurs nocturnes', 'Altération de la conscience',
            // Céphalées / Neuro
            'Céphalées', 'Vertiges', 'Photophobie', 'Raideur de la nuque',
            'Convulsions', 'Insomnies',
            // Digestifs
            'Nausées', 'Vomissements', 'Diarrhée', 'Constipation',
            'Douleurs abdominales', 'Douleur hypochondre droit', 'Ictère',
            'Selles liquides', 'Selles glairo-sanglantes',
            // Urinaires
            'Brûlures mictionnelles', 'Pollakiurie', 'Dysurie',
            'Douleurs lombaires', 'Rétention aiguë d\'urine', 'Hématurie',
            // ORL
            'Mal de gorge', 'Odynophagie', 'Rhinorrhée', 'Obstruction nasale',
            'Otalgies', 'Otorrhée', 'Anosmie', 'Epistaxis',
            // Peau
            'Eruption cutanée', 'Placard inflammatoire', 'Prurit',
            'Douleur locale intense', 'Tuméfaction fluctuante', 'Nécrose cutanée',
            'Vésicules', 'Lésions papuleuses', 'Nodule douloureux',
            // Ostéo-articulaire
            'Douleurs articulaires', 'Impotence fonctionnelle',
            'Tuméfaction articulaire', 'Rachalgies',
            // Cardio-vasculaire
            'Tachycardie', 'Hypotension', 'Purpura',
            // Autres
            'Déshydratation', 'Œdèmes', 'Adénopathies',
            'Ecoulement urétral', 'Ecoulement vaginal', 'Chancre indolore',
            'Hépatomégalie', 'Masse palpable abdominale',
            // Pédiatrie
            'Émaciation sévère', 'Cheveux roux cassants', 'Peau sèche',
        ];

        $symptomeIds = [];
        foreach ($symptomesData as $nom) {
            DB::table('symptomes')->updateOrInsert(
                ['nom' => $nom],
                ['description' => null, 'created_at' => $now, 'updated_at' => $now]
            );
            $symptomeIds[$nom] = DB::table('symptomes')->where('nom', $nom)->value('id');
        }

        // ╔═══════════════════════════════════════════════════════════╗
        // ║  2. MÉDICAMENTS (ajout des manquants)                    ║
        // ╚═══════════════════════════════════════════════════════════╝
        // unite_id: 5=comprimé, 13=ampoule, 7=ml, 10=goutte, 16=sachet, 22=tube, 6=capsule
        // famille_id: 1=Antibiotiques, 2=Antalgiques, 3=Anti-inflammatoires, 6=Antipaludiques, 8=Antifongiques

        $medsToAdd = [
            // Antibiotiques oraux
            ['nom' => 'Amoxicilline/Acide clavulanique 1g',     'famille_id' => 1, 'unite_id' => 5, 'prix_achat' => 800, 'prix_vente' => 1200, 'stock' => 200, 'stock_min' => 50],
            ['nom' => 'Azithromycine 500mg',                    'famille_id' => 1, 'unite_id' => 5, 'prix_achat' => 1500, 'prix_vente' => 2500, 'stock' => 100, 'stock_min' => 30],
            ['nom' => 'Cotrimoxazole 960mg',                    'famille_id' => 1, 'unite_id' => 5, 'prix_achat' => 300, 'prix_vente' => 500, 'stock' => 300, 'stock_min' => 50],
            ['nom' => 'Doxycycline 100mg',                      'famille_id' => 1, 'unite_id' => 5, 'prix_achat' => 200, 'prix_vente' => 400, 'stock' => 250, 'stock_min' => 50],
            ['nom' => 'Métronidazole 500mg',                    'famille_id' => 1, 'unite_id' => 5, 'prix_achat' => 350, 'prix_vente' => 600, 'stock' => 200, 'stock_min' => 50],
            ['nom' => 'Erythromycine 500mg',                    'famille_id' => 1, 'unite_id' => 5, 'prix_achat' => 600, 'prix_vente' => 1000, 'stock' => 100, 'stock_min' => 30],
            ['nom' => 'Cefixime 200mg',                        'famille_id' => 1, 'unite_id' => 5, 'prix_achat' => 1200, 'prix_vente' => 2000, 'stock' => 80, 'stock_min' => 20],
            ['nom' => 'Ofloxacine 200mg',                      'famille_id' => 1, 'unite_id' => 5, 'prix_achat' => 900, 'prix_vente' => 1500, 'stock' => 120, 'stock_min' => 30],
            ['nom' => 'Nitrofurantoine 100mg',                  'famille_id' => 1, 'unite_id' => 6, 'prix_achat' => 500, 'prix_vente' => 800, 'stock' => 80, 'stock_min' => 20],
            ['nom' => 'Spiramycine 3 MUI',                      'famille_id' => 1, 'unite_id' => 5, 'prix_achat' => 1000, 'prix_vente' => 1800, 'stock' => 60, 'stock_min' => 15],
            ['nom' => 'Clindamycine 300mg',                     'famille_id' => 1, 'unite_id' => 6, 'prix_achat' => 800, 'prix_vente' => 1500, 'stock' => 60, 'stock_min' => 15],
            ['nom' => 'Flucloxacilline 500mg',                  'famille_id' => 1, 'unite_id' => 6, 'prix_achat' => 700, 'prix_vente' => 1200, 'stock' => 50, 'stock_min' => 15],
            ['nom' => 'Pristinamycine 500mg',                   'famille_id' => 1, 'unite_id' => 5, 'prix_achat' => 2500, 'prix_vente' => 4000, 'stock' => 40, 'stock_min' => 10],
            ['nom' => 'Cefadroxil 500mg',                      'famille_id' => 1, 'unite_id' => 6, 'prix_achat' => 1100, 'prix_vente' => 1800, 'stock' => 60, 'stock_min' => 15],

            // Antibiotiques injectables
            ['nom' => 'Céfotaxime 1g Injectable',               'famille_id' => 1, 'unite_id' => 13, 'prix_achat' => 2000, 'prix_vente' => 3500, 'stock' => 80, 'stock_min' => 20],
            ['nom' => 'Ceftazidime 1g Injectable',               'famille_id' => 1, 'unite_id' => 13, 'prix_achat' => 3000, 'prix_vente' => 5000, 'stock' => 40, 'stock_min' => 10],
            ['nom' => 'Pipéracilline/Tazobactam 4g Injectable',  'famille_id' => 1, 'unite_id' => 13, 'prix_achat' => 5000, 'prix_vente' => 8000, 'stock' => 30, 'stock_min' => 10],
            ['nom' => 'Vancomycine 500mg Injectable',            'famille_id' => 1, 'unite_id' => 13, 'prix_achat' => 4000, 'prix_vente' => 6500, 'stock' => 30, 'stock_min' => 10],
            ['nom' => 'Imipénème 500mg Injectable',              'famille_id' => 1, 'unite_id' => 13, 'prix_achat' => 6000, 'prix_vente' => 9000, 'stock' => 25, 'stock_min' => 10],
            ['nom' => 'Amikacine 500mg Injectable',              'famille_id' => 1, 'unite_id' => 13, 'prix_achat' => 2500, 'prix_vente' => 4000, 'stock' => 50, 'stock_min' => 15],
            ['nom' => 'Gentamicine 80mg Injectable',             'famille_id' => 1, 'unite_id' => 13, 'prix_achat' => 800, 'prix_vente' => 1500, 'stock' => 80, 'stock_min' => 20],
            ['nom' => 'Pénicilline G 5MUI Injectable',           'famille_id' => 1, 'unite_id' => 13, 'prix_achat' => 1000, 'prix_vente' => 1800, 'stock' => 50, 'stock_min' => 15],
            ['nom' => 'Benzathine Pénicilline 2.4 MUI',          'famille_id' => 1, 'unite_id' => 13, 'prix_achat' => 1500, 'prix_vente' => 2500, 'stock' => 40, 'stock_min' => 10],
            ['nom' => 'Métronidazole 500mg perfusion',           'famille_id' => 1, 'unite_id' => 13, 'prix_achat' => 600, 'prix_vente' => 1000, 'stock' => 100, 'stock_min' => 30],
            ['nom' => 'Ciprofloxacine 400mg perfusion',          'famille_id' => 1, 'unite_id' => 13, 'prix_achat' => 2000, 'prix_vente' => 3500, 'stock' => 50, 'stock_min' => 15],

            // Ophtalmologie
            ['nom' => 'Gentamicine collyre',                     'famille_id' => 1, 'unite_id' => 10, 'prix_achat' => 1200, 'prix_vente' => 2000, 'stock' => 30, 'stock_min' => 10],
        ];

        foreach ($medsToAdd as $med) {
            DB::table('medicaments')->updateOrInsert(
                ['nom' => $med['nom']],
                array_merge($med, ['description' => null, 'created_at' => $now, 'updated_at' => $now])
            );
        }

        // ╔════════════════════════════════════════════════════════════╗
        // ║  3. RÉFÉRENTIEL EXPERT : 40+ PATHOLOGIES & PROTOCOLES     ║
        // ╚════════════════════════════════════════════════════════════╝
        $pathologies = [
            // ==================== PNEUMONIES COMMUNAUTAIRES ====================
            [
                'nom' => 'Pneumonie communautaire non sévère',
                'description' => 'Pneumonie ambulatoire (traitement oral)',
                'symptomes' => ['Toux productive', 'Fièvre', 'Frissons', 'Douleur thoracique'],
                'protocole' => [
                    'titre' => 'Pneumonie communautaire non sévère',
                    'signes' => 'Toux productive, fièvre, syndrome de condensation, opacités alvéolaires',
                    'diagnostics' => 'Radio thorax, ECB expectorations, recherche BK',
                    'germes_adulte' => 'S. pneumoniae, M. pneumoniae, C. pneumoniae, Legionella',
                    'germes_nourrisson' => 'S. pneumoniae, H. influenzae',
                    'traitement_principal' => 'Amoxicilline 500mg',
                    'posologie_principale' => 'Adulte: 1g 3x/jour (10j) — Enfant: 50mg/kg/jour (10j)',
                    'traitement_alternatif' => 'Azithromycine 500mg',
                    'posologie_alternative' => 'Adulte: 500mg/jour (7j) — Enfant: 10-20mg/kg/jour (7j)',
                ]
            ],
            [
                'nom' => 'Pneumonie communautaire (Hospitalisation)',
                'description' => 'Pneumonie avec comorbidités ou signes de gravité modérés',
                'symptomes' => ['Toux productive', 'Fièvre', 'Dyspnée', 'Asthénie'],
                'protocole' => [
                    'titre' => 'Pneumonie communautaire (hospitalisation simple)',
                    'signes' => 'Comorbidités, dyspnée, fièvre',
                    'diagnostics' => 'Radio thorax, ECB, Hémocultures',
                    'germes_adulte' => 'S. pneumoniae, Legionella, Mycoplasma, M. tuberculosis',
                    'germes_nourrisson' => 'S. pneumoniae, H. influenzae',
                    'traitement_principal' => 'Amoxicilline/Acide clavulanique 1g',
                    'posologie_principale' => 'Adulte: 1g toutes les 8h IVD (7j) — Enfant: 40-50mg/kg/j',
                    'traitement_alternatif' => 'Azithromycine 500mg',
                    'posologie_alternative' => 'Ajouter Azithromycine 500mg/j si suspicion de germes atypiques',
                ]
            ],
            [
                'nom' => 'Pneumonie communautaire (USI)',
                'description' => 'Détresse respiratoire ou choc septique d\'origine pulmonaire',
                'symptomes' => ['Dyspnée', 'Tachycardie', 'Hypotension', 'Altération de la conscience'],
                'protocole' => [
                    'titre' => 'Pneumonie communautaire grave (USI)',
                    'signes' => 'FR≥30, TAS≤90, FC≥120, Hypoxie sévère',
                    'diagnostics' => 'Gaze du sang, Radio, ECB LBA, Hémocultures',
                    'germes_adulte' => 'S. pneumoniae, S. aureus, BGN, Legionella',
                    'germes_nourrisson' => 'S. pneumoniae, S. aureus',
                    'traitement_principal' => 'Céfotaxime 1g Injectable, Azithromycine 500mg',
                    'posologie_principale' => 'Céfotaxime 100mg/kg/j + Azithromycine 500mg/j (14j)',
                    'traitement_alternatif' => 'Vancomycine 500mg Injectable',
                    'posologie_alternative' => 'Si suspicion SARM : 40mg/kg/j en perfusion',
                ]
            ],

            // ==================== PNEUMONIES NOSOCOMIALES ====================
            [
                'nom' => 'Pneumonie nosocomiale précoce',
                'description' => 'Apparition < 5 jours après admission',
                'symptomes' => ['Fièvre', 'Toux productive', 'Dyspnée'],
                'protocole' => [
                    'titre' => 'Pneumonie nosocomiale précoce',
                    'signes' => 'Signes respiratoires apparaissant > 48h après admission',
                    'diagnostics' => 'Radio thorax, Prélèvement bronchique',
                    'germes_adulte' => 'S. pneumoniae, S. aureus (Méthi-S), Entérobactéries',
                    'germes_nourrisson' => 'Entérobactéries',
                    'traitement_principal' => 'Amoxicilline/Acide clavulanique 1g',
                    'posologie_principale' => '1g toutes les 8h (10j)',
                    'traitement_alternatif' => 'Ceftriaxone 1g Injectable',
                    'posologie_alternative' => '1g/12h ou 2g dose unique',
                ]
            ],
            [
                'nom' => 'Pneumonie nosocomiale tardive',
                'description' => 'Apparition > 5 jours après admission',
                'symptomes' => ['Fièvre', 'Expectoration purulente', 'Hypoxie'],
                'protocole' => [
                    'titre' => 'Pneumonie nosocomiale tardive (Grave)',
                    'signes' => 'Sécrétions purulentes massives, choc septique fréquent',
                    'diagnostics' => 'Scanner pulmonaire, LBA, Hémocultures',
                    'germes_adulte' => 'P. aeruginosa, Acinetobacter, SARM, Entérobactéries BLSE',
                    'germes_nourrisson' => 'BGN multirésistants',
                    'traitement_principal' => 'Pipéracilline/Tazobactam 4g Injectable',
                    'posologie_principale' => '4g toutes les 8h en perfusion de 3 heures',
                    'traitement_alternatif' => 'Vancomycine 500mg Injectable, Amikacine 500mg Injectable',
                    'posologie_alternative' => 'Bi-thérapie pour couvrir P. aeruginosa et SARM',
                ]
            ],

            // ==================== SEPTICÉMIES ====================
            [
                'nom' => 'Septicémie communautaire cutanée',
                'description' => 'Sepsis à point de départ cutané',
                'symptomes' => ['Fièvre', 'Placard inflammatoire', 'Tachycardie', 'Hypotension'],
                'protocole' => [
                    'titre' => 'Septicémie d\'origine cutanée',
                    'signes' => 'Température <36 ou >38, qSOFA ≥ 2',
                    'diagnostics' => 'Hémocultures x3, Prélèvement local, NFS, CRP',
                    'germes_adulte' => 'S. aureus, S. pyogenes, P. aeruginosa',
                    'germes_nourrisson' => 'S. aureus',
                    'traitement_principal' => 'Amoxicilline/Acide clavulanique 1g, Amikacine 500mg Injectable',
                    'posologie_principale' => 'Amox/Clav 1g/6h IV + Amikacine 25mg/kg/j (48h)',
                    'traitement_alternatif' => 'Ceftriaxone 1g Injectable, Gentamicine 80mg Injectable',
                    'posologie_alternative' => 'Ceftriaxone 2g/24h + Gentamicine 7mg/kg/j',
                ]
            ],
            [
                'nom' => 'Septicémie communautaire digestive',
                'description' => 'Sepsis à point de départ abdominal',
                'symptomes' => ['Fièvre', 'Douleurs abdominales', 'Frissons', 'Vomissements'],
                'protocole' => [
                    'titre' => 'Septicémie d\'origine digestive',
                    'signes' => 'Sepsis, défense abdominale ou syndrome occlusif',
                    'diagnostics' => 'Scanner AP, Hémocultures, Ionogramme',
                    'germes_adulte' => 'E. coli, Klebsiella, Anaérobies, S. enterica',
                    'germes_nourrisson' => 'Salmonelle, E. coli',
                    'traitement_principal' => 'Ceftriaxone 1g Injectable, Métronidazole 500mg perfusion, Amikacine 500mg Injectable',
                    'posologie_principale' => 'Ceftriaxone 2g + Métronidazole 500mgx3 + Amikacine 25mg/kg',
                    'traitement_alternatif' => 'Ciprofloxacine 400mg perfusion, Gentamicine 80mg Injectable',
                    'posologie_alternative' => 'Si allergie aux bétalactamines',
                ]
            ],

            // ==================== IST ====================
            [
                'nom' => 'Gonococcie',
                'description' => 'Infection à Neisseria gonorrhoeae',
                'symptomes' => ['Ecoulement urétral', 'Brûlures mictionnelles', 'Dysurie'],
                'protocole' => [
                    'titre' => 'Gonococcies (Urétrite/Cervicite)',
                    'signes' => 'Ecoulement purulent "chaude-pisse", méat inflammatoire',
                    'diagnostics' => 'Prélèvement local, recherche Chlamydia associée',
                    'germes_adulte' => 'Neisseria gonorrhoeae',
                    'germes_nourrisson' => 'N. gonorrhoeae (conjonctivite)',
                    'traitement_principal' => 'Ceftriaxone 1g Injectable',
                    'posologie_principale' => '1g dose unique IV ou IM',
                    'traitement_alternatif' => 'Doxycycline 100mg',
                    'posologie_alternative' => '100mg x2/jour pendant 7 jours',
                ]
            ],
            [
                'nom' => 'Chlamydiose',
                'description' => 'Infection à Chlamydia trachomatis',
                'symptomes' => ['Ecoulement urétral', 'Ecoulement vaginal', 'Prurit'],
                'protocole' => [
                    'titre' => 'Chlamydiose Urogénitale',
                    'signes' => 'Souvent peu symptomatique, écoulement clair',
                    'diagnostics' => 'PCR ou prélèvement local',
                    'germes_adulte' => 'Chlamydia trachomatis',
                    'germes_nourrisson' => 'C. trachomatis',
                    'traitement_principal' => 'Azithromycine 500mg',
                    'posologie_principale' => '500mg dose unique (Ou 1g selon recommandations local)',
                    'traitement_alternatif' => 'Doxycycline 100mg',
                    'posologie_alternative' => '100mg x2/jour pendant 7 jours',
                ]
            ],
            [
                'nom' => 'Syphilis primaire',
                'description' => 'Infection à Treponema pallidum (Stade 1)',
                'symptomes' => ['Chancre indolore', 'Adénopathies'],
                'protocole' => [
                    'titre' => 'Syphilis primaire',
                    'signes' => 'Chancre induré, propre, indolore + adénopathie satellite',
                    'diagnostics' => 'VDRL, TPHA',
                    'germes_adulte' => 'Treponema pallidum',
                    'germes_nourrisson' => 'Syphilis congénitale',
                    'traitement_principal' => 'Benzathine Pénicilline 2.4 MUI',
                    'posologie_principale' => '2,4 MUI dose unique IM profonde',
                    'traitement_alternatif' => 'Doxycycline 100mg',
                    'posologie_alternative' => '100mg x2/jour pendant 14 jours',
                ]
            ],

            // ==================== OSTÉOARTICULAIRE ====================
            [
                'nom' => 'Arthrite aiguë',
                'description' => 'Infection articulaire purulente',
                'symptomes' => ['Douleurs articulaires', 'Impotence fonctionnelle', 'Fièvre', 'Œdèmes'],
                'protocole' => [
                    'titre' => 'Arthrite bactérienne aiguë',
                    'signes' => 'Articulation chaude, tendue, douloureuse, fièvre élevée',
                    'diagnostics' => 'Ponction articulaire (ECB), Radio, Hémocultures',
                    'germes_adulte' => 'S. aureus, Streptocoques, N. gonorrhoeae',
                    'germes_nourrisson' => 'S. aureus, Streptocoque B',
                    'traitement_principal' => 'Céfotaxime 1g Injectable, Gentamicine 80mg Injectable',
                    'posologie_principale' => 'Céfotaxime 100mg/kg/j + Gentamicine 7mg/kg/j (48h)',
                    'traitement_alternatif' => 'Ceftriaxone 1g Injectable, Amikacine 500mg Injectable',
                    'posologie_alternative' => 'Si allergie ou suspicion de BGN',
                ]
            ],
            
            // ==================== DIGESTIF ====================
            [
                'nom' => 'Fièvre typhoïde',
                'description' => 'Infection systémique à Salmonelle',
                'symptomes' => ['Fièvre', 'Céphalées', 'Constipation', 'Diarrhée', 'Insomnies', 'Asthénie'],
                'protocole' => [
                    'titre' => 'Fièvre typhoïde',
                    'signes' => 'Température en "marche d\'escalier", pouls dissocié, tuphos',
                    'diagnostics' => 'Hémocultures (Sem 1), Widal et Félix, Coproculture (Sem 2)',
                    'germes_adulte' => 'Salmonella Typhi, Paratyphi A, B, C',
                    'germes_nourrisson' => 'S. Typhi',
                    'traitement_principal' => 'Ceftriaxone 1g Injectable',
                    'posologie_principale' => 'Adulte: 2g/jour IV (7j) — Enfant: 50-75mg/kg/j',
                    'traitement_alternatif' => 'Cefixime 200mg',
                    'posologie_alternative' => '200mg x2/jour pendant 7 à 10 jours',
                ]
            ],
            [
                'nom' => 'Choléra',
                'description' => 'Diarrhée toxique foudroyante',
                'symptomes' => ['Diarrhée', 'Selles liquides', 'Vomissements', 'Déshydratation'],
                'protocole' => [
                    'titre' => 'Choléra (Prise en charge)',
                    'signes' => 'Selles aqueuses "riziformes", déshydratation massive',
                    'diagnostics' => 'Test rapide, Coproculture',
                    'germes_adulte' => 'Vibrio cholerae',
                    'germes_nourrisson' => 'Vibrio cholerae',
                    'traitement_principal' => 'Doxycycline 100mg',
                    'posologie_principale' => 'Dose unique de 300mg (Adulte) — Enfant: 6mg/kg',
                    'traitement_alternatif' => 'Azithromycine 500mg',
                    'posologie_alternative' => 'Dose unique 1g (Adulte)',
                ]
            ],
        ];

        foreach ($pathologies as $data) {
            // Check if illness already exists
            $maladieId = DB::table('maladies')->where('nom', $data['nom'])->value('id');
            
            if (!$maladieId) {
                $maladieId = DB::table('maladies')->insertGetId([
                    'nom' => $data['nom'],
                    'description' => $data['description'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('maladies')->where('id', $maladieId)->update([
                    'description' => $data['description'],
                    'updated_at' => $now,
                ]);
            }

            // Sync Symptoms
            DB::table('maladie_symptome')->where('maladie_id', $maladieId)->delete();
            foreach ($data['symptomes'] as $symptomNom) {
                if (isset($symptomeIds[$symptomNom])) {
                    DB::table('maladie_symptome')->insert([
                        'maladie_id' => $maladieId,
                        'symptome_id' => $symptomeIds[$symptomNom],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            // Update/Insert Protocol
            $proto = $data['protocole'];
            DB::table('protocole_traitements')->updateOrInsert(
                ['maladie_id' => $maladieId],
                [
                    'titre' => $proto['titre'],
                    'signes' => $proto['signes'],
                    'diagnostics' => $proto['diagnostics'],
                    'germes_nourrisson' => $proto['germes_nourrisson'],
                    'germes_adulte' => $proto['germes_adulte'],
                    'traitement_principal' => $proto['traitement_principal'],
                    'posologie_principale' => $proto['posologie_principale'],
                    'traitement_alternatif' => $proto['traitement_alternatif'],
                    'posologie_alternative' => $proto['posologie_alternative'],
                    'remarques' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            // 🔹 AUTO-CREATE MISSING MEDICAMENTS
            $medsToEnsure = [];
            if (!empty($proto['traitement_principal'])) {
                foreach (explode(',', $proto['traitement_principal']) as $m) $medsToEnsure[] = trim($m);
            }
            if (!empty($proto['traitement_alternatif'])) {
                foreach (explode(',', $proto['traitement_alternatif']) as $m) $medsToEnsure[] = trim($m);
            }

            foreach ($medsToEnsure as $medName) {
                if (empty($medName)) continue;
                $exists = DB::table('medicaments')->where('nom', $medName)->exists();
                if (!$exists) {
                    $familleId = 1; // Default: Antibiotics
                    if (stripos($medName, 'Paracé') !== false) $familleId = 2; // Antalgics
                    
                    DB::table('medicaments')->insert([
                        'nom' => $medName,
                        'famille_id' => $familleId,
                        'unite_id' => 5, // Default: Tablet
                        'prix_achat' => 500,
                        'prix_vente' => 1000,
                        'stock' => 50,
                        'stock_min' => 10,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }
}
