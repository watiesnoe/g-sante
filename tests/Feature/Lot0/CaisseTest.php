<?php

use App\Models\CaisseMouvement;
use App\Models\Ticket;
use App\Services\CaisseService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Models\Activity;
use Tests\Support\Fabrique;

beforeEach(function () {
    // Clinique : validation des clôtures et séparation des tâches actives
    Fabrique::etablissement('clinique');
    $this->caissier = Fabrique::utilisateur(['tickets.create', 'tickets.edit', 'tickets.delete']);
    $this->responsable = Fabrique::utilisateur(['tickets.delete', 'caisse.annuler', 'caisse.valider_cloture']);
    $this->session = Fabrique::ouvrirCaisse($this->caissier);
});

function creerTicket($test, float $prix = 5000): Ticket
{
    $prestation = Fabrique::prestation($prix);
    $test->actingAs($test->caissier)->postJson(route('tickets.store'), [
        'patient_id' => Fabrique::patient()->id,
        'service_medical_id' => $prestation->service_medical_id,
        'medecin_id' => $test->responsable->id,
        'items' => [['prestation_id' => $prestation->id, 'prix_unitaire' => $prix, 'quantite' => 1, 'remise' => 0, 'sous_total' => $prix, 'service' => 'MG']],
    ])->assertOk();

    return Ticket::latest('id')->first();
}

it('annule un paiement avec motif, écriture inverse et trace dans le journal d\'audit (REC-08, RG-01)', function () {
    $ticket = creerTicket($this);
    Fabrique::ouvrirCaisse($this->responsable);

    $this->actingAs($this->responsable)->deleteJson(route('tickets.destroy', $ticket), [])->assertStatus(422);
    $this->actingAs($this->responsable)->deleteJson(route('tickets.destroy', $ticket), ['motif' => 'Doublon de saisie'])->assertOk();

    $original = CaisseMouvement::where('reference_id', $ticket->id)->whereNull('mouvement_annule_id')->first();
    $inverse = CaisseMouvement::where('mouvement_annule_id', $original->id)->first();

    expect($ticket->fresh()->statut)->toBe('annule')
        ->and($original->statut)->toBe('annule')
        ->and($original->motif_annulation)->toBe('Doublon de saisie')
        ->and($original->annule_par)->toBe($this->responsable->id)
        ->and($inverse->type)->toBe('sortie')
        ->and((float) $inverse->montant)->toBe(5000.0)
        ->and(Activity::where('subject_type', CaisseMouvement::class)->where('subject_id', $original->id)->where('event', 'updated')->exists())->toBeTrue();
});

it('refuse qu\'un caissier annule seul son encaissement (RG-03)', function () {
    $ticket = creerTicket($this);

    $this->actingAs($this->caissier)->deleteJson(route('tickets.destroy', $ticket), ['motif' => 'Essai'])->assertStatus(403);
    expect($ticket->fresh()->statut)->not->toBe('annule');
});

it('refuse de modifier un paiement, y compris d\'une caisse clôturée (REC-14, RG-02)', function () {
    $ticket = creerTicket($this);
    $mouvement = CaisseMouvement::where('reference_id', $ticket->id)->first();
    $this->actingAs($this->caissier);
    app(CaisseService::class)->cloturer(5000, null);

    expect(fn () => $mouvement->update(['montant' => 1]))->toThrow(LogicException::class)
        ->and(fn () => $mouvement->delete())->toThrow(LogicException::class)
        ->and(fn () => $this->session->fresh()->update(['solde_initial' => 99]))->toThrow(LogicException::class);

    // Modifier le montant facturé d'un ticket encaissé est refusé
    $autre = Fabrique::prestation(9000);
    $this->putJson(route('tickets.update', $ticket), [
        'service_medical_id' => $autre->service_medical_id, 'medecin_id' => $this->responsable->id,
        'items' => [['prestation_id' => $autre->id, 'quantite' => 1, 'remise' => 0, 'service' => 'MG']],
    ])->assertStatus(422);
    expect((float) $ticket->fresh()->total)->toBe(5000.0);
});

it('exige une justification quand la clôture présente un écart (EF-06.04)', function () {
    creerTicket($this);
    $this->actingAs($this->caissier);

    expect(fn () => app(CaisseService::class)->cloturer(4000, ''))->toThrow(ValidationException::class);

    $session = app(CaisseService::class)->cloturer(4000, 'Monnaie rendue en trop');
    expect((float) $session->ecart)->toBe(-1000.0)->and($session->statut)->toBe('fermee');
});

it('refuse qu\'un caissier valide sa propre clôture quand la séparation des tâches est active (REC-07)', function () {
    $this->caissier->givePermissionTo('caisse.valider_cloture');
    $this->actingAs($this->caissier);
    app(CaisseService::class)->cloturer(0, null);

    $this->post(route('caisse.valider', $this->session))->assertSessionHas('error');
    expect($this->session->fresh()->statut)->toBe('fermee');

    $this->actingAs($this->responsable)->post(route('caisse.valider', $this->session))->assertSessionHas('success');
    expect($this->session->fresh()->statut)->toBe('validee');
});

it('compte un encaissement annulé dans sa caisse d\'origine et sa compensation dans celle du responsable', function () {
    $ticket = creerTicket($this);
    $sessionResponsable = Fabrique::ouvrirCaisse($this->responsable, 10000);
    $this->actingAs($this->responsable)->deleteJson(route('tickets.destroy', $ticket), ['motif' => 'Remboursement'])->assertOk();

    $service = app(CaisseService::class);
    expect($service->soldeTheorique($this->session->fresh()))->toBe(5000.0)
        ->and($service->soldeTheorique($sessionResponsable->fresh()))->toBe(5000.0);
});

it('rend le journal d\'audit non modifiable, même directement en base (SEC-07)', function () {
    creerTicket($this);
    $id = Activity::query()->value('id');

    expect(fn () => DB::table('activity_log')->where('id', $id)->update(['description' => 'falsifié']))->toThrow(QueryException::class)
        ->and(fn () => DB::table('activity_log')->where('id', $id)->delete())->toThrow(QueryException::class);
});

it('exige un motif pour toute remise (RG-04)', function () {
    $prestation = Fabrique::prestation(5000);
    $this->actingAs($this->caissier)->postJson(route('tickets.store'), [
        'patient_id' => Fabrique::patient()->id, 'service_medical_id' => $prestation->service_medical_id, 'medecin_id' => $this->responsable->id,
        'items' => [['prestation_id' => $prestation->id, 'prix_unitaire' => 5000, 'quantite' => 1, 'remise' => 20, 'sous_total' => 4000, 'service' => 'MG']],
    ])->assertStatus(422)->assertJsonValidationErrors('motif_remise');
});
