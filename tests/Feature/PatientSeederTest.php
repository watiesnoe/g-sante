<?php

use App\Models\Patient;
use Database\Seeders\PatientSeeder;
use Tests\Support\Fabrique;

it('crée des patients fictifs dans chaque établissement, sans doublon à la relance', function () {
    $premier = Fabrique::etablissement('csref');
    $autre = Fabrique::autreEtablissement();

    $this->seed(PatientSeeder::class);
    $this->seed(PatientSeeder::class);

    $patients = Patient::sansCloisonnement()->get();
    expect($patients->where('etablissement_id', $premier->id))->toHaveCount(30)
        ->and($patients->where('etablissement_id', $autre->id))->toHaveCount(30)
        ->and($patients->whereNull('numero_dossier'))->toBeEmpty()
        // Numéros fictifs : aucun ne commence comme un mobile malien
        ->and($patients->every(fn($p) => str_starts_with($p->telephone, '0') && strlen($p->telephone) === 8))->toBeTrue()
        ->and($patients->every(fn($p) => in_array($p->genre, ['M', 'F']) && $p->age >= 0 && $p->age <= 85))->toBeTrue()
        ->and($patients->where('age', '<', 15)->count())->toBeGreaterThan(5);
});

it('ne crée aucun patient de démonstration en production', function () {
    Fabrique::etablissement('csref');
    app()->detectEnvironment(fn() => 'production');

    // Appel direct : en production, db:seed demanderait d'abord une confirmation
    app(PatientSeeder::class)->run();

    expect(Patient::sansCloisonnement()->count())->toBe(0);
});
