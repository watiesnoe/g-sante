<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceMedical;
use App\Models\Salle;
use App\Models\Lit;
use App\Models\Examen;

class SalleLitExamenSeeder extends Seeder
{
    public function run(): void
    {
        $services = ServiceMedical::all();

        foreach ($services as $service) {

            // Créer 3 salles par service
            for ($i = 1; $i <= 3; $i++) {
                $salle = Salle::create([
                    'nom' => $service->nom . ' - Salle ' . $i,
                    'type' => ['Consultation', 'Hospitalisation', 'Blocoperatoire', 'Laboratoire'][array_rand(['Consultation', 'Hospitalisation', 'Blocoperatoire', 'Laboratoire'])],
                    'service_medical_id' => $service->id,
                    'capacite' => rand(2, 5),
                    'prix' => rand(5000, 25000), // 💰 ajout du prix aléatoire
                ]);

                // Créer les lits pour chaque salle
                for ($j = 1; $j <= $salle->capacite; $j++) {
                    Lit::create([
                        'numero' => $salle->nom . ' - Lit ' . $j,
                        'salle_id' => $salle->id,
                        'statut' => 'Libre',
                    ]);
                }
            }

            // Créer 5 examens par service
            for ($k = 1; $k <= 5; $k++) {
                Examen::create([
                    'nom' => $service->nom . ' Examen ' . $k,
                    'description' => 'Description de l’examen ' . $k . ' pour le service ' . $service->nom,
                    'prix' => rand(5000, 20000),
                    'service_medical_id' => $service->id,
                ]);
            }
        }
    }
}
