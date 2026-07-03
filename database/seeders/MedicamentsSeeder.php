<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Medicament;
use App\Models\Unite;
use App\Models\Famille;

/**
 * Liste des médicaments essentiels cités dans le Guide clinique et thérapeutique MSF
 * (2024). stock / stock_min / prix sont initialisés à des valeurs de démonstration,
 * à ajuster selon l'inventaire réel de la pharmacie.
 */
class MedicamentsSeeder extends Seeder
{
    public function run(): void
    {
        $medicaments = [
            ['nom' => 'Paracétamol', 'unite' => 'Comprimé', 'famille' => 'Antalgique / Antipyrétique'],
            ['nom' => 'Ibuprofène', 'unite' => 'Comprimé', 'famille' => 'Anti-inflammatoire (AINS)'],
            ['nom' => 'Amoxicilline', 'unite' => 'Gélule', 'famille' => 'Antibiotique'],
            ['nom' => 'Amoxicilline/Acide clavulanique (co-amoxiclav)', 'unite' => 'Comprimé', 'famille' => 'Antibiotique'],
            ['nom' => 'Ceftriaxone', 'unite' => 'Flacon', 'famille' => 'Antibiotique'],
            ['nom' => 'Cloxacilline', 'unite' => 'Flacon', 'famille' => 'Antibiotique'],
            ['nom' => 'Ampicilline', 'unite' => 'Flacon', 'famille' => 'Antibiotique'],
            ['nom' => 'Azithromycine', 'unite' => 'Comprimé', 'famille' => 'Antibiotique'],
            ['nom' => 'Ciprofloxacine', 'unite' => 'Comprimé', 'famille' => 'Antibiotique'],
            ['nom' => 'Doxycycline', 'unite' => 'Comprimé', 'famille' => 'Antibiotique'],
            ['nom' => 'Métronidazole', 'unite' => 'Comprimé', 'famille' => 'Antiparasitaire'],
            ['nom' => 'Gentamicine', 'unite' => 'Ampoule', 'famille' => 'Antibiotique'],
            ['nom' => 'Co-trimoxazole (sulfaméthoxazole + triméthoprime)', 'unite' => 'Comprimé', 'famille' => 'Antibiotique'],
            ['nom' => 'Benzathine benzylpénicilline', 'unite' => 'Flacon', 'famille' => 'Antibiotique'],
            ['nom' => 'Chloroquine', 'unite' => 'Comprimé', 'famille' => 'Antipaludique'],
            ['nom' => 'Artéméther/Luméfantrine (AL)', 'unite' => 'Comprimé', 'famille' => 'Antipaludique'],
            ['nom' => 'Artésunate', 'unite' => 'Flacon', 'famille' => 'Antipaludique'],
            ['nom' => 'Quinine', 'unite' => 'Comprimé', 'famille' => 'Antipaludique'],
            ['nom' => 'Primaquine', 'unite' => 'Comprimé', 'famille' => 'Antipaludique'],
            ['nom' => 'Sulfate de zinc', 'unite' => 'Comprimé', 'famille' => 'Vitamine / Complément'],
            ['nom' => 'Sels de réhydratation orale (SRO)', 'unite' => 'Sachet', 'famille' => 'Solution de réhydratation / Perfusion'],
            ['nom' => 'Rétinol (vitamine A)', 'unite' => 'Capsule', 'famille' => 'Vitamine / Complément'],
            ['nom' => 'Sulfate ferreux + acide folique', 'unite' => 'Comprimé', 'famille' => 'Vitamine / Complément'],
            ['nom' => 'Mébendazole', 'unite' => 'Comprimé', 'famille' => 'Antiparasitaire'],
            ['nom' => 'Albendazole', 'unite' => 'Comprimé', 'famille' => 'Antiparasitaire'],
            ['nom' => 'Ivermectine', 'unite' => 'Comprimé', 'famille' => 'Antiparasitaire'],
            ['nom' => 'Praziquantel', 'unite' => 'Comprimé', 'famille' => 'Antiparasitaire'],
            ['nom' => 'Perméthrine', 'unite' => 'Tube', 'famille' => 'Antiparasitaire'],
            ['nom' => 'Benzoate de benzyle', 'unite' => 'Flacon', 'famille' => 'Antiparasitaire'],
            ['nom' => 'Clotrimazole', 'unite' => 'Tube', 'famille' => 'Antifongique'],
            ['nom' => 'Nystatine', 'unite' => 'Flacon', 'famille' => 'Antifongique'],
            ['nom' => 'Fluconazole', 'unite' => 'Gélule', 'famille' => 'Antifongique'],
            ['nom' => 'Aciclovir', 'unite' => 'Comprimé', 'famille' => 'Antiviral'],
            ['nom' => 'Prednisolone', 'unite' => 'Comprimé', 'famille' => 'Corticoïde'],
            ['nom' => 'Dexaméthasone', 'unite' => 'Ampoule', 'famille' => 'Corticoïde'],
            ['nom' => 'Hydrocortisone', 'unite' => 'Flacon', 'famille' => 'Corticoïde'],
            ['nom' => 'Salbutamol', 'unite' => 'Inhalateur', 'famille' => 'Bronchodilatateur'],
            ['nom' => 'Épinéphrine (adrénaline)', 'unite' => 'Ampoule', 'famille' => 'Cardio-vasculaire / Urgence'],
            ['nom' => 'Diazépam', 'unite' => 'Ampoule', 'famille' => 'Anxiolytique / Sédatif'],
            ['nom' => 'Phénobarbital', 'unite' => 'Comprimé', 'famille' => 'Anticonvulsivant'],
            ['nom' => 'Hydroxyzine', 'unite' => 'Comprimé', 'famille' => 'Antihistaminique'],
            ['nom' => 'Prométhazine', 'unite' => 'Comprimé', 'famille' => 'Antihistaminique'],
            ['nom' => 'Chlorphéniramine', 'unite' => 'Comprimé', 'famille' => 'Antihistaminique'],
            ['nom' => 'Fluoxétine', 'unite' => 'Gélule', 'famille' => 'Antidépresseur'],
            ['nom' => 'Halopéridol', 'unite' => 'Ampoule', 'famille' => 'Antipsychotique'],
            ['nom' => 'Vaccin antitétanique (VAT)', 'unite' => 'Flacon', 'famille' => 'Vaccin'],
            ['nom' => 'Sérum antitétanique (immunoglobulines)', 'unite' => 'Flacon', 'famille' => 'Immunoglobuline'],
            ['nom' => 'Isoniazide', 'unite' => 'Comprimé', 'famille' => 'Antituberculeux'],
            ['nom' => 'Rifampicine', 'unite' => 'Comprimé', 'famille' => 'Antituberculeux'],
            ['nom' => 'Pyrazinamide', 'unite' => 'Comprimé', 'famille' => 'Antituberculeux'],
            ['nom' => 'Éthambutol', 'unite' => 'Comprimé', 'famille' => 'Antituberculeux'],
            ['nom' => 'Tétracycline ophtalmique 1%', 'unite' => 'Tube', 'famille' => 'Antiseptique / Dermatologique'],
            ['nom' => 'Polyvidone iodée', 'unite' => 'Flacon', 'famille' => 'Antiseptique / Dermatologique'],
            ['nom' => 'Chlorure de sodium 0,9%', 'unite' => 'Flacon', 'famille' => 'Solution de réhydratation / Perfusion'],
            ['nom' => 'Insuline', 'unite' => 'Flacon', 'famille' => 'Antidiabétique'],
            ['nom' => 'Metformine', 'unite' => 'Comprimé', 'famille' => 'Antidiabétique'],
            ['nom' => 'Énalapril', 'unite' => 'Comprimé', 'famille' => 'Antihypertenseur'],
            ['nom' => 'Furosémide', 'unite' => 'Comprimé', 'famille' => 'Diurétique'],
        ];

        foreach ($medicaments as $m) {
            $unite = Unite::where('nom', $m['unite'])->first();
            $famille = Famille::where('nom', $m['famille'])->first();

            if (!$unite || !$famille) {
                continue; // sécurité si UnitesSeeder / FamillesSeeder n'ont pas été lancés avant
            }

            Medicament::firstOrCreate(
                ['nom' => $m['nom']],
                [
                    'uuid' => (string) Str::uuid(),
                    'description' => null,
                    'stock' => 100,
                    'stock_min' => 10,
                    'prix_achat' => 0,
                    'prix_vente' => 0,
                    'unite_id' => $unite->id,
                    'famille_id' => $famille->id,
                ]
            );
        }
    }
}
