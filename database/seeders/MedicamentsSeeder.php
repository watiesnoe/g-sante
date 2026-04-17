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
                "prix_achat" => 250,     // 250 FCFA
                "prix_vente" => 500,     // 500 FCFA
                "unite_id" => 1,
                "famille_id" => 1,
            ],
            [
                "nom" => "Amoxicilline 500mg",
                "description" => "Antibiotique de la famille des pénicillines.",
                "stock" => 150,
                "stock_min" => 15,
                "prix_achat" => 500,     // 500 FCFA
                "prix_vente" => 1000,    // 1 000 FCFA
                "unite_id" => 1,
                "famille_id" => 2,
            ],
            [
                "nom" => "Ibuprofène 400mg",
                "description" => "Anti-inflammatoire non stéroïdien (AINS).",
                "stock" => 180,
                "stock_min" => 20,
                "prix_achat" => 350,     // 350 FCFA
                "prix_vente" => 700,     // 700 FCFA
                "unite_id" => 1,
                "famille_id" => 3,
            ],
            [
                "nom" => "Metformine 850mg",
                "description" => "Traitement du diabète de type 2.",
                "stock" => 120,
                "stock_min" => 10,
                "prix_achat" => 600,     // 600 FCFA
                "prix_vente" => 1200,    // 1 200 FCFA
                "unite_id" => 1,
                "famille_id" => 4,
            ],
            [
                "nom" => "Oméprazole 20mg",
                "description" => "Inhibiteur de la pompe à protons, traitement des reflux gastriques.",
                "stock" => 90,
                "stock_min" => 10,
                "prix_achat" => 700,     // 700 FCFA
                "prix_vente" => 1400,    // 1 400 FCFA
                "unite_id" => 1,
                "famille_id" => 5,
            ],
            [
                "nom" => "Salbutamol Inhalateur",
                "description" => "Bronchodilatateur utilisé dans l’asthme.",
                "stock" => 60,
                "stock_min" => 5,
                "prix_achat" => 8000,    // 8 000 FCFA
                "prix_vente" => 15000,   // 15 000 FCFA
                "unite_id" => 2,
                "famille_id" => 6,
            ],
            [
                "nom" => "Ciprofloxacine 500mg",
                "description" => "Antibiotique de la famille des fluoroquinolones.",
                "stock" => 70,
                "stock_min" => 7,
                "prix_achat" => 800,     // 800 FCFA
                "prix_vente" => 1600,    // 1 600 FCFA
                "unite_id" => 1,
                "famille_id" => 2,
            ],
            [
                "nom" => "Furosémide 40mg",
                "description" => "Diurétique utilisé dans l’hypertension et l’insuffisance cardiaque.",
                "stock" => 80,
                "stock_min" => 10,
                "prix_achat" => 400,     // 400 FCFA
                "prix_vente" => 800,     // 800 FCFA
                "unite_id" => 1,
                "famille_id" => 7,
            ],
            [
                "nom" => "Loratadine 10mg",
                "description" => "Antihistaminique utilisé contre les allergies.",
                "stock" => 100,
                "stock_min" => 15,
                "prix_achat" => 300,     // 300 FCFA
                "prix_vente" => 600,     // 600 FCFA
                "unite_id" => 1,
                "famille_id" => 8,
            ],
            [
                "nom" => "Vitamine C 500mg",
                "description" => "Complément vitaminique, stimulant immunitaire.",
                "stock" => 250,
                "stock_min" => 30,
                "prix_achat" => 150,     // 150 FCFA
                "prix_vente" => 300,     // 300 FCFA
                "unite_id" => 1,
                "famille_id" => 9,
            ],
            [
                "nom" => "Ceftriaxone 1g Injectable",
                "description" => "Antibiotique céphalosporine de 3ème génération.",
                "stock" => 50,
                "stock_min" => 5,
                "prix_achat" => 2500,
                "prix_vente" => 4500,
                "unite_id" => 5, // Ampoule/Flacon
                "famille_id" => 2,
            ],
            [
                "nom" => "Artéméther + Luméfantrine (Coartem)",
                "description" => "Traitement combiné à base d'artémisinine (CTA) contre le paludisme.",
                "stock" => 100,
                "stock_min" => 10,
                "prix_achat" => 3000,
                "prix_vente" => 5500,
                "unite_id" => 1,
                "famille_id" => 2,
            ],
        ];

        foreach ($medicaments as $medicament) {
            Medicament::create($medicament);
        }
    }
}