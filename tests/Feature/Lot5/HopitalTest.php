<?php

use App\Models\Equipement;
use App\Models\Hospitalisation;
use App\Models\Intervention;
use App\Models\Lit;
use App\Models\OccupationLit;
use App\Models\Salle;
use App\Services\HospitalisationService;
use App\Services\IndicateursHospitaliersService;
use App\Services\StockService;
use Illuminate\Support\Carbon;
use Tests\Support\Fabrique;

beforeEach(function () {
    Fabrique::etablissement('hopital');
    $this->serviceId = Fabrique::prestation()->service_medical_id;
    $this->chambre = Salle::create(['nom' => 'Chirurgie 1', 'type' => 'Hospitalisation', 'capacite' => 2, 'prix' => 5000, 'service_medical_id' => $this->serviceId]);
    $this->bloc = Salle::create(['nom' => 'Bloc A', 'type' => 'Blocoperatoire', 'capacite' => 1, 'prix' => 0, 'service_medical_id' => $this->serviceId]);
});

it('mène un patient des urgences au bloc puis à la sortie : lits libérés, séjour facturé en entier (REC-16)', function () {
    $chirurgien = Fabrique::utilisateur(['urgences.gerer', 'urgences.view', 'bloc.view', 'bloc.programmer', 'hospitalisations.edit']);
    $this->actingAs($chirurgien);
    $patient = Fabrique::patient();
    $litA = Lit::create(['numero' => 'C1', 'salle_id' => $this->chambre->id, 'statut' => 'Libre']);
    $litB = Lit::create(['numero' => 'C2', 'salle_id' => $this->chambre->id, 'statut' => 'Libre']);
    $acte = Fabrique::prestation(150000);
    $fil = Fabrique::medicament('Fil résorbable', 2000);
    app(StockService::class)->entree($fil, 10, 'F1', now()->addYear(), null);

    // Urgences → hospitalisation
    $this->post(route('urgences.store'), ['patient_id' => $patient->id, 'motif' => 'Abdomen aigu', 'priorite' => 1]);
    $sejour = app(HospitalisationService::class)->admettre($patient, $litA, ['motif' => 'Appendicite']);

    // Bloc : programmation, déroulé, consommables
    $this->post(route('bloc.store'), [
        'patient_id' => $patient->id, 'salle_id' => $this->bloc->id, 'prestation_id' => $acte->id, 'libelle' => 'Appendicectomie',
        'urgence' => 'urgente', 'debut_prevu' => now()->addHour()->format('Y-m-d H:i'), 'duree_prevue_min' => 90, 'chirurgien_id' => $chirurgien->id,
    ])->assertRedirect();
    $intervention = Intervention::firstOrFail();
    expect($intervention->hospitalisation_id)->toBe($sejour->id);

    // Une seconde intervention sur le même créneau et la même salle est refusée
    $this->post(route('bloc.store'), [
        'patient_id' => Fabrique::patient()->id, 'salle_id' => $this->bloc->id, 'libelle' => 'Autre', 'urgence' => 'programmee',
        'debut_prevu' => now()->addHour()->addMinutes(30)->format('Y-m-d H:i'), 'duree_prevue_min' => 60, 'chirurgien_id' => Fabrique::utilisateur()->id,
    ])->assertSessionHasErrors('debut_prevu');

    $this->post(route('bloc.action', [$intervention, 'demarrer']));
    $this->post(route('bloc.action', [$intervention, 'consommer']), ['medicament_id' => $fil->id, 'quantite' => 2]);
    $this->post(route('bloc.action', [$intervention, 'terminer']), ['compte_rendu' => 'Appendice inflammatoire retiré, suites simples.']);
    expect($intervention->fresh()->statut)->toBe('terminee')->and($fil->fresh()->stock)->toBe(8);

    // Transfert de lit puis sortie : chaque lit est libéré
    $this->travel(2)->days();
    app(HospitalisationService::class)->transferer($sejour, $litB);
    $this->travel(1)->days();
    app(HospitalisationService::class)->sortir($sejour->fresh(), 'Guérison');
    expect($litA->fresh()->statut)->toBe('Libre')->and($litB->fresh()->statut)->toBe('Libre')
        ->and(OccupationLit::whereNull('fin')->count())->toBe(0);

    // Facture du séjour : 3 nuits × 5 000 + acte 150 000 + 2 fils × 2 000
    $releve = app(HospitalisationService::class)->releve($sejour->fresh());
    expect($releve['total'])->toBe(169000.0)
        ->and(collect($releve['lignes'])->pluck('type')->unique()->sort()->values()->all())->toBe(['acte', 'consommable', 'hebergement']);
});

it('calcule le taux d\'occupation et la durée moyenne de séjour comme le calcul manuel (REC-17)', function () {
    $lits = collect(range(1, 4))->map(fn($i) => Lit::create(['numero' => "L$i", 'salle_id' => $this->chambre->id, 'statut' => 'Libre']));
    $debutMois = Carbon::parse('2026-09-01');

    // Séjour 1 : 1er → 11 septembre (10 nuits), lit 1
    $h1 = Hospitalisation::create(['patient_id' => Fabrique::patient()->id, 'lit_id' => $lits[0]->id, 'salles_id' => $this->chambre->id, 'service_id' => $this->serviceId, 'date_entree' => '2026-09-01', 'date_sortie' => '2026-09-11', 'etat' => 'terminé', 'statut_sortie' => 'Guérison']);
    OccupationLit::create(['hospitalisation_id' => $h1->id, 'lit_id' => $lits[0]->id, 'debut' => '2026-09-01 10:00', 'fin' => '2026-09-11 09:00']);
    // Séjour 2 : 21 → 30 septembre (9 nuits), lit 2, décès
    $h2 = Hospitalisation::create(['patient_id' => Fabrique::patient()->id, 'lit_id' => $lits[1]->id, 'salles_id' => $this->chambre->id, 'service_id' => $this->serviceId, 'date_entree' => '2026-09-21', 'date_sortie' => '2026-09-30', 'etat' => 'terminé', 'statut_sortie' => 'Décès']);
    OccupationLit::create(['hospitalisation_id' => $h2->id, 'lit_id' => $lits[1]->id, 'debut' => '2026-09-21 14:00', 'fin' => '2026-09-30 08:00']);

    $i = app(IndicateursHospitaliersService::class)->calculer($debutMois, $debutMois->copy()->endOfMonth());

    // Calcul manuel : 19 journées / (4 lits × 30 jours) = 15,8 % ; DMS = 19 / 2 = 9,5 j ; 1 décès sur 2 sorties
    expect($i['journees_occupees'])->toBe(19)
        ->and($i['taux_occupation'])->toBe(round(19 / 120 * 100, 1))
        ->and($i['duree_moyenne_sejour'])->toBe(9.5)
        ->and($i['deces'])->toBe(1)
        ->and($i['taux_deces'])->toBe(50.0);
});

it('suit une panne d\'équipement jusqu\'à sa résolution et replanifie l\'entretien (EF-12.06)', function () {
    $technicien = Fabrique::utilisateur(['maintenance.view', 'maintenance.gerer']);
    $this->actingAs($technicien)->post(route('maintenance.store'), ['nom' => 'Autoclave', 'periodicite_jours' => 90])->assertSessionHas('success');
    $equipement = Equipement::firstOrFail();

    $this->post(route('maintenance.signaler', $equipement), ['type' => 'panne', 'description' => 'Ne chauffe plus']);
    expect($equipement->fresh()->statut)->toBe('en_panne');

    $this->post(route('maintenance.resoudre', $equipement->maintenances()->first()), ['intervention_realisee' => 'Résistance changée', 'cout' => 45000]);
    expect($equipement->fresh()->statut)->toBe('en_service')
        ->and($equipement->fresh()->prochaine_maintenance->toDateString())->toBe(today()->addDays(90)->toDateString());
    $this->get(route('maintenance.index'))->assertOk()->assertSee('Autoclave');
});
