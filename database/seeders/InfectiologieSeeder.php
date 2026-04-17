<?php

namespace Database\Seeders;

use App\Models\Maladie;
use App\Models\ProtocoleTraitement;
use Illuminate\Database\Seeder;

class InfectiologieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Paludisme
        $paludisme = Maladie::where('nom', 'Paludisme')->first();
        if ($paludisme) {
            ProtocoleTraitement::updateOrCreate(
                ['maladie_id' => $paludisme->id, 'titre' => 'Traitement de première intention (CTA)'],
                [
                    'signes' => 'Fièvre, frissons, sueurs, céphalées, fatigue',
                    'diagnostics' => 'Goutte épaisse (GE), TDR Paludisme',
                    'traitement_principal' => 'Artéméther + Luméfantrine (CTA)',
                    'posologie_principale' => '1 comprimé (20/120mg) pour un enfant de 5-14kg, 2 prises par jour pendant 3 jours.',
                    'remarques' => 'Repos strict et hydratation abondante.'
                ]
            );

            ProtocoleTraitement::updateOrCreate(
                ['maladie_id' => $paludisme->id, 'titre' => 'Traitement de deuxième intention'],
                [
                    'signes' => 'Fièvre persistante, échec de première intention',
                    'diagnostics' => 'GE de contrôle, Test de résistance',
                    'traitement_principal' => 'Quinine',
                    'posologie_principale' => '8mg/kg toutes les 8 heures pendant 7 jours.',
                    'remarques' => 'Surveillance cardiaque requise.'
                ]
            );
        }

        // Méningite (The new case)
        $meningite = Maladie::firstOrCreate(['nom' => 'Méningites'], ['description' => 'Inflammation des méninges']);
        ProtocoleTraitement::updateOrCreate(
            ['maladie_id' => $meningite->id],
            [
                'titre' => 'Protocole Méningites Bactériennes',
                'signes' => 'Fièvre, céphalées intenses, photophobie, raideur de la nuque, purpura',
                'diagnostics' => 'LCR purulent trouble ou clair, ECBC du LCR avec antigènes solubles, PCR sur LCR, hémocultures',
                'germes_nourrisson' => '0 à 3 mois : E coli K1, H influenza b, Streptocoques groupe B, S. pneumoniae, N. meningitidis',
                'germes_adulte' => 'Plus de 3 mois et adulte : S. pneumoniae, N. meningitidis, H. influenza b',
                'traitement_principal' => 'Ceftriaxone',
                'posologie_principale' => 'Adulte : 100 mg/kg/jour max 4g/j en 2 injections IV pendant 7 à 10 jours. Enfant : 100 mg/kg/jour max 2g/j en 2 injections IV pendant 7 à 10 jours',
                'traitement_alternatif' => 'Amoxicilline / Céfotaxime',
                'posologie_alternative' => 'Adulte : 200 mg/kg/jour max 6g/j en 3 injections IV pendant 7 à 10 jours. Enfant : 200 mg/kg/jour max 3g/j en 3 injections IV pendant 7 à 10 jours',
                'remarques' => 'Durée 21 jours si suspicion Streptocoque groupe B. Pénicilline A sous réserve antibiogramme.'
            ]
        );
    }
}
