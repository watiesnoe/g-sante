<?php


namespace Database\Seeders;
use Illuminate\Support\Str;

use App\Models\Unite;
use Illuminate\Database\Seeder;


class UniteSeeder extends Seeder
{
    public function run(): void
    {
        $unites = [
            // Solides
            'mg', 'g', 'µg', 'UI', 'comprimé', 'capsule',

            // Liquides
            'ml', 'cl', 'l', 'goutte', 'cuillère à café', 'cuillère à soupe','ampoule',

            // Poudres
            'poudre', 'granule', 'sachet', 'dose',

            // Divers
            'patch', 'inhalation', 'puff', 'suppositoire', 'tube', 'spray', 'gélule', 'suspension', 'boite',
            'aérosol', 'collyre', 'crème', 'injection', 'pommade', 'solution buvable'
        ];

        foreach ($unites as $u) {
            Unite::firstOrCreate(['nom' => $u]);
        }
    }
}
