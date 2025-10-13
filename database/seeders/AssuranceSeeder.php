<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssuranceSeeder extends Seeder
{
    public function run(): void
    {
        $assurances = [
            ['nom' => 'CNSS', 'telephone' => '0800100100', 'adresse' => 'Dakar', 'taux' => 80],
            ['nom' => 'IPM', 'telephone' => '0800200200', 'adresse' => 'Dakar', 'taux' => 70],
            ['nom' => 'Aucune', 'telephone' => null, 'adresse' => null, 'taux' => 0],
        ];

        foreach ($assurances as $assurance) {
            $assurance['uuid'] = (string) Str::uuid();
            $assurance['created_at'] = now();
            $assurance['updated_at'] = now();
            
            DB::table('assurances')->updateOrInsert(
                ['nom' => $assurance['nom']],
                $assurance
            );
        }
    }
}
