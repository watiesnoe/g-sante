<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProtocoleTraitementsSeeder extends Seeder
{
    /**
     * Peuple les protocoles de traitement pour les maladies additionnelles.
     * Doit être exécuté APRÈS PathologiesSeeder et MaladieSymptomeSeeder.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Récupère les maladies déjà existantes par nom
        $maladies = DB::table('maladies')->pluck('id', 'nom');

        $protocoles = [
            [
                'maladie'   => 'Paludisme',
                'titre'     => 'Protocole Paludisme - Première ligne adulte',
                'signes'    => 'Fièvre > 38.5°C, frissons, sueurs, céphalées, arthralgies, TDR positif',
                'diagnostics' => 'Goutte épaisse (GE), TDR Paludisme, Frottis sanguin',
                'germes_nourrisson' => 'Plasmodium falciparum (formes sévères chez nourrisson)',
                'germes_adulte' => 'Plasmodium falciparum (95% des cas en Afrique subsaharienne)',
                'remarques' => 'Repos strict, hydratation abondante. Consulter si fièvre > 72h ou signes de gravité.',
            ],
            [
                'maladie'   => 'Grippe',
                'titre'     => 'Protocole Grippe Saisonnière',
                'signes'    => 'Fièvre brutale > 39°C, courbatures, céphalées, rhinorrhée, toux sèche',
                'diagnostics' => 'Clinique, test rapide grippe (PCR nasopharyngé si besoin)',
                'germes_nourrisson' => 'Influenza A/H1N1, H3N2, Influenza B',
                'germes_adulte' => 'Influenza A/H1N1, H3N2, Influenza B',
                'remarques' => 'Antiviral si < 48h du début, sujet à risque ou forme grave. Éviter l\'Aspirine chez l\'enfant (risque de Reye).',
            ],
            [
                'maladie'   => 'Gastro-entérite',
                'titre'     => 'Protocole GEA - Réhydratation & Traitement',
                'signes'    => 'Diarrhées aiguës, vomissements, nausées, douleurs abdominales, fièvre possible',
                'diagnostics' => 'Clinique, coproculture si formes prolongées, examen parasitologique des selles',
                'germes_nourrisson' => 'Rotavirus, E. coli entéropathogènes, Campylobacter',
                'germes_adulte' => 'Norovirus, Salmonella, Shigella, Campylobacter, giardiase',
                'remarques' => 'Réhydratation orale (SRO) en première intention. Antibiotiques uniquement si bactérie identifiée ou signe de gravité.',
            ],
            [
                'maladie'   => 'Méningites',
                'titre'     => 'Protocole Méningites Bactériennes',
                'signes'    => 'Fièvre, céphalées intenses, photophobie, raideur de la nuque, purpura, altération conscience',
                'diagnostics' => 'Ponction lombaire (LCR purulent), hémocultures, PCR, bandelette urinaire',
                'germes_nourrisson' => 'Streptocoque B, E. coli K1, Listeria monocytogenes',
                'germes_adulte' => 'Streptococcus pneumoniae (pneumocoque), Neisseria meningitidis (méningocoque)',
                'remarques' => 'Urgence médicale absolue. Antibiothérapie en urgence avant le résultat du LCR si tableau évocateur.',
            ],
            [
                'maladie'   => 'Paludisme simple',
                'titre'     => 'Protocole Standard Paludisme Non Compliqué',
                'signes'    => 'Fièvre, frissons, céphalées, vomissements, TDR positif sans signes de gravité',
                'diagnostics' => 'TDR positif, Goutte épaisse',
                'germes_nourrisson' => 'Plasmodium falciparum',
                'germes_adulte' => 'Plasmodium falciparum',
                'remarques' => 'Surveillance à J3 si persistance de la fièvre. CTA en première ligne selon les recommandations nationales.',
            ],
        ];

        foreach ($protocoles as $p) {
            if (!isset($maladies[$p['maladie']])) {
                // Créer la maladie si elle n'existe pas
                $maladieId = DB::table('maladies')->insertGetId([
                    'nom' => $p['maladie'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                $maladieId = $maladies[$p['maladie']];
            }

            DB::table('protocole_traitements')->updateOrInsert(
                ['maladie_id' => $maladieId],
                [
                    'titre'            => $p['titre'],
                    'signes'           => $p['signes'],
                    'diagnostics'      => $p['diagnostics'],
                    'germes_nourrisson' => $p['germes_nourrisson'],
                    'germes_adulte'    => $p['germes_adulte'],
                    'remarques'        => $p['remarques'],
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]
            );
        }
    }
}
