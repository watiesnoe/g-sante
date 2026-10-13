<?php

namespace Database\Seeders;

use App\Models\ConsultationPrenatale;
use App\Models\Grossesse;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MaternitySeeder extends Seeder
{
    public function run(): void
    {
        $patientes = [
            ['nom' => 'Traore',   'prenom' => 'Aminata', 'age' => 24, 'telephone' => '70010001', 'groupe_sanguin' => 'O+', 'adresse' => 'Hamdallaye, Bamako'],
            ['nom' => 'Coulibaly', 'prenom' => 'Kadiatou', 'age' => 31, 'telephone' => '70010002', 'groupe_sanguin' => 'A+', 'adresse' => 'Sogoniko, Bamako'],
            ['nom' => 'Diarra',   'prenom' => 'Rokia',   'age' => 19, 'telephone' => '70010003', 'groupe_sanguin' => 'B+', 'adresse' => 'Yirimadio, Bamako'],
            ['nom' => 'Keita',    'prenom' => 'Assetou', 'age' => 36, 'telephone' => '70010004', 'groupe_sanguin' => 'O-', 'adresse' => 'Niamakoro, Bamako'],
        ];

        // semaines d'aménorrhée, gestité, parité, statut, issue, antécédents
        $cas = [
            [20, 1, 0, 'En cours',    null, null],
            [32, 3, 2, 'En cours',    null, 'Hypertension artérielle gravidique lors de la grossesse précédente'],
            [8,  2, 1, 'En cours',    null, 'Diabète gestationnel antérieur'],
            [41, 4, 3, 'Terminée',    'Accouchement par voie basse, nouveau-né vivant', 'Césarienne antérieure'],
        ];

        foreach ($patientes as $i => $data) {
            $patient = Patient::firstOrCreate(
                ['telephone' => $data['telephone']],
                $data + ['genre' => 'F']
            );

            if (Grossesse::where('patient_id', $patient->id)->exists()) {
                continue;
            }

            [$sa, $gestite, $parite, $statut, $issue, $antecedents] = $cas[$i];

            $ddr = Carbon::today()->subWeeks($sa);

            $grossesse = Grossesse::create([
                'patient_id' => $patient->id,
                'ddr' => $ddr,
                'dpa' => $ddr->copy()->addDays(280),
                'gestite' => $gestite,
                'parite' => $parite,
                'antecedents_particuliers' => $antecedents,
                'statut' => $statut,
                'issue' => $issue,
                'date_fin' => $statut === 'Terminée' ? Carbon::today()->subDays(3) : null,
            ]);

            // Une CPN environ toutes les 4 semaines à partir de 8 SA (max 8 CPN)
            $numero = 1;
            for ($semaine = 8; $semaine <= min($sa, 40) && $numero <= 8; $semaine += 4) {
                ConsultationPrenatale::create([
                    'grossesse_id' => $grossesse->id,
                    'numero_cpn' => $numero,
                    'date_cpn' => $ddr->copy()->addWeeks($semaine),
                    'poids' => 55 + $numero * 1.5,
                    'tension' => '110/70',
                    'hauteur_uterine' => $semaine >= 16 ? $semaine : null,
                    'bcf' => $semaine >= 12 ? '140 bpm' : null,
                    'observations' => 'RAS',
                    'traitement_recu' => 'Fer + Acide folique',
                ]);
                $numero++;
            }
        }
    }
}
