<?php

use App\Models\Etablissement;
use App\Models\Patient;
use Tests\Support\Fabrique;

it('crée un cabinet avec les seuls modules et paramètres de son profil (REC-01)', function () {
    Fabrique::etablissement();
    $superAdmin = Fabrique::utilisateur(['etablissements.gerer', 'parametres.etablissement']);

    $this->actingAs($superAdmin)->post(route('etablissements.store'), [
        'type' => 'cabinet', 'nom' => 'Cabinet Dr Coulibaly', 'code' => 'CAB-DC', 'district' => 'Ségou',
    ])->assertRedirect(route('etablissements.index'));

    $cabinet = Etablissement::where('code', 'CAB-DC')->firstOrFail();
    expect($cabinet->modules)->toBe(Etablissement::PROFILS['cabinet'])
        ->and($cabinet->moduleActif('hospitalisation'))->toBeFalse()
        ->and($cabinet->parametre('caisse.validation_cloture'))->toBeFalse()
        ->and($cabinet->parametre('paiement.moment'))->toBe('apres')
        ->and($cabinet->parametre('paiement.bloquant_urgence'))->toBeFalse()
        ->and($superAdmin->etablissements()->where('etablissements.id', $cabinet->id)->exists())->toBeTrue();

    // L'administrateur autorisé peut ensuite modifier les paramètres
    $this->post(route('etablissement.changer'), ['etablissement_id' => $cabinet->id]);
    $this->put(route('etablissement.update'), [
        'type' => 'cabinet', 'nom' => 'Cabinet Dr Coulibaly', 'modules' => ['rendezvous', 'ordonnance', 'examens'],
        'parametres' => ['caisse_validation_cloture' => 1, 'numerotation_format' => '{CODE}/{NUM}', 'caisse_modes' => ['especes']],
    ])->assertRedirect();

    $cabinet->refresh();
    expect($cabinet->moduleActif('examens'))->toBeTrue()
        ->and($cabinet->parametre('caisse.validation_cloture'))->toBeTrue()
        ->and($cabinet->parametre('numerotation.format'))->toBe('{CODE}/{NUM}');
});

it('refuse le dossier d\'un patient d\'un autre établissement, sans fuite (REC-09, SEC-04)', function () {
    $principal = Fabrique::etablissement();
    $autre = Fabrique::autreEtablissement();
    $patientAutre = Fabrique::dans($autre, fn() => Fabrique::patient());
    $medecin = Fabrique::utilisateur(['patients.dossier', 'patients.search', 'patients.view']);

    $this->actingAs($medecin);
    $this->get(route('patients.show', $patientAutre))->assertNotFound();
    $this->getJson(route('patients.search', ['q' => 'Traoré']))->assertOk()->assertJsonCount(0);

    expect(Patient::count())->toBe(0)
        ->and(Etablissement::table('patients')->count())->toBe(0)
        ->and(Patient::sansCloisonnement()->count())->toBe(1)
        ->and($patientAutre->etablissement_id)->toBe($autre->id);

    // Et le compte ne peut pas se placer dans un établissement qui ne lui est pas affecté
    $this->post(route('etablissement.changer'), ['etablissement_id' => $autre->id])->assertForbidden();
});

it('numérote dossiers, passages et tickets au format de l\'établissement, sans doublon (EF-02.01, EF-03.01)', function () {
    $etablissement = Fabrique::etablissement();
    $etablissement->update(['code' => 'CSREF']);
    $annee = now()->year;

    $a = Fabrique::patient();
    $b = Fabrique::patient();

    expect($a->numero_dossier)->toBe("CSREF-P{$annee}-000001")
        ->and($b->numero_dossier)->toBe("CSREF-P{$annee}-000002")
        ->and(app(\App\Services\NumerotationService::class)->suivant('passage'))->toBe("CSREF-V{$annee}-000001");
});

it('signale un doublon probable avant de créer un dossier (EF-02.02)', function () {
    Fabrique::etablissement();
    Fabrique::patient();
    $agent = Fabrique::utilisateur(['patients.create']);
    $donnees = ['nom' => 'TRAORÉ', 'prenom' => 'awa', 'genre' => 'F', 'telephone' => '70000001', 'ethnie' => 'Bambara', 'age' => 31];

    $this->actingAs($agent)->postJson(route('patients.store'), $donnees)->assertStatus(409)->assertJsonPath('doublons.0.nom', 'Traoré');
    $this->postJson(route('patients.store'), $donnees + ['confirmer_doublon' => 1])->assertOk();
    expect(Patient::count())->toBe(2);
});

it('affiche la provenance et le CSCom d\'origine pour un CSRéf (EF-02.01)', function () {
    Fabrique::etablissement('csref');
    $agent = Fabrique::utilisateur(['patients.create']);
    $donnees = ['nom' => 'Diarra', 'prenom' => 'Mariam', 'genre' => 'F', 'telephone' => '70000002', 'ethnie' => 'Peul', 'age' => 24];

    $this->actingAs($agent)->postJson(route('patients.store'), $donnees)->assertStatus(422)->assertJsonValidationErrors('provenance');
    $this->postJson(route('patients.store'), $donnees + ['provenance' => 'cscom'])->assertJsonValidationErrors('cscom_origine');
    $this->postJson(route('patients.store'), $donnees + ['provenance' => 'cscom', 'cscom_origine' => 'CSCom de Pelengana'])->assertOk();
});
