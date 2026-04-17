<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedicamentsSeeder extends Seeder
{
    /**
     * Peuple la pharmacopée de base de l'établissement.
     * Doit être exécuté APRÈS UniteSeeder et FamilleSeeder.
     */
    public function run(): void
    {
        $unites  = DB::table('unites')->pluck('id', 'nom');
        $familles = DB::table('familles')->pluck('id', 'nom');

        // --- MÉDICAMENTS ESSENTIELS (OMS + Protocoles nationaux) ---
        $medicaments = [
            // ANTALGIQUES
            ['nom' => 'Paracétamol 500mg',          'famille' => 'Antalgiques',         'unite' => 'comprimé',  'stock' => 500, 'p_a' => 300,  'p_v' => 500,   'desc' => 'Antalgique et antipyrétique de référence.'],
            ['nom' => 'Paracétamol Sirop Enfant',    'famille' => 'Antalgiques',         'unite' => 'ml',        'stock' => 100, 'p_a' => 1500, 'p_v' => 2500,  'desc' => 'Sirop paracétamol 120mg/5ml pour enfants.'],
            ['nom' => 'Tramadol 100mg Injectable',   'famille' => 'Antalgiques',         'unite' => 'ampoule',   'stock' => 50,  'p_a' => 2500, 'p_v' => 4000,  'desc' => 'Analgésique opioïde pour douleurs modérées à sévères.'],

            // ANTI-INFLAMMATOIRES
            ['nom' => 'Ibuprofène 400mg',            'famille' => 'Anti-inflammatoires', 'unite' => 'comprimé',  'stock' => 300, 'p_a' => 700,  'p_v' => 1200,  'desc' => 'AINS pour douleurs et inflammations.'],
            ['nom' => 'Diclofénac 50mg',             'famille' => 'Anti-inflammatoires', 'unite' => 'comprimé',  'stock' => 200, 'p_a' => 900,  'p_v' => 1500,  'desc' => 'AINS, indiqué en rhumatologie et gynécologie.'],
            ['nom' => 'Diclofénac 75mg Injectable',  'famille' => 'Anti-inflammatoires', 'unite' => 'ampoule',   'stock' => 80,  'p_a' => 1500, 'p_v' => 2500,  'desc' => 'AINS injectable pour crises aiguës.'],

            // ANTIBIOTIQUES
            ['nom' => 'Amoxicilline 500mg',          'famille' => 'Antibiotiques',       'unite' => 'comprimé',  'stock' => 400, 'p_a' => 600,  'p_v' => 1000,  'desc' => 'Pénicilline à large spectre de première intention.'],
            ['nom' => 'Amoxicilline + Acide Clavulanique (Augmentin)', 'famille' => 'Antibiotiques', 'unite' => 'comprimé', 'stock' => 200, 'p_a' => 4500, 'p_v' => 7500, 'desc' => 'Association béta-lactamine + inhibiteur de béta-lactamase.'],
            ['nom' => 'Ceftriaxone 1g Injectable',   'famille' => 'Antibiotiques',       'unite' => 'ampoule',   'stock' => 100, 'p_a' => 2700, 'p_v' => 4500,  'desc' => 'Céphalosporine 3G pour infections sévères.'],
            ['nom' => 'Azithromycine 500mg',          'famille' => 'Antibiotiques',       'unite' => 'comprimé',  'stock' => 150, 'p_a' => 3000, 'p_v' => 5000,  'desc' => 'Macrolide pour atypiques et IST.'],
            ['nom' => 'Doxycycline 100mg',            'famille' => 'Antibiotiques',       'unite' => 'comprimé',  'stock' => 200, 'p_a' => 1200, 'p_v' => 2000,  'desc' => 'Tétracycline à large spectre.'],
            ['nom' => 'Métronidazole 500mg',          'famille' => 'Antibiotiques',       'unite' => 'comprimé',  'stock' => 250, 'p_a' => 900,  'p_v' => 1500,  'desc' => 'Imidazolé actif sur anaérobies et parasites.'],
            ['nom' => 'Ciprofloxacine 500mg',         'famille' => 'Antibiotiques',       'unite' => 'comprimé',  'stock' => 150, 'p_a' => 1800, 'p_v' => 3000,  'desc' => 'Fluoroquinolone à large spectre.'],
            ['nom' => 'Ampicilline 1g Injectable',    'famille' => 'Antibiotiques',       'unite' => 'ampoule',   'stock' => 80,  'p_a' => 1800, 'p_v' => 3000,  'desc' => 'Aminopénicilline injectable.'],
            ['nom' => 'Gentamicine 80mg Injectable',  'famille' => 'Antibiotiques',       'unite' => 'ampoule',   'stock' => 60,  'p_a' => 1200, 'p_v' => 2000,  'desc' => 'Aminoside injectable pour infections sévères.'],

            // ANTIPALUDIQUES
            ['nom' => 'Artéméther + Luméfantrine (Coartem)', 'famille' => 'Antipaludiques', 'unite' => 'comprimé', 'stock' => 300, 'p_a' => 2000, 'p_v' => 3500, 'desc' => 'CTA de première ligne contre Plasmodium falciparum.'],
            ['nom' => 'Artésunate Injectable',         'famille' => 'Antipaludiques',    'unite' => 'ampoule',   'stock' => 50,  'p_a' => 3000, 'p_v' => 5000,  'desc' => 'Traitement IV du paludisme grave.'],
            ['nom' => 'Quinine 600mg',                 'famille' => 'Antipaludiques',    'unite' => 'perfusion', 'stock' => 60,  'p_a' => 1500, 'p_v' => 2500,  'desc' => 'Alcaloïde du quinquina, relais ou alternative.'],
            ['nom' => 'Chloroquine 100mg',             'famille' => 'Antipaludiques',    'unite' => 'comprimé',  'stock' => 120, 'p_a' => 500,  'p_v' => 900,   'desc' => 'Antipaludéen pour P. vivax et P. ovale.'],

            // ANTIFONGIQUES
            ['nom' => 'Fluconazole 150mg',             'famille' => 'Antifongiques',      'unite' => 'capsule',   'stock' => 80,  'p_a' => 2500, 'p_v' => 4000,  'desc' => 'Antifongique azolé systémique.'],

            // ANTIHYPERTENSEURS
            ['nom' => 'Amlodipine 10mg',               'famille' => 'Antihypertenseurs',  'unite' => 'comprimé',  'stock' => 150, 'p_a' => 900,  'p_v' => 1500,  'desc' => 'Inhibiteur calcique pour HTA et angor.'],
            ['nom' => 'Furosémide 40mg',                'famille' => 'Antihypertenseurs',  'unite' => 'comprimé',  'stock' => 200, 'p_a' => 600,  'p_v' => 1000,  'desc' => 'Diurétique de l\'anse pour HTA et insuffisance cardiaque.'],

            // ANTIDIABÉTIQUES
            ['nom' => 'Metformine 850mg',               'famille' => 'Antidiabétiques',    'unite' => 'comprimé',  'stock' => 200, 'p_a' => 700,  'p_v' => 1200,  'desc' => 'Biguanide de première ligne du diabète type 2.'],

            // BRONCHODILATATEURS
            ['nom' => 'Salbutamol Puff',                'famille' => 'Bronchodilatateurs', 'unite' => 'puff',      'stock' => 50,  'p_a' => 2700, 'p_v' => 4500,  'desc' => 'Bronchodilatateur bêta-2 à courte durée d\'action.'],

            // ANTIANÉMIQUES
            ['nom' => 'Fumarate de Fer',                'famille' => 'Antianémiques',      'unite' => 'comprimé',  'stock' => 300, 'p_a' => 900,  'p_v' => 1500,  'desc' => 'Supplémentation en fer pour anémie ferriprive.'],
            ['nom' => 'Acide Folique 5mg',              'famille' => 'Antianémiques',      'unite' => 'comprimé',  'stock' => 200, 'p_a' => 200,  'p_v' => 400,   'desc' => 'Vitamine B9, prévention des anomalies du tube neural.'],

            // CORTICOÏDES
            ['nom' => 'Dexaméthasone 8mg Injectable',   'famille' => 'Corticostéroïdes',   'unite' => 'ampoule',   'stock' => 60,  'p_a' => 1500, 'p_v' => 2500,  'desc' => 'Corticoïde puissant pour urgences inflammatoires.'],
            ['nom' => 'Prednisolone 20mg',               'famille' => 'Corticostéroïdes',   'unite' => 'comprimé',  'stock' => 150, 'p_a' => 900,  'p_v' => 1500,  'desc' => 'Corticoïde oral pour maladies inflammatoires chroniques.'],

            // ANTITUBERCULEUX
            ['nom' => 'Rifampicine 600mg',              'famille' => 'Antibiotiques',       'unite' => 'comprimé',  'stock' => 100, 'p_a' => 2000, 'p_v' => 3500,  'desc' => 'Antituberculeux de première ligne.'],
            ['nom' => 'Isoniazide 300mg',               'famille' => 'Antibiotiques',       'unite' => 'comprimé',  'stock' => 100, 'p_a' => 900,  'p_v' => 1500,  'desc' => 'Antituberculeux bactéricide.'],
            ['nom' => 'Pyrazinamide 500mg',             'famille' => 'Antibiotiques',       'unite' => 'comprimé',  'stock' => 80,  'p_a' => 1200, 'p_v' => 2000,  'desc' => 'Antituberculeux de la phase intensive.'],
            ['nom' => 'Ethambutol 400mg',               'famille' => 'Antibiotiques',       'unite' => 'comprimé',  'stock' => 80,  'p_a' => 1200, 'p_v' => 2000,  'desc' => 'Antituberculeux bactériostatique.'],
            ['nom' => 'Chloramphénicol 1g Injectable',  'famille' => 'Antibiotiques',       'unite' => 'ampoule',   'stock' => 40,  'p_a' => 1500, 'p_v' => 2500,  'desc' => 'Antibiotique à large spectre réservé aux situations critiques.'],

            // DIVERS URGENCES
            ['nom' => 'Adrénaline 1mg Injectable',      'famille' => 'Antianémiques',       'unite' => 'ampoule',   'stock' => 30,  'p_a' => 2500, 'p_v' => 4000,  'desc' => 'Catécholamine pour choc anaphylactique et réanimation.'],
            ['nom' => 'Sérum Salé 0.9%',                'famille' => 'Antianémiques',       'unite' => 'perfusion', 'stock' => 120, 'p_a' => 1000, 'p_v' => 1800,  'desc' => 'Soluté isotonique pour réhydratation IV et dilution médicaments.'],
            ['nom' => 'Sérum Glucosé 5%',               'famille' => 'Antianémiques',       'unite' => 'perfusion', 'stock' => 100, 'p_a' => 1200, 'p_v' => 2000,  'desc' => 'Soluté glucosé pour apport calorique et réhydratation.'],
        ];

        foreach ($medicaments as $m) {
            DB::table('medicaments')->updateOrInsert(
                ['nom' => $m['nom']],
                [
                    'description' => $m['desc'] ?? null,
                    'stock'       => $m['stock'],
                    'stock_min'   => intval($m['stock'] * 0.1),
                    'prix_achat'  => $m['p_a'],
                    'prix_vente'  => $m['p_v'],
                    'unite_id'    => $unites[$m['unite']] ?? ($unites['comprimé'] ?? 1),
                    'famille_id'  => $familles[$m['famille']] ?? ($familles['Antibiotiques'] ?? 1),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            );
        }
    }
}