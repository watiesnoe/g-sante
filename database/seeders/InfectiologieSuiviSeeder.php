<?php

namespace Database\Seeders;

use App\Models\Consultation;
use App\Models\Maladie;
use App\Models\Patient;
use App\Models\ProtocoleTraitement;
use App\Models\RendezVous;
use App\Models\SuiviTraitement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Données de démonstration pour /infectiologie/suivi-traitements.
 * Couvre tous les statuts : Guéri, En cours (évalué ou non), Perdu de vue.
 * Chaque consultation renseigne à la fois consultations.maladie_id et le pivot
 * consultation_maladie (la page lit le pivot).
 */
class InfectiologieSuiviSeeder extends Seeder
{
    public function run(): void
    {
        $medecin = User::role('medecin')->first() ?? User::first();
        if (!$medecin) {
            $this->command?->warn('Aucun utilisateur : exécutez UserSeeder avant.');
            return;
        }

        $maladies = [
            'Paludisme' => ['Paludisme simple - Artémisinine', 'Artéméther-Luméfantrine', '2 comprimés x2/jour pendant 3 jours'],
            'Tuberculose pulmonaire' => ['Tuberculose - Schéma 2RHZE/4RH', 'Rifampicine-Isoniazide-Pyrazinamide-Éthambutol', '1 prise quotidienne pendant 6 mois'],
            'Fièvre typhoïde' => ['Fièvre typhoïde - Ciprofloxacine', 'Ciprofloxacine', '500 mg x2/jour pendant 7 jours'],
            'Pneumonie' => ['Pneumonie - Amoxicilline', 'Amoxicilline', '1 g x3/jour pendant 7 jours'],
        ];

        $m = [];
        foreach ($maladies as $nom => [$titre, $traitement, $posologie]) {
            $m[$nom] = Maladie::firstOrCreate(['nom' => $nom], ['description' => $nom . ' (données de démonstration)']);
            ProtocoleTraitement::firstOrCreate(
                ['maladie_id' => $m[$nom]->id],
                ['titre' => $titre, 'traitement_principal' => $traitement, 'posologie_principale' => $posologie]
            );
        }

        // [nom, prénom, genre, âge, téléphone, maladie, consultations (jours passés), suivis [jours passés => évolution],
        //  rdv réalisés (jours passés), rdv prévus (jours à venir)]
        $cas = [
            ['Sanogo',  'Mamadou', 'M', 34, '70020001', 'Paludisme',             [20],     [12 => 'Amélioration', 4 => 'Guérison'], [], []],
            ['Haidara', 'Boubacar', 'M', 41, '70020002', 'Tuberculose pulmonaire', [60, 30], [15 => 'Amélioration'],                  [], [5]],
            ['Doumbia', 'Fanta',   'F', 27, '70020003', 'Fièvre typhoïde',       [70],     [60 => 'Stagnation'],                    [], []],
            ['Maiga',   'Adama',   'M', 8,  '70020004', 'Pneumonie',             [3],      [],                                      [], [4]],
            ['Kone',    'Awa',     'F', 52, '70020005', 'Paludisme',             [10],     [2 => 'Aggravation'],                    [6], []],
            ['Sylla',   'Ousmane', 'M', 47, '70020006', 'Tuberculose pulmonaire', [75],     [40 => 'Amélioration'],                  [], []],
        ];

        foreach ($cas as [$nom, $prenom, $genre, $age, $tel, $maladie, $consultJours, $suivis, $rdvRealises, $rdvPrevus]) {
            $patient = Patient::firstOrCreate(
                ['telephone' => $tel],
                ['nom' => $nom, 'prenom' => $prenom, 'genre' => $genre, 'age' => $age, 'adresse' => 'Bamako']
            );

            if (Consultation::where('patient_id', $patient->id)->exists()) {
                continue;
            }

            $derniere = null;
            foreach ($consultJours as $jours) {
                $derniere = Consultation::create([
                    'patient_id' => $patient->id,
                    'medecin_id' => $medecin->id,
                    'date_consultation' => Carbon::now()->subDays($jours),
                    'motif' => 'Suivi ' . $maladie,
                    'diagnostic' => $maladie,
                    'maladie_id' => $m[$maladie]->id,
                    'temperature' => 38.5,
                    'tension' => '120/80',
                ]);
                $derniere->maladies()->sync([$m[$maladie]->id]);
            }

            foreach ($suivis as $jours => $evolution) {
                SuiviTraitement::create([
                    'consultation_id' => $derniere->id,
                    'date_suivi' => Carbon::now()->subDays($jours)->toDateString(),
                    'evolution' => $evolution,
                    'observations' => 'Évolution : ' . $evolution,
                    'temperature' => $evolution === 'Aggravation' ? '39.2' : '37.0',
                    'tension' => '120/80',
                ]);
            }

            foreach ($rdvRealises as $jours) {
                RendezVous::create([
                    'consultation_id' => $derniere->id,
                    'patient_id' => $patient->id,
                    'medecin_id' => $medecin->id,
                    'date_heure' => Carbon::now()->subDays($jours)->setTime(9, 0),
                    'motif' => 'Contrôle de traitement',
                    'resultat' => 'Patient revu, traitement poursuivi.',
                    'statut' => 'realise',
                ]);
            }

            foreach ($rdvPrevus as $jours) {
                RendezVous::create([
                    'consultation_id' => $derniere->id,
                    'patient_id' => $patient->id,
                    'medecin_id' => $medecin->id,
                    'date_heure' => Carbon::now()->addDays($jours)->setTime(9, 0),
                    'motif' => 'Contrôle de traitement',
                    'statut' => 'prevu',
                ]);
            }
        }
    }
}
