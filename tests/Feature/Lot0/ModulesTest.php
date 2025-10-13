<?php

use App\Models\Etablissement;
use Tests\Support\Fabrique;

it('rend un module désactivé inaccessible, même par son adresse directe (REC-03, RG-16)', function () {
    Fabrique::etablissement('cabinet');
    Etablissement::query()->first()->update(['modules' => Etablissement::PROFILS['cabinet']]);
    $sageFemme = Fabrique::utilisateur(['maternity.view']);

    expect($sageFemme->hasModuleAccess('maternity'))->toBeFalse();
    $this->actingAs($sageFemme)->get(route('maternity.index'))->assertForbidden();
});

it('rattache chaque nouvelle donnée métier à l\'établissement (EF-01.03)', function () {
    $etablissement = Fabrique::etablissement();

    expect(Fabrique::patient()->etablissement_id)->toBe($etablissement->id)
        ->and(Fabrique::medicament()->etablissement_id)->toBe($etablissement->id);
});
