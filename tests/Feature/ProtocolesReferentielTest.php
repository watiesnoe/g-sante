<?php

use App\Models\FicheMedicament;
use App\Models\Maladie;
use App\Models\ProtocoleTraitement;
use App\Services\Referentiel\RapprochementProtocoles;
use Illuminate\Support\Facades\DB;
use Tests\Support\Fabrique;

beforeEach(function () {
    Fabrique::etablissement('csref');
    $this->amox = FicheMedicament::create(['nom' => 'AMOXICILLINE', 'voie' => 'oral', 'source' => 'MSF']);
    FicheMedicament::create(['nom' => 'CEFTRIAXONE', 'voie' => 'injectable', 'source' => 'MSF']);
    FicheMedicament::create(['nom' => 'MÉFLOQUINE = MQ', 'voie' => 'oral', 'source' => 'MSF', 'deconseille' => true]);
    FicheMedicament::create(['nom' => 'AMOXICILLINE/Acide CLAVULANIQUE = CO-AMOXICLAV', 'voie' => 'oral', 'source' => 'MSF']);
    FicheMedicament::create(['nom' => 'PREDNISOLONE et PREDNISONE', 'voie' => 'oral', 'source' => 'MSF']);

    $this->maladie = Maladie::create(['nom' => 'Pneumonie']);
    $this->protocole = ProtocoleTraitement::create(['maladie_id' => $this->maladie->id, 'titre' => 'Pneumonie de l\'adulte']);
    foreach ([['Amoxicilline 500mg', 'principal'], ['Ceftriaxone (IV)', 'alternatif'], ['Méfloquine (oral)', 'principal'],
              ['Amoxicilline + acide clavulanique (oral)', 'alternatif'], ['Prednisolone (oral)', 'adjuvant'], ['Surfactant (intratrachéal)', 'adjuvant']] as [$nom, $type]) {
        $this->protocole->lignes()->create(['nom_medicament' => $nom, 'type' => $type, 'posologie' => '1 g x 3/j', 'duree' => '5 jours']);
    }
});

it('relie les médicaments des protocoles au guide par leur DCI, jamais à une fiche déconseillée', function () {
    $resultat = app(RapprochementProtocoles::class)->relier();
    $liens = $this->protocole->lignes()->with('fiche')->get()->mapWithKeys(fn($l) => [$l->nom_medicament => $l->fiche?->nom]);

    expect($liens['Amoxicilline 500mg'])->toBe('AMOXICILLINE')
        ->and($liens['Ceftriaxone (IV)'])->toBe('CEFTRIAXONE')
        ->and($liens['Amoxicilline + acide clavulanique (oral)'])->toBe('AMOXICILLINE/Acide CLAVULANIQUE = CO-AMOXICLAV')
        ->and($liens['Prednisolone (oral)'])->toBe('PREDNISOLONE et PREDNISONE')
        ->and($liens['Méfloquine (oral)'])->toBeNull()
        ->and($liens['Surfactant (intratrachéal)'])->toBeNull()
        ->and($resultat['reliees'])->toBe(4);
});

it('propose en consultation le produit du stock de l\'établissement relié à la fiche, sinon le nom seul', function () {
    app(RapprochementProtocoles::class)->relier();
    $produit = Fabrique::medicament('Amoxicilline 500 mg gélule (Clamoxyl)');
    $produit->forceFill(['fiche_medicament_id' => $this->amox->id, 'stock' => 40])->save();
    $this->actingAs(Fabrique::utilisateur(['consultations.create']));

    $amox = collect($this->getJson(route('infectiologie.get_protocole', $this->maladie->id))->json('protocoles.0.medicaments'))->firstWhere('id', $produit->id);

    expect($amox['nom'])->toBe('Amoxicilline 500 mg gélule (Clamoxyl)')
        ->and($amox['stock'])->toBe(40)
        ->and($amox['fiche']['nom'])->toBe('AMOXICILLINE')
        ->and($amox['pivot']['posologie'])->toBe('1 g x 3/j');

    $ceftriaxone = collect($this->getJson(route('infectiologie.get_protocole', $this->maladie->id))->json('protocoles.0.medicaments'))->firstWhere('nom', 'Ceftriaxone (IV)');
    expect($ceftriaxone['id'])->toBeNull()->and($ceftriaxone['fiche']['nom'])->toBe('CEFTRIAXONE');

    // Un autre établissement ne voit pas ce produit : le protocole y reste cité par nom
    $autre = Fabrique::autreEtablissement();
    $ligne = $this->protocole->lignes()->where('nom_medicament', 'Amoxicilline 500mg')->first();
    Fabrique::dans($autre, fn() => expect($ligne->produitEnStock())->toBeNull());
});

it('affiche le protocole avec la fiche du guide et la disponibilité en stock', function () {
    app(RapprochementProtocoles::class)->relier();
    $this->actingAs(Fabrique::utilisateur(['infectiologie.view', 'referentiel.view']));
    $this->get(route('infectiologie.protocoles.show', $this->protocole))->assertOk()
        ->assertSee('Amoxicilline 500mg')->assertSee(route('referentiel.show', $this->amox))->assertSee('Absent du stock');
});
