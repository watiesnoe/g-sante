<?php

use App\Models\Consultation;
use App\Models\Diagnostic;
use App\Models\FusionPatient;
use App\Models\RendezVous;
use Tests\Support\Fabrique;

beforeEach(fn() => Fabrique::etablissement());

it('fige une consultation signée : modification refusée, addendum accepté (EF-04.05, RG-13)', function () {
    $medecin = Fabrique::utilisateur(['consultations.edit', 'consultations.view']);
    $consultation = Consultation::create(['patient_id' => Fabrique::patient()->id, 'medecin_id' => $medecin->id, 'diagnostic' => 'Paludisme simple']);

    $this->actingAs($medecin)->post(route('consultations.signer', $consultation))->assertSessionHas('success');
    expect($consultation->fresh()->estSignee())->toBeTrue();

    $this->put(route('consultations.update', $consultation), ['patient_id' => $consultation->patient_id, 'medecin_id' => $medecin->id, 'diagnostic' => 'Autre'])->assertForbidden();
    $this->post(route('consultations.addendum', $consultation), ['contenu' => 'Résultat TDR positif reçu après coup.'])->assertSessionHas('success');

    expect($consultation->fresh()->diagnostic)->toBe('Paludisme simple')
        ->and($consultation->addenda()->first()->contenu)->toBe('Résultat TDR positif reçu après coup.')
        ->and(fn() => $consultation->addenda()->first()->update(['contenu' => 'x']))->toThrow(LogicException::class);
});

it('cherche un diagnostic dans la nomenclature par code ou libellé (EF-04.02)', function () {
    Diagnostic::create(['code' => 'B54', 'libelle' => 'Paludisme, sans précision']);
    Diagnostic::create(['code' => 'J11', 'libelle' => 'Grippe, virus non identifié']);

    $this->actingAs(Fabrique::utilisateur())->getJson(route('diagnostics.recherche', ['q' => 'B5']))->assertJsonCount(1)->assertJsonPath('0.code', 'B54');
    $this->getJson(route('diagnostics.recherche', ['q' => 'grippe']))->assertJsonPath('0.code', 'J11');
});

it('fusionne deux dossiers puis annule la fusion sans rien perdre (EF-02.03)', function () {
    $admin = Fabrique::utilisateur(['patients.fusionner']);
    $conserve = Fabrique::patient();
    $doublon = Fabrique::patient();
    $doublon->update(['allergies' => 'Pénicilline']);
    $consultation = Consultation::create(['patient_id' => $doublon->id, 'medecin_id' => $admin->id, 'diagnostic' => 'x']);

    $this->actingAs($admin)->post(route('patients.fusion', $conserve), ['doublon_id' => $doublon->id, 'motif' => 'Même personne, double inscription'])->assertSessionHas('success');
    expect($consultation->fresh()->patient_id)->toBe($conserve->id)
        ->and($conserve->fresh()->allergies)->toContain('Pénicilline')
        ->and($doublon->fresh()->fusionne_dans_id)->toBe($conserve->id);

    $this->post(route('fusions.annuler', FusionPatient::first()))->assertSessionHas('success');
    expect($consultation->fresh()->patient_id)->toBe($doublon->id)
        ->and($doublon->fresh()->fusionne_dans_id)->toBeNull();
});

it('produit un certificat à partir d\'un modèle avec les données du patient (EF-04.06)', function () {
    $medecin = Fabrique::utilisateur(['consultations.print']);
    $patient = Fabrique::patient();

    $this->actingAs($medecin)->get(route('documents.create', ['patient' => $patient->uuid]))
        ->assertOk()->assertSee($patient->numero_dossier)->assertSee('TRAORÉ');
    $this->post(route('documents.store'), [
        'patient_id' => $patient->id, 'type' => 'certificat', 'titre' => 'Certificat médical', 'contenu' => 'Texte du certificat',
    ])->assertRedirect();
    expect(\App\Models\DocumentMedical::count())->toBe(1);
});

it('gère l\'agenda : honoré, absent, annulé, sans retour sur un rendez-vous clos (EF-03.04)', function () {
    $secretaire = Fabrique::utilisateur(['rendezvous.view', 'rendezvous.confirm']);
    $rdv = RendezVous::create(['patient_id' => Fabrique::patient()->id, 'medecin_id' => $secretaire->id, 'date_heure' => now()->setTime(10, 0), 'statut' => 'prevu']);

    $this->actingAs($secretaire)->get(route('agenda.index'))->assertOk()->assertSee('TRAORÉ');
    $this->post(route('agenda.statut', $rdv), ['statut' => 'absent'])->assertSessionHas('success');
    expect($rdv->fresh()->statut)->toBe('absent');
    $this->post(route('agenda.statut', $rdv), ['statut' => 'realise'])->assertStatus(422);
});

it('produit le rapport de la période et l\'exporte en Excel et PDF (EF-13.02)', function () {
    $this->actingAs(Fabrique::utilisateur(['rapports.view']));

    $this->get(route('rapports.index'))->assertOk()->assertSee('État de facturation');
    $this->get(route('rapports.index', ['format' => 'xlsx']))->assertOk()->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $this->get(route('rapports.index', ['format' => 'pdf']))->assertOk()->assertHeader('content-type', 'application/pdf');
});
