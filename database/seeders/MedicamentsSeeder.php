<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Medicament;

class MedicamentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medicaments = [
            [
                "nom" => "Paracétamol 500mg",
                "description" => "Antalgique et antipyrétique utilisé contre la douleur et la fièvre.",
                "stock" => 200,
                "stock_min" => 20,
                "prix_achat" => 0.05,
                "prix_vente" => 0.10,
                "unite_id" => 1,   // ⚠️ adapter selon ta table unites
                "famille_id" => 1, // ⚠️ adapter selon ta table familles
            ],
            [
                "nom" => "Amoxicilline 500mg",
                "description" => "Antibiotique de la famille des pénicillines.",
                "stock" => 150,
                "stock_min" => 15,
                "prix_achat" => 0.12,
                "prix_vente" => 0.25,
                "unite_id" => 1,
                "famille_id" => 2,
            ],
            [
                "nom" => "Ibuprofène 400mg",
                "description" => "Anti-inflammatoire non stéroïdien (AINS).",
                "stock" => 180,
                "stock_min" => 20,
                "prix_achat" => 0.08,
                "prix_vente" => 0.20,
                "unite_id" => 1,
                "famille_id" => 3,
            ],
            [
                "nom" => "Metformine 850mg",
                "description" => "Traitement du diabète de type 2.",
                "stock" => 120,
                "stock_min" => 10,
                "prix_achat" => 0.15,
                "prix_vente" => 0.35,
                "unite_id" => 1,
                "famille_id" => 4,
            ],
            [
                "nom" => "Oméprazole 20mg",
                "description" => "Inhibiteur de la pompe à protons, traitement des reflux gastriques.",
                "stock" => 90,
                "stock_min" => 10,
                "prix_achat" => 0.18,
                "prix_vente" => 0.40,
                "unite_id" => 1,
                "famille_id" => 5,
            ],
            [
                "nom" => "Salbutamol Inhalateur",
                "description" => "Bronchodilatateur utilisé dans l’asthme.",
                "stock" => 60,
                "stock_min" => 5,
                "prix_achat" => 2.00,
                "prix_vente" => 3.50,
                "unite_id" => 2,
                "famille_id" => 6,
            ],
            [
                "nom" => "Ciprofloxacine 500mg",
                "description" => "Antibiotique de la famille des fluoroquinolones.",
                "stock" => 70,
                "stock_min" => 7,
                "prix_achat" => 0.25,
                "prix_vente" => 0.50,
                "unite_id" => 1,
                "famille_id" => 2,
            ],
            [
                "nom" => "Furosémide 40mg",
                "description" => "Diurétique utilisé dans l’hypertension et l’insuffisance cardiaque.",
                "stock" => 80,
                "stock_min" => 10,
                "prix_achat" => 0.10,
                "prix_vente" => 0.25,
                "unite_id" => 1,
                "famille_id" => 7,
            ],
            [
                "nom" => "Loratadine 10mg",
                "description" => "Antihistaminique utilisé contre les allergies.",
                "stock" => 100,
                "stock_min" => 15,
                "prix_achat" => 0.07,
                "prix_vente" => 0.15,
                "unite_id" => 1,
                "famille_id" => 8,
            ],
            [
                "nom" => "Vitamine C 500mg",
                "description" => "Complément vitaminique, stimulant immunitaire.",
                "stock" => 250,
                "stock_min" => 30,
                "prix_achat" => 0.03,
                "prix_vente" => 0.08,
                "unite_id" => 1,
                "famille_id" => 9,
            ]
        ];

        foreach ($medicaments as $medicament) {
            Medicament::create($medicament);
        }
    }
}
