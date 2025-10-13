<?php

use App\Models\Consultation;
use App\Models\Lit;
use App\Models\PrescriptionExamen;
use App\Models\Reference;
use App\Models\Salle;
use App\Services\HospitalisationService;
use App\Services\RapportMensuelService;
use Tests\Support\Fabrique;

beforeEach(fn() => Fabrique::etablissement('csref'));

it('crée une référence sortante lors d\'un transfert vers un autre hôpital, comptée au rapport mensuel', function () {
    $medecin = Fabrique::utilisateur(['transferts.create', 'references.view']);
    $this->actingAs($medecin);
    $salle = Salle::create(['nom' => 'Médecine', 'type' => 'commune', 'capacite' => 1, 'prix' => 5000, 'service_medical_id' => Fabrique::prestation()->service_medical_id]);
    $lit = Lit::create(['numero' => 'M1', 'salle_id' => $salle->id, 'statut' => 'Libre']);
    $patient = Fabrique::patient();
    $sejour = app(HospitalisationService::class)->admettre($patient, $lit, ['motif' => 'Éclampsie']);

    $this->postJson(route('transferts.store'), [
        'patient_id' => $patient->uuid, 'type' => 'hopital_externe', 'motif' => 'Plateau technique',
        'hospitalisation_id' => $sejour->uuid, 'hopital_destination' => 'CHU Gabriel Touré',
    ])->assertOk()->assertJsonPath('success', true);

    $reference = Reference::sole();
    expect($reference->sens)->toBe('sortante')
        ->and($reference->structure_destination)->toBe('CHU Gabriel Touré')
        ->and($reference->hospitalisation_id)->toBe($sejour->id)
        ->and($reference->statut)->toBe('emise')
        ->and($lit->fresh()->statut)->toBe('Libre');

    $indicateurs = app(RapportMensuelService::class)->calculer(now()->startOfMonth(), now()->endOfMonth());
    expect($indicateurs['indicateurs']['references.emises'])->toBe(1);
});

it('ne crée pas de référence pour un transfert interne vers un autre médecin', function () {
    $medecin = Fabrique::utilisateur(['transferts.create']);
    $confrere = Fabrique::utilisateur();
    $confrere->assignRole(\App\Models\Role::firstOrCreate(['name' => 'medecin', 'guard_name' => 'web']));
    $this->actingAs($medecin);
    $patient = Fabrique::patient();
    $consultation = Consultation::create(['patient_id' => $patient->id, 'medecin_id' => $medecin->id, 'diagnostic' => 'x']);

    $this->postJson(route('transferts.store'), [
        'patient_id' => $patient->uuid, 'type' => 'medecin', 'motif' => 'Avis', 'consultation_id' => $consultation->uuid, 'dest_medecin_id' => $confrere->id,
    ])->assertOk();

    expect(Reference::count())->toBe(0);
});

it('regroupe les anciens écrans d\'examens dans le poste laboratoire', function () {
    $medecin = Fabrique::utilisateur(['examens.view', 'examens.results', 'consultations.create']);
    $autre = Fabrique::utilisateur();
    $this->actingAs($medecin);
    $mien = Consultation::create(['patient_id' => Fabrique::patient()->id, 'medecin_id' => $medecin->id, 'diagnostic' => 'x']);
    $sien = Consultation::create(['patient_id' => Fabrique::patient()->id, 'medecin_id' => $autre->id, 'diagnostic' => 'y']);
    PrescriptionExamen::create(['consultation_id' => $mien->id, 'examen' => 'Glycémie', 'type' => 'laboratoire', 'statut' => 'demande']);
    PrescriptionExamen::create(['consultation_id' => $sien->id, 'examen' => 'Créatinine', 'type' => 'laboratoire', 'statut' => 'demande']);

    // Les anciennes adresses restent valides et mènent au poste laboratoire
    $this->get(route('prescriptions.index'))->assertRedirect(route('laboratoire.index', ['mes' => 1]));
    $this->get(route('reponses.index'))->assertRedirect(route('laboratoire.index', ['statut' => 'valide']));

    $this->get(route('laboratoire.index', ['mes' => 1]))->assertOk()->assertSee('Glycémie')->assertDontSee('Créatinine')
        ->assertSee(route('prescriptions.pdf', $mien->uuid));
    $this->get(route('laboratoire.index'))->assertOk()->assertSee('Glycémie')->assertSee('Créatinine');
});
