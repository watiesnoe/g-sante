<?php

use App\Models\CaisseSession;
use App\Models\Commande;
use App\Models\DemandeAchat;
use App\Models\Fournisseur;
use App\Models\Garde;
use App\Models\Guichet;
use Tests\Support\Fabrique;

beforeEach(fn() => Fabrique::etablissement('clinique'));

it('ouvre une seule caisse par guichet et consolide la journée (EF-06.06)', function () {
    $guichet = Guichet::create(['nom' => 'Guichet 1']);
    $a = Fabrique::utilisateur(['caisse.view']);
    $b = Fabrique::utilisateur(['caisse.view', 'caisse.valider_cloture']);

    $this->actingAs($a)->post(route('caisse.storeOpen'), ['solde_initial' => 1000])->assertSessionHasErrors('guichet_id');
    $this->post(route('caisse.storeOpen'), ['solde_initial' => 1000, 'guichet_id' => $guichet->id])->assertRedirect();
    $this->actingAs($b)->post(route('caisse.storeOpen'), ['solde_initial' => 0, 'guichet_id' => $guichet->id])->assertSessionHasErrors('guichet_id');

    expect(CaisseSession::where('guichet_id', $guichet->id)->count())->toBe(1);
    $this->get(route('caisse.consolidation'))->assertOk()->assertSee('Guichet 1');
});

it('fait passer une demande d\'achat par l\'approbation puis la transforme en commande (EF-12.04)', function () {
    $infirmier = Fabrique::utilisateur(['achats.demander']);
    $pharmacien = Fabrique::utilisateur(['achats.demander', 'achats.approuver', 'stock.commandes']);
    $med = Fabrique::medicament('Sérum salé 500 ml', 800);
    $fournisseur = Fournisseur::create(['nom' => 'PPM', 'contact' => '20000000']);

    $this->actingAs($infirmier)->post(route('achats.store'), ['lignes' => [['medicament_id' => $med->id, 'quantite' => 40]], 'soumettre' => 1])->assertSessionHas('success');
    $demande = DemandeAchat::firstOrFail();
    $this->post(route('achats.action', [$demande, 'approuver']))->assertSessionHas('error');

    $this->actingAs($pharmacien)->post(route('achats.action', [$demande, 'approuver']))->assertSessionHas('success');
    $this->post(route('achats.action', [$demande, 'commander']), ['fournisseur_id' => $fournisseur->id])->assertSessionHas('success');

    $commande = Commande::with('lignes')->firstOrFail();
    expect($demande->fresh()->statut)->toBe('commandee')
        ->and($commande->lignes->first()->quantite)->toBe(40)
        ->and((float) $commande->total)->toBe(16000.0);
});

it('refuse deux gardes qui se chevauchent pour la même personne (EF-12.05)', function () {
    $rh = Fabrique::utilisateur(['rh.gardes.view', 'rh.gardes.gerer']);
    $medecin = Fabrique::utilisateur();
    $garde = ['user_id' => $medecin->id, 'type' => 'garde', 'debut' => now()->addDay()->setTime(18, 0)->toDateTimeString(), 'fin' => now()->addDays(2)->setTime(8, 0)->toDateTimeString()];

    $this->actingAs($rh)->post(route('gardes.store'), $garde)->assertSessionHas('success');
    $this->post(route('gardes.store'), $garde)->assertSessionHas('error');
    expect(Garde::count())->toBe(1);
    $this->get(route('gardes.index', ['semaine' => now()->addDay()->toDateString()]))->assertOk()->assertSee($medecin->name);
});

it('alerte quand un médicament prescrit correspond à une allergie connue (EF-04.04)', function () {
    $medecin = Fabrique::utilisateur(['consultations.create']);
    $patient = Fabrique::patient();
    $patient->update(['allergies' => 'Amoxicilline, arachide']);
    $med = Fabrique::medicament('Amoxicilline 500 mg');
    $donnees = ['patient_id' => $patient->id, 'medecin_id' => $medecin->id, 'diagnostic' => 'Angine', 'medicaments' => [$med->id], 'quantites' => [1], 'posologies' => ['1x3'], 'duree_jours' => [7]];

    $this->actingAs($medecin)->postJson(route('consultations.store'), $donnees)->assertStatus(422)->assertJsonValidationErrors('allergie');
    $this->postJson(route('consultations.store'), $donnees + ['confirmer_allergie' => 1])->assertOk();
});

it('ventile recettes et dépenses par service dans le rapport (EF-12.02)', function () {
    $caissier = Fabrique::utilisateur(['tickets.create', 'rapports.view']);
    Fabrique::ouvrirCaisse($caissier);
    $acte = Fabrique::prestation(4000);
    $this->actingAs($caissier)->postJson(route('tickets.store'), [
        'patient_id' => Fabrique::patient()->id, 'service_medical_id' => $acte->service_medical_id, 'medecin_id' => $caissier->id,
        'items' => [['prestation_id' => $acte->id, 'prix_unitaire' => 0, 'quantite' => 1, 'remise' => 0, 'sous_total' => 0, 'service' => 'MG']],
    ])->assertOk();

    $rapport = app(\App\Services\RapportService::class)->generer(today(), today());
    expect($rapport['analytique']['Médecine générale']['recettes'])->toBe(4000.0);
});

it('envoie la veille un rappel SMS une seule fois par rendez-vous (EF-03.05)', function () {
    $rdv = \App\Models\RendezVous::create(['patient_id' => Fabrique::patient()->id, 'medecin_id' => Fabrique::utilisateur()->id, 'date_heure' => today()->addDay()->setTime(9, 30), 'statut' => 'prevu']);

    $this->artisan('rendezvous:rappels')->assertSuccessful();
    $this->artisan('rendezvous:rappels')->assertSuccessful();

    expect($rdv->fresh()->rappel_envoye_at)->not->toBeNull()
        ->and(\Illuminate\Support\Facades\DB::table('sms_envois')->count())->toBe(1);
});
