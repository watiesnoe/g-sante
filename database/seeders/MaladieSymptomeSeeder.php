<?php

namespace Database\Seeders;
use Illuminate\Support\Str;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaladieSymptomeSeeder extends Seeder
{
    /**
     * Crée les maladies et symptômes de base et les lie.
     * Les données avancées (protocoles, médicaments) sont gérées par PathologiesSeeder et InfectiologieSeeder.
     */
    public function run()
    {
        $now = now();

        // --- SYMPTÔMES ---
        $symptomes = [
            ['nom' => 'Fièvre',             'description' => 'Élévation de la température corporelle au-dessus de 38°C'],
            ['nom' => 'Fatigue',            'description' => 'Sensation de faiblesse et épuisement généralisé'],
            ['nom' => 'Céphalées',          'description' => 'Douleurs ou maux de tête'],
            ['nom' => 'Nausées',            'description' => 'Envie de vomir, malaise gastrique'],
            ['nom' => 'Vomissements',       'description' => 'Expulsion forcée du contenu gastrique'],
            ['nom' => 'Toux',               'description' => 'Expulsion brusque d\'air des voies respiratoires'],
            ['nom' => 'Frissons',           'description' => 'Tremblements involontaires accompagnant la fièvre'],
            ['nom' => 'Diarrhée',           'description' => 'Selles liquides fréquentes'],
            ['nom' => 'Courbatures',        'description' => 'Douleurs musculaires diffuses'],
            ['nom' => 'Perte d\'appétit',  'description' => 'Diminution ou absence de l\'envie de manger'],
            ['nom' => 'Dyspnée',            'description' => 'Difficulté ou gêne respiratoire'],
            ['nom' => 'Douleur thoracique', 'description' => 'Douleur ou oppression au niveau de la poitrine'],
            ['nom' => 'Sueurs nocturnes',   'description' => 'Transpiration excessive pendant le sommeil'],
            ['nom' => 'Amaigrissement',     'description' => 'Perte de poids involontaire'],
            ['nom' => 'Ictère',             'description' => 'Jaunissement de la peau et des muqueuses'],
            ['nom' => 'Prurit',             'description' => 'Démangeaisons cutanées'],
            ['nom' => 'Éruption cutanée',   'description' => 'Rougeurs ou boutons sur la peau'],
            ['nom' => 'Hémoptysies',        'description' => 'Crachats de sang d\'origine pulmonaire'],
            ['nom' => 'Raideur de la nuque','description' => 'Limitation douloureuse des mouvements du cou'],
            ['nom' => 'Photophobie',        'description' => 'Hypersensibilité à la lumière'],
        ];

        foreach ($symptomes as $s) {
            DB::table('symptomes')->updateOrInsert(
                ['nom' => $s['nom']],
                ['description' => $s['description'], 'uuid' => (string) Str::uuid(), 'created_at' => $now, 'uuid' => (string) Str::uuid(), 'updated_at' => $now]
            );
        }

        $sId = DB::table('symptomes')->pluck('id', 'nom');

        // --- MALADIES COURANTES ---
        $maladies = [
            ['nom' => 'Paludisme',      'description' => 'Maladie parasitaire transmise par le moustique Anophèle.'],
            ['nom' => 'Grippe',         'description' => 'Infection virale respiratoire saisonnière.'],
            ['nom' => 'Gastro-entérite','description' => 'Inflammation de l\'estomac et des intestins.'],
            ['nom' => 'Méningites',     'description' => 'Inflammation des méninges d\'origine bactérienne ou virale.'],
            ['nom' => 'Tuberculose pulmonaire', 'description' => 'Infection bactérienne chronique des poumons.'],
            ['nom' => 'Candidose systémique',   'description' => 'Infection fongique généralisée par Candida.'],
        ];

        foreach ($maladies as $m) {
            DB::table('maladies')->updateOrInsert(
                ['nom' => $m['nom']],
                ['description' => $m['description'] ?? null, 'uuid' => (string) Str::uuid(), 'created_at' => $now, 'uuid' => (string) Str::uuid(), 'updated_at' => $now]
            );
        }

        $mId = DB::table('maladies')->pluck('id', 'nom');

        // --- LIAISONS MALADIE ↔ SYMPTÔMES ---
        $liens = [
            'Paludisme'            => ['Fièvre', 'Frissons', 'Céphalées', 'Courbatures', 'Fatigue', 'Nausées', 'Vomissements'],
            'Grippe'               => ['Fièvre', 'Fatigue', 'Céphalées', 'Toux', 'Courbatures'],
            'Gastro-entérite'      => ['Fièvre', 'Nausées', 'Vomissements', 'Diarrhée', 'Douleur thoracique', 'Perte d\'appétit'],
            'Méningites'           => ['Fièvre', 'Céphalées', 'Raideur de la nuque', 'Photophobie', 'Vomissements', 'Fatigue'],
            'Tuberculose pulmonaire' => ['Toux', 'Hémoptysies', 'Sueurs nocturnes', 'Amaigrissement', 'Fièvre', 'Fatigue'],
            'Candidose systémique' => ['Fièvre', 'Fatigue', 'Prurit', 'Éruption cutanée'],
        ];

        foreach ($liens as $maladie => $symptomesList) {
            if (!isset($mId[$maladie])) continue;

            foreach ($symptomesList as $symptomNom) {
                if (!isset($sId[$symptomNom])) continue;

                DB::table('maladie_symptome')->updateOrInsert([
                    'maladie_id' => $mId[$maladie],
                    'symptome_id' => $sId[$symptomNom],
                ]);
            }
        }
    }
}
