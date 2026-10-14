<?php

use App\Models\Passage;
use App\Models\Ticket;
use App\Services\FacturationService;
use App\Services\PassageService;
use Tests\Support\Fabrique;

it('suit le passage selon le workflow actif et refuse une transition non prévue (EF-03.02, section 7.2)', function () {
    Fabrique::etablissement('clinique'); // pas de triage en clinique
    $agent = Fabrique::utilisateur(['accueil.view', 'accueil.create', 'accueil.orienter']);
    $patient = Fabrique::patient();

    $this->actingAs($agent)->postJson(route('passages.store'), ['patient_id' => $patient->id])->assertOk();
    $passage = Passage::firstOrFail();
    expect($passage->statut)->toBe('enregistre');

    $this->postJson(route('passages.statut', $passage), ['vers' => 'trie', 'priorite' => 2])->assertStatus(422);
    $this->postJson(route('passages.statut', $passage), ['vers' => 'termine'])->assertStatus(422);
    $this->postJson(route('passages.statut', $passage), ['vers' => 'en_consultation'])->assertOk();
    $this->postJson(route('passages.statut', $passage), ['vers' => 'termine'])->assertOk();

    expect($passage->fresh()->statut)->toBe('termine')
        ->and($passage->fresh()->termine_at)->not->toBeNull()
        ->and($passage->historiques()->pluck('vers')->all())->toBe(['enregistre', 'en_consultation', 'termine']);
});

it('ordonne la file par priorité quand le triage est actif (EF-03.03)', function () {
    Fabrique::etablissement('csref');
    $this->actingAs(Fabrique::utilisateur(['accueil.view', 'accueil.orienter']));
    $service = app(PassageService::class);

    $premier = $service->ouvrir(Fabrique::patient());
    $second = $service->ouvrir(Fabrique::patient());
    $service->passerA($second, 'trie', ['priorite' => 1]);
    $service->passerA($premier, 'trie', ['priorite' => 3]);

    $ordre = collect($this->getJson(route('passages.index', ['statut' => 'ouverts']), ['X-Requested-With' => 'XMLHttpRequest'])->json('data'))->pluck('numero')->all();
    expect($ordre)->toBe([$second->numero, $premier->numero]);
});

it('facture au tarif en vigueur à la date de l\'acte et fige le prix des actes passés (EF-05.01, RG-07)', function () {
    Fabrique::etablissement();
    $prestation = Fabrique::prestation(5000);
    $prestation->tarifs()->create(['prix' => 5000, 'date_effet' => now()->subYear()]);
    $prestation->tarifs()->create(['prix' => 7500, 'date_effet' => now()->addMonth()]);

    $service = app(FacturationService::class);
    expect($service->calculer([['prestation_id' => $prestation->id, 'quantite' => 1]])['total'])->toBe(5000.0)
        ->and($service->calculer([['prestation_id' => $prestation->id, 'quantite' => 1]], null, now()->addMonths(2))['total'])->toBe(7500.0);
});

it('ouvre le passage du patient et enregistre le mode de paiement à la création d\'un ticket (EF-03.01, EF-06.03)', function () {
    Fabrique::etablissement();
    $caissier = Fabrique::utilisateur(['tickets.create']);
    Fabrique::ouvrirCaisse($caissier);
    $prestation = Fabrique::prestation(3000);
    $patient = Fabrique::patient();

    $this->actingAs($caissier)->postJson(route('tickets.store'), [
        'patient_id' => $patient->id, 'service_medical_id' => $prestation->service_medical_id, 'medecin_id' => $caissier->id,
        'mode_paiement' => 'mobile',
        'items' => [['prestation_id' => $prestation->id, 'prix_unitaire' => 1, 'quantite' => 1, 'remise' => 0, 'sous_total' => 1, 'service' => 'MG']],
    ])->assertOk();

    $ticket = Ticket::firstOrFail();
    expect((float) $ticket->total)->toBe(3000.0)
        ->and($ticket->numero)->toStartWith(\App\Models\Etablissement::courant()->code . '-T')
        ->and(\App\Models\CaisseMouvement::first()->mode)->toBe('mobile')
        ->and(Passage::where('patient_id', $patient->id)->where('ticket_id', $ticket->id)->exists())->toBeTrue();

    // Un mode non accepté par l'établissement est refusé
    $this->postJson(route('tickets.store'), [
        'patient_id' => $patient->id, 'service_medical_id' => $prestation->service_medical_id, 'medecin_id' => $caissier->id,
        'mode_paiement' => 'bitcoin',
        'items' => [['prestation_id' => $prestation->id, 'prix_unitaire' => 1, 'quantite' => 1, 'remise' => 0, 'sous_total' => 1, 'service' => 'MG']],
    ])->assertJsonValidationErrors('mode_paiement');
});
