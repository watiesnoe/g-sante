<?php

use App\Models\Assurance;
use App\Models\Bordereau;
use App\Models\CaisseMouvement;
use App\Models\Convention;
use App\Models\MouvementStock;
use App\Models\OrdonnancePaiement;
use App\Models\Ticket;
use App\Services\StockService;
use Tests\Support\Fabrique;

beforeEach(function () {
    Fabrique::etablissement('clinique');
    $this->assurance = Assurance::create(['nom' => 'AMO test', 'taux' => 0]);
    Convention::create(['assurance_id' => $this->assurance->id, 'taux' => 80, 'couverture_actes' => 'toutes', 'couvre_medicaments' => true, 'date_effet' => now()->subYear()]);
    $this->patient = Fabrique::patient();
    $this->patient->update(['assurance_id' => $this->assurance->id, 'numero_assurance' => 'AMO-123', 'fin_validite_assurance' => now()->addYear()]);
    $this->agent = Fabrique::utilisateur(['tickets.create', 'ordonnances.payer', 'tiers_payants.bordereaux']);
    Fabrique::ouvrirCaisse($this->agent);
    $this->actingAs($this->agent);
});

it('calcule exactement les parts patient et organisme sur consultation, analyse et médicaments, et sort le bon lot (REC-04)', function () {
    $consultation = Fabrique::prestation(5000);
    $analyse = Fabrique::prestation(3000);
    $med = Fabrique::medicament('Amoxicilline 500', 1000);
    $stock = app(StockService::class);
    $stock->entree($med, 10, 'LOT-TARD', now()->addYear(), null);
    $stock->entree($med, 10, 'LOT-TOT', now()->addMonths(2), null);

    $this->postJson(route('tickets.store'), [
        'patient_id' => $this->patient->id, 'service_medical_id' => $consultation->service_medical_id, 'medecin_id' => $this->agent->id,
        'assurance_id' => $this->assurance->id,
        'items' => [
            ['prestation_id' => $consultation->id, 'prix_unitaire' => 0, 'quantite' => 1, 'remise' => 0, 'sous_total' => 0, 'service' => 'MG'],
            ['prestation_id' => $analyse->id, 'prix_unitaire' => 0, 'quantite' => 1, 'remise' => 0, 'sous_total' => 0, 'service' => 'Labo'],
        ],
    ])->assertOk();

    $ticket = Ticket::with('items')->firstOrFail();
    expect((float) $ticket->total)->toBe(8000.0)
        ->and((float) $ticket->part_assurance)->toBe(6400.0)
        ->and((float) $ticket->part_patient)->toBe(1600.0)
        ->and($ticket->items->pluck('part_assurance')->map(fn($v) => (float) $v)->all())->toBe([4000.0, 2400.0])
        ->and($ticket->numero_assure)->toBe('AMO-123');

    $ordonnance = Fabrique::ordonnance($med, 3);
    $ordonnance->consultation->update(['patient_id' => $this->patient->id]);
    $this->postJson(route('ordonnances.payer', $ordonnance), ['medicaments' => [$med->id => 3]])->assertOk()->assertJson(['montant' => 600]);

    $vente = OrdonnancePaiement::firstOrFail();
    expect((float) $vente->part_assurance)->toBe(2400.0)
        ->and((float) $vente->part_patient)->toBe(600.0)
        ->and(MouvementStock::where('source_type', OrdonnancePaiement::class)->first()->lot->numero)->toBe('LOT-TOT');

    // La caisse n'encaisse que les parts patient : 1 600 + 600
    expect((float) CaisseMouvement::sum('montant'))->toBe(2200.0);

    // Bordereau de l'organisme : 6 400 + 2 400
    $this->post(route('bordereaux.generer'), ['assurance_id' => $this->assurance->id, 'du' => today()->toDateString(), 'au' => today()->toDateString()])->assertRedirect();
    $bordereau = Bordereau::with('lignes')->firstOrFail();
    expect($bordereau->montant)->toBe(8800.0)->and($bordereau->lignes)->toHaveCount(2);

    // Pas de double facturation ; recouvrement partiel puis total
    $this->post(route('bordereaux.generer'), ['assurance_id' => $this->assurance->id, 'du' => today()->toDateString(), 'au' => today()->toDateString()])->assertSessionHas('error');
    $this->post(route('bordereaux.recouvrer', $bordereau), ['montant' => 5000, 'date_recouvrement' => today()->toDateString(), 'mode' => 'virement'])->assertSessionHas('success');
    expect($bordereau->fresh()->statut)->toBe('partiel');
    $this->post(route('bordereaux.recouvrer', $bordereau), ['montant' => 9999, 'date_recouvrement' => today()->toDateString(), 'mode' => 'virement'])->assertSessionHas('error');
    $this->post(route('bordereaux.recouvrer', $bordereau), ['montant' => 3800, 'date_recouvrement' => today()->toDateString(), 'mode' => 'virement']);
    expect($bordereau->fresh()->statut)->toBe('regle');
});

it('plafonne la prise en charge par facture et refuse une prise en charge expirée (EF-07.01, EF-07.02)', function () {
    Convention::create(['assurance_id' => $this->assurance->id, 'taux' => 80, 'plafond_par_facture' => 2000, 'couverture_actes' => 'toutes', 'date_effet' => today()]);
    $acte = Fabrique::prestation(5000);
    $donnees = [
        'patient_id' => $this->patient->id, 'service_medical_id' => $acte->service_medical_id, 'medecin_id' => $this->agent->id, 'assurance_id' => $this->assurance->id,
        'items' => [['prestation_id' => $acte->id, 'prix_unitaire' => 0, 'quantite' => 1, 'remise' => 0, 'sous_total' => 0, 'service' => 'MG']],
    ];

    $this->postJson(route('tickets.store'), $donnees)->assertOk();
    expect((float) Ticket::first()->part_assurance)->toBe(2000.0)->and((float) Ticket::first()->part_patient)->toBe(3000.0);

    $this->patient->update(['fin_validite_assurance' => now()->subDay()]);
    $this->postJson(route('tickets.store'), $donnees)->assertStatus(422);
});
