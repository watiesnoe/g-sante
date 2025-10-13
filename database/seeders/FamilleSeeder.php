<?php

namespace Database\Seeders;

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
        ];

        foreach ($familles as $famille) {
            DB::table('familles')->updateOrInsert(
                ['nom' => $famille],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
