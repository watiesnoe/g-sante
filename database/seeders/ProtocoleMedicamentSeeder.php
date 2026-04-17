<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProtocoleMedicamentSeeder extends Seeder
{
    /**
     * Lie les médicaments aux protocoles avec posologies détaillées.
     * Doit être exécuté APRÈS PathologiesSeeder et ProtocoleTraitementsSeeder.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Récupère les IDs nécessaires
        $protocoles  = DB::table('protocole_traitements')->pluck('id', 'titre');
        $medicaments = DB::table('medicaments')->pluck('id', 'nom');

        $liens = [
            // --- PALUDISME SIMPLE ---
            [
                'protocole'   => 'Protocole Standard Paludisme Non Compliqué',
                'medicament'  => 'Artéméther + Luméfantrine (Coartem)',
                'type'        => 'principal',
                'posologie'   => '4 cp 2x/j à H0, H8, H24, H36, H48, H60',
                'duree'       => '3 jours',
            ],
            [
                'protocole'   => 'Protocole Standard Paludisme Non Compliqué',
                'medicament'  => 'Paracétamol 500mg',
                'type'        => 'adjuvant',
                'posologie'   => '1g 3x/j si fièvre > 38.5°C',
                'duree'       => '3 jours',
            ],

            // --- PALUDISME GRAVE ---
            [
                'protocole'   => 'Hospitalisation Palu Grave',
                'medicament'  => 'Artésunate Injectable',
                'type'        => 'principal',
                'posologie'   => '2.4 mg/kg IV à H0, H12, H24 puis toutes les 24h',
                'duree'       => 'Selon évolution clinique',
            ],
            [
                'protocole'   => 'Hospitalisation Palu Grave',
                'medicament'  => 'Quinine 600mg',
                'type'        => 'relais',
                'posologie'   => '8 mg/kg IV/8h en perfusion lente (relais si artésunate indisponible)',
                'duree'       => '7 jours',
            ],

            // --- PNEUMONIE ---
            [
                'protocole'   => 'Traitement Pneumonie Adulte',
                'medicament'  => 'Amoxicilline + Acide Clavulanique (Augmentin)',
                'type'        => 'principal',
                'posologie'   => '1g (875/125mg) 2x/j',
                'duree'       => '7 jours',
            ],
            [
                'protocole'   => 'Traitement Pneumonie Adulte',
                'medicament'  => 'Azithromycine 500mg',
                'type'        => 'adjuvant',
                'posologie'   => '500mg 1x/j (atypiques)',
                'duree'       => '3 jours',
            ],

            // --- INFECTION URINAIRE ---
            [
                'protocole'   => 'Traitement Minute Cystite',
                'medicament'  => 'Ciprofloxacine 500mg',
                'type'        => 'principal',
                'posologie'   => '500mg 2x/j',
                'duree'       => '5-7 jours',
            ],

            // --- IST GONOCOCCIE ---
            [
                'protocole'   => 'Protocole National IST',
                'medicament'  => 'Ceftriaxone 1g Injectable',
                'type'        => 'principal',
                'posologie'   => '1g IM dose unique',
                'duree'       => '1 jour',
            ],
            [
                'protocole'   => 'Protocole National IST',
                'medicament'  => 'Azithromycine 500mg',
                'type'        => 'assos',
                'posologie'   => '1g PO dose unique (co-infection Chlamydia)',
                'duree'       => '1 jour',
            ],

            // --- GASTRO-ENTÉRITE ---
            [
                'protocole'   => 'Réhydratation & Anti-biothérapie GEA',
                'medicament'  => 'Métronidazole 500mg',
                'type'        => 'principal',
                'posologie'   => '500mg 3x/j',
                'duree'       => '5-7 jours',
            ],
            [
                'protocole'   => 'Réhydratation & Anti-biothérapie GEA',
                'medicament'  => 'Paracétamol 500mg',
                'type'        => 'adjuvant',
                'posologie'   => '1g 3x/j si fièvre ou douleurs abdominales',
                'duree'       => '3 jours',
            ],

            // --- ANÉMIE ---
            [
                'protocole'   => 'Supplémentation Martiale',
                'medicament'  => 'Fumarate de Fer',
                'type'        => 'principal',
                'posologie'   => '200mg 1x/j à jeun ou entre les repas',
                'duree'       => '3 mois',
            ],
        ];

        foreach ($liens as $l) {
            if (!isset($protocoles[$l['protocole']]) || !isset($medicaments[$l['medicament']])) {
                // Lien ignoré si données de base absentes
                continue;
            }

            DB::table('protocole_medicament')->updateOrInsert(
                [
                    'protocole_id'  => $protocoles[$l['protocole']],
                    'medicament_id' => $medicaments[$l['medicament']],
                ],
                [
                    'type'      => $l['type'],
                    'posologie' => $l['posologie'],
                    'duree'     => $l['duree'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
