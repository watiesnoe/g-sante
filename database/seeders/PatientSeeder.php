<?php

namespace Database\Seeders;

use App\Models\Etablissement;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

/**
 * Patients fictifs de démonstration, pour chaque établissement (jamais en production).
 *
 * - Noms et prénoms maliens courants, prénom cohérent avec le sexe ;
 * - pyramide des âges proche d'un CSRéf : beaucoup d'enfants, peu de personnes âgées ;
 * - téléphones commençant par 0 : aucun numéro mobile réel ne peut recevoir de SMS ;
 * - relançable : un patient déjà créé (même téléphone dans l'établissement) n'est pas dupliqué ;
 * - toujours les mêmes patients d'une exécution à l'autre (tirage à graine fixe).
 *
 * Nombre par établissement : variable d'environnement PATIENTS_DEMO (30 par défaut).
 */
class PatientSeeder extends Seeder
{
    private const NOMS = [
        'Traoré', 'Coulibaly', 'Diarra', 'Keïta', 'Konaté', 'Sangaré', 'Diallo', 'Touré', 'Cissé', 'Sidibé',
        'Dembélé', 'Camara', 'Maïga', 'Sissoko', 'Kané', 'Dicko', 'Bagayoko', 'Samaké', 'Doumbia', 'Fofana',
        'Kouyaté', 'Tangara', 'Guindo', 'Ballo', 'Berthé', 'Haïdara', 'Diakité', 'Sacko', 'Kéita', 'Togola',
    ];

    private const PRENOMS = [
        'F' => ['Awa', 'Aminata', 'Fatoumata', 'Mariam', 'Kadiatou', 'Oumou', 'Hawa', 'Assitan', 'Salimata', 'Aïssata',
                'Djeneba', 'Rokia', 'Bintou', 'Korotoumou', 'Nana', 'Fanta', 'Maïmouna', 'Sitan', 'Kadidia', 'Ramata'],
        'M' => ['Moussa', 'Mamadou', 'Ibrahim', 'Seydou', 'Oumar', 'Bakary', 'Abdoulaye', 'Issa', 'Souleymane', 'Adama',
                'Boubacar', 'Modibo', 'Lassana', 'Drissa', 'Youssouf', 'Sékou', 'Amadou', 'Siaka', 'Mahamadou', 'Cheick'],
    ];

    private const QUARTIERS = [
        'Niamakoro', 'Yirimadio', 'Sogoniko', 'Faladié', 'Magnambougou', 'Banankabougou', 'Missabougou', 'Kalaban Coura',
        'Sabalibougou', 'Lafiabougou', 'Hamdallaye', 'Badalabougou', 'Djélibougou', 'Banconi', 'Sébénikoro', 'Daoudabougou',
    ];

    /** Fréquences approximatives en Afrique de l'Ouest (O+ majoritaire). */
    private const GROUPES = ['O+' => 45, 'A+' => 22, 'B+' => 20, 'AB+' => 4, 'O-' => 4, 'A-' => 2, 'B-' => 2, 'AB-' => 1];

    /** Tranches d'âge [min, max] => poids. */
    private const AGES = ['0-4' => 18, '5-14' => 20, '15-24' => 18, '25-44' => 24, '45-64' => 14, '65-85' => 6];

    private const ALLERGIES = ['Pénicilline', 'Sulfamides', 'Aspirine', 'Arachide', 'Chloroquine'];

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('Patients de démonstration : jamais en production.');

            return;
        }

        $nombre = max(1, (int) env('PATIENTS_DEMO', 30));

        // L'audit attribue chaque création à un utilisateur : on agit comme le super-admin
        $superAdmin = User::whereHas('roles', fn($q) => $q->where('name', 'super_admin'))->orderBy('id')->first();
        if ($superAdmin) {
            Auth::setUser($superAdmin);
        }

        foreach (Etablissement::orderBy('id')->get() as $etablissement) {
            mt_srand(1000 + $etablissement->id);
            app()->instance('etablissement.courant', $etablissement);
            try {
                $crees = 0;
                for ($i = 1; $i <= $nombre; $i++) {
                    $donnees = $this->patient($etablissement->id, $i);
                    $patient = Patient::firstOrCreate(['telephone' => $donnees['telephone']], $donnees);
                    $crees += $patient->wasRecentlyCreated ? 1 : 0;
                }
                $this->command?->line("  {$etablissement->nom} : {$crees} patient(s) créé(s), " . ($nombre - $crees) . ' déjà présent(s).');
            } finally {
                Etablissement::oublierCourant();
            }
        }
    }

    private function patient(int $etablissementId, int $rang): array
    {
        $genre = mt_rand(0, 99) < 52 ? 'F' : 'M';
        [$min, $max] = array_map('intval', explode('-', $this->tirage(self::AGES)));
        $age = mt_rand($min, $max);
        $naissance = now()->subYears($age)->subDays(mt_rand(0, 364));

        $quartier = self::QUARTIERS[mt_rand(0, count(self::QUARTIERS) - 1)];
        $provenance = $this->tirage(['domicile' => 80, 'cscom' => 15, 'structure' => 4, 'evacuation' => 1]);

        return [
            'nom'            => self::NOMS[mt_rand(0, count(self::NOMS) - 1)],
            'prenom'         => self::PRENOMS[$genre][mt_rand(0, count(self::PRENOMS[$genre]) - 1)],
            'genre'          => $genre,
            'age'            => $age,
            'date_naissance' => $naissance->toDateString(),
            // 0 + établissement + rang : numéro fictif, unique dans l'établissement
            'telephone'      => sprintf('0%02d%05d', $etablissementId % 100, $rang),
            'adresse'        => $quartier . ', Bamako',
            'groupe_sanguin' => mt_rand(0, 99) < 70 ? $this->tirage(self::GROUPES) : null,
            'allergies'      => mt_rand(0, 99) < 8 ? self::ALLERGIES[mt_rand(0, count(self::ALLERGIES) - 1)] : null,
            'provenance'     => $provenance,
            'cscom_origine'  => $provenance === 'cscom' ? 'CSCom de ' . $quartier : null,
        ];
    }

    /** Tire une clé selon les poids donnés. */
    private function tirage(array $poids): string
    {
        $n = mt_rand(1, array_sum($poids));
        foreach ($poids as $cle => $p) {
            if (($n -= $p) <= 0) {
                return (string) $cle;
            }
        }

        return (string) array_key_last($poids);
    }
}
