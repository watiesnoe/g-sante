<?php

use App\Models\FicheMedicament;
use App\Models\FicheSynthese;
use Tests\Support\Fabrique;

beforeEach(function () {
    Fabrique::etablissement('csref');
    $this->amox = FicheMedicament::create(['nom' => 'AMOXICILLINE', 'voie' => 'oral', 'source' => 'MSF', 'page' => 37]);
    $this->azithro = FicheMedicament::create(['nom' => 'AZITHROMYCINE', 'voie' => 'oral', 'source' => 'MSF', 'page' => 52]);
    $this->auteur = Fabrique::utilisateur(['syntheses.view', 'syntheses.rediger', 'syntheses.valider', 'referentiel.view']);
    $this->confrere = Fabrique::utilisateur(['syntheses.view', 'syntheses.rediger', 'syntheses.valider']);
    $this->infirmier = Fabrique::utilisateur(['syntheses.view']);
});

function formulaire(array $plus = []): array
{
    return $plus + [
        'titre' => 'Pneumonie aiguë communautaire de l\'adulte', 'specialite' => 'Pneumologie', 'motif' => 'Fièvre, toux, point de côté',
        'demarche' => 'Rechercher un foyer de crépitants.', 'gravite' => 'FR > 30/min, PAS < 90 mmHg : hospitaliser.',
        'traitement' => 'Antibiothérapie probabiliste.',
        'medicaments' => [['id' => test()->amox->id, 'usage' => '1re intention'], ['id' => test()->azithro->id, 'usage' => 'Si allergie aux pénicillines']],
        'references' => [['ouvrage' => 'Berrebi — Diagnostics et thérapeutique', 'localisation' => 'p. 54', 'note' => 'Point de côté fébrile']],
        'action' => 'soumettre',
    ];
}

it('rédige une fiche reliée aux médicaments du guide et à des références, puis la fait valider par un confrère', function () {
    $this->actingAs($this->auteur)->post(route('syntheses.store'), formulaire())->assertRedirect();
    $fiche = FicheSynthese::sole();
    expect($fiche->statut)->toBe('a_valider')
        ->and($fiche->medicaments->pluck('nom')->all())->toBe(['AMOXICILLINE', 'AZITHROMYCINE'])
        ->and($fiche->medicaments->first()->pivot->usage)->toBe('1re intention')
        ->and($fiche->references->first()->localisation)->toBe('p. 54');

    // L'auteur ne valide pas sa propre fiche
    $this->post(route('syntheses.valider', $fiche))->assertSessionHas('error');
    $this->actingAs($this->confrere)->post(route('syntheses.valider', $fiche))->assertSessionHas('success');
    expect($fiche->fresh()->statut)->toBe('validee')->and($fiche->fresh()->valide_par)->toBe($this->confrere->id);

    $this->actingAs($this->infirmier)->get(route('syntheses.show', $fiche))->assertOk()
        ->assertSee('Pneumonie aiguë communautaire')->assertSee('AMOXICILLINE')->assertSee('Si allergie aux pénicillines')->assertSee('p. 54');
    $this->post(route('syntheses.valider', $fiche))->assertForbidden();
    $this->get(route('syntheses.create'))->assertForbidden();

    // La fiche du médicament renvoie aux fiches de synthèse qui l'utilisent
    $this->actingAs($this->auteur)->get(route('referentiel.show', $this->amox))->assertSee('Pneumonie aiguë communautaire');
});

it('renvoie en validation une fiche validée dont le contenu change', function () {
    $this->actingAs($this->auteur)->post(route('syntheses.store'), formulaire());
    $fiche = FicheSynthese::sole();
    $this->actingAs($this->confrere)->post(route('syntheses.valider', $fiche));

    // Enregistré sans changement : reste validée
    $this->actingAs($this->auteur)->put(route('syntheses.update', $fiche), formulaire(['action' => 'brouillon']));
    expect($fiche->fresh()->statut)->toBe('validee');

    $this->put(route('syntheses.update', $fiche), formulaire(['gravite' => 'Ajout : confusion.', 'action' => 'brouillon']));
    expect($fiche->fresh()->statut)->toBe('brouillon')->and($fiche->fresh()->valide_par)->toBeNull();
});

it('garde un brouillon visible de son seul auteur et cloisonne les fiches par établissement', function () {
    $this->actingAs($this->auteur)->post(route('syntheses.store'), formulaire(['action' => 'brouillon']));
    $fiche = FicheSynthese::sole();

    $this->actingAs($this->confrere)->get(route('syntheses.show', $fiche))->assertNotFound();
    $this->get(route('syntheses.index'))->assertDontSee('Pneumonie aiguë communautaire');
    $this->actingAs($this->auteur)->get(route('syntheses.index'))->assertSee('Pneumonie aiguë communautaire');

    $autre = Fabrique::autreEtablissement();
    Fabrique::dans($autre, fn() => expect(FicheSynthese::count())->toBe(0));
});

it('recherche les fiches par symptôme', function () {
    $this->actingAs($this->auteur)->post(route('syntheses.store'), formulaire());
    $this->get(route('syntheses.index', ['q' => 'point de côté']))->assertSee('Pneumonie aiguë communautaire');
    $this->get(route('syntheses.index', ['q' => 'céphalée']))->assertDontSee('Pneumonie aiguë communautaire');
});
