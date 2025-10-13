<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Maladie;
use App\Models\Symptome;

class MaladieSymptomeSeeder extends Seeder
{
    public function run()
    {
        // 1️⃣ Création des symptômes
        $symptomes = [
            ['nom' => 'Fièvre', 'description' => 'Élévation de la température corporelle'],
            ['nom' => 'Fatigue', 'description' => 'Sensation de faiblesse générale'],
            ['nom' => 'Maux de tête', 'description' => 'Douleurs au niveau de la tête'],
            ['nom' => 'Nausée', 'description' => 'Sensation de malaise et envie de vomir'],
            ['nom' => 'Toux', 'description' => 'Expulsion d’air forcée par les voies respiratoires'],
        ];

        foreach ($symptomes as $s) {
            Symptome::firstOrCreate(['nom' => $s['nom']], $s);
        }

        // 2️⃣ Création des maladies
        $maladies = [
            ['nom' => 'Paludisme', 'description' => 'Maladie parasitaire transmise par le moustique'],
            ['nom' => 'Grippe', 'description' => 'Maladie virale saisonnière'],
            ['nom' => 'Gastro-entérite', 'description' => 'Inflammation de l’estomac et des intestins'],
        ];

        foreach ($maladies as $m) {
            Maladie::firstOrCreate(['nom' => $m['nom']], $m);
        }

        // 3️⃣ Lier maladies et symptômes
        $paludisme = Maladie::where('nom', 'Paludisme')->first();
        $grippe = Maladie::where('nom', 'Grippe')->first();
        $gastro = Maladie::where('nom', 'Gastro-entérite')->first();

        $fievre = Symptome::where('nom', 'Fièvre')->first();
        $fatigue = Symptome::where('nom', 'Fatigue')->first();
        $mauxTete = Symptome::where('nom', 'Maux de tête')->first();
        $nausee = Symptome::where('nom', 'Nausée')->first();
        $toux = Symptome::where('nom', 'Toux')->first();

        $paludisme->symptomes()->sync([$fievre->id, $fatigue->id, $mauxTete->id]);
        $grippe->symptomes()->sync([$fievre->id, $fatigue->id, $mauxTete->id, $toux->id]);
        $gastro->symptomes()->sync([$fievre->id, $fatigue->id, $nausee->id]);
    }
}
