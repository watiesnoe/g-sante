<?php

namespace Database\Seeders;
use Illuminate\Support\Str;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FamilleSeeder extends Seeder
{
    public function run(): void
    {
        $familles = [
            'Antibiotiques',
            'Antalgiques',
            'Anti-inflammatoires',
            'Antipyrétiques',
            'Antihistaminiques',
            'Antipaludiques',
            'Antiviraux',
            'Antifongiques',
            'Anesthésiques',
            'Antihypertenseurs',
            'Diurétiques',
            'Antidiabétiques',
            'Vitamines',
            'Vaccins',
            'Antidouleurs',
            'AINS',
            'Corticoïdes',
            'Antipaludéens',
            'Antiacides',
            'Anticonvulsivants',
            'Antidépresseurs',
            'Antiparasitaires',
            'Antipsychotiques',
            'Antispasmodiques',
            'Bronchodilatateurs',
        ];

        foreach ($familles as $famille) {
            DB::table('familles')->updateOrInsert(
                ['nom' => $famille],
                ['uuid' => (string) Str::uuid(), 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
