<?php

use App\Models\Accouchement;
use App\Models\Depense;
use App\Models\Grossesse;
use App\Models\LigneBudgetaire;
use App\Models\Lot;
use App\Models\Magasin;
use App\Models\Passage;
use App\Models\RapportPeriodique;
use App\Models\Reference;
use App\Models\RegleGratuite;
use App\Models\Salle;
use App\Models\Ticket;
use App\Models\Lit;
use App\Services\HospitalisationService;
use App\Services\RapportMensuelService;
use App\Services\StockService;
use Tests\Support\Fabrique;

beforeEach(fn() => Fabrique::etablissement('csref'));

it('prend en charge une urgence sans paiement préalable puis régularise la facture (REC-05, RG-06, EF-05.06)', function () {
    $infirmier = Fabrique::utilisateur(['urgences.view', 'urgences.gerer', 'consultations.create', 'tickets.create']);
    $this->actingAs($infirmier);
    $patient = Fabrique::patient();

    // Arrivée et triage sans ticket ni caisse ouverte
    $this->post(route('urgences.store'), ['patient_id' => $patient->id, 'motif' => 'Détresse respiratoire', 'priorite' => 1])->assertSessionHas('success');
    $passage = Passage::firstOrFail();
    expect($passage->urgence)->toBeTrue()->and($passage->statut)->toBe('trie')->and($passage->ticket_id)->toBeNull();

    $this->post(route('urgences.prise_en_charge', $passage))->assertSessionHas('success');
    $this->postJson(route('consultations.store'), ['patient_id' => $patient->id, 'medecin_id' => $infirmier->id, 'diagnostic' => 'Crise d\'asthme'])->assertOk();
    $this->post(route('urgences.decision', $passage), ['decision' => 'retour_domicile', 'orientation' => 'Pneumologie dans 1 semaine']);

    $passage->refresh();
    expect($passage->pris_en_charge_at)->not->toBeNull()->and($passage->statut)->toBe('termine')->and($passage->decision)->toBe('retour_domicile');

    // Régularisation après coup, rattachée au passage d'urgence
    Fabrique::ouvrirCaisse($infirmier);
    $acte = Fabrique::prestation(10000);
    $this->postJson(route('tickets.store'), [
        'patient_id' => $patient->id, 'service_medical_id' => $acte->service_medical_id, 'medecin_id' => $infirmier->id, 'passage_id' => $passage->id,
        'items' => [['prestation_id' => $acte->id, 'prix_unitaire' => 0, 'quantite' => 1, 'remise' => 0, 'sous_total' => 0, 'service' => 'Urgences']],
    ])->assertOk();
    expect($passage->fresh()->ticket_id)->toBe(Ticket::first()->id);

    $this->get(route('urgences.index', ['format' => 'csv']))->assertOk();
});

it('compte une seule fois dans chaque rubrique un patient référé par un CSCom, hospitalisé puis évacué (REC-06)', function () {
    $medecin = Fabrique::utilisateur(['references.gerer', 'references.view', 'hospitalisations.edit']);
    $this->actingAs($medecin);
    $patient = Fabrique::patient();

    $this->post(route('references.store'), ['sens' => 'entrante', 'patient_id' => $patient->id, 'structure_origine' => 'CSCom de Pelengana', 'motif' => 'Paludisme grave', 'date_reference' => now()->toDateTimeString()]);
    $salle = Salle::create(['nom' => 'Pédiatrie', 'type' => 'commune', 'capacite' => 1, 'prix' => 0, 'service_medical_id' => Fabrique::prestation()->service_medical_id]);
    $sejour = app(HospitalisationService::class)->admettre($patient, Lit::create(['numero' => 'P1', 'salle_id' => $salle->id, 'statut' => 'Libre']), []);
    $this->post(route('references.store'), ['sens' => 'sortante', 'evacuation' => 1, 'patient_id' => $patient->id, 'structure_destination' => 'Hôpital de Ségou', 'motif' => 'Aggravation', 'transport' => 'ambulance', 'date_reference' => now()->toDateTimeString()]);
    app(HospitalisationService::class)->sortir($sejour, 'Transfert');

    $i = app(RapportMensuelService::class)->calculer(today()->startOfMonth(), today()->endOfMonth())['indicateurs'];
    expect($i['references.recues'])->toBe(1)
        ->and($i['references.emises'])->toBe(1)
        ->and($i['references.evacuations'])->toBe(1)
        ->and($i['hospitalisations.admissions'])->toBe(1)
        ->and($i['hospitalisations.sorties'])->toBe(1)
        ->and($i['hospitalisations.deces'])->toBe(0);
});

it('fige un rapport transmis et crée une nouvelle version pour le corriger (RG-15, EF-13.03)', function () {
    $statisticien = Fabrique::utilisateur(['rapports.mensuel']);
    $medecinChef = Fabrique::utilisateur(['rapports.mensuel', 'rapports.valider']);
    $mois = today()->format('Y-m');

    $this->actingAs($statisticien)->post(route('rapports-mensuels.generer'), ['mois' => $mois])->assertRedirect();
    $rapport = RapportPeriodique::firstOrFail();
    $this->actingAs($medecinChef)->post(route('rapports-mensuels.etape', $rapport), ['etape' => 'valider']);
    $this->post(route('rapports-mensuels.etape', $rapport), ['etape' => 'transmettre']);
    expect($rapport->fresh()->statut)->toBe('transmis')
        ->and(fn() => $rapport->fresh()->update(['donnees' => []]))->toThrow(LogicException::class);

    $this->post(route('rapports-mensuels.generer'), ['mois' => $mois])->assertSessionHas('error');
    $this->post(route('rapports-mensuels.etape', $rapport), ['etape' => 'nouvelle_version', 'motif' => 'Accouchements oubliés'])->assertRedirect();
    expect(RapportPeriodique::where('version', 2)->where('statut', 'brouillon')->exists())->toBeTrue();

    $this->get(route('rapports-mensuels.export', [$rapport, 'csv']))->assertOk();
    $this->get(route('rapports-mensuels.export', [$rapport, 'dhis2']))->assertOk()->assertJsonPath('period', today()->format('Ym'));
});

it('enregistre un accouchement, ouvre un dossier pour l\'enfant et clôt la grossesse (EF-10.04)', function () {
    $sageFemme = Fabrique::utilisateur(['maternity.view', 'maternity.accouchement']);
    $mere = Fabrique::patient();
    $grossesse = Grossesse::create(['patient_id' => $mere->id, 'ddr' => now()->subMonths(9), 'dpa' => now(), 'statut' => 'En cours']);

    $this->actingAs($sageFemme)->post(route('accouchements.store'), [
        'patient_id' => $mere->id, 'date_heure' => now()->subHour()->format('Y-m-d H:i'), 'mode' => 'voie_basse', 'terme_sa' => 39, 'etat_mere' => 'bon',
        'enfants' => [['sexe' => 'F', 'etat' => 'vivant', 'poids_g' => 3100, 'apgar_1' => 8, 'apgar_5' => 10]],
    ])->assertRedirect(route('accouchements.index'));

    $accouchement = Accouchement::with('nouveauNes.patient')->firstOrFail();
    expect($accouchement->nouveauNes->first()->patient->date_naissance->toDateString() ?? null)->toBe(today()->toDateString())
        ->and($grossesse->fresh()->statut)->toBe('Terminée');
    $this->get(route('accouchements.index'))->assertOk()->assertSee('3100 g');
});

it('applique automatiquement une gratuité aux enfants de moins de 5 ans (EF-05.03)', function () {
    $acte = Fabrique::prestation(2000);
    $regle = RegleGratuite::create(['nom' => 'Règle de test', 'age_max_mois' => 59, 'portee_actes' => 'liste', 'couvre_medicaments' => true, 'date_effet' => today()->subYear(), 'actif' => true]);
    $regle->prestations()->sync([$acte->id]);
    $caissier = Fabrique::utilisateur(['tickets.create']);
    Fabrique::ouvrirCaisse($caissier);
    $enfant = Fabrique::patient();
    $enfant->update(['age' => 3]);
    $adulte = Fabrique::patient();

    foreach ([$enfant, $adulte] as $p) {
        $this->actingAs($caissier)->postJson(route('tickets.store'), [
            'patient_id' => $p->id, 'service_medical_id' => $acte->service_medical_id, 'medecin_id' => $caissier->id,
            'items' => [['prestation_id' => $acte->id, 'prix_unitaire' => 0, 'quantite' => 1, 'remise' => 0, 'sous_total' => 0, 'service' => 'MG']],
        ])->assertOk();
    }

    $ticketEnfant = Ticket::where('patient_id', $enfant->id)->first();
    $ticketAdulte = Ticket::where('patient_id', $adulte->id)->first();
    expect((float) $ticketEnfant->part_patient)->toBe(0.0)->and((float) $ticketEnfant->part_gratuite)->toBe(2000.0)
        ->and((float) $ticketAdulte->part_patient)->toBe(2000.0)
        ->and(\App\Models\CaisseMouvement::count())->toBe(1);
});

it('transfère un lot de la pharmacie centrale vers un dépôt et ne vend que dans le dépôt (EF-08.07)', function () {
    $stock = app(StockService::class);
    $med = Fabrique::medicament('Artésunate inj.');
    $central = Magasin::create(['nom' => 'Pharmacie centrale', 'type' => 'central', 'par_defaut' => false, 'actif' => true]);
    $depot = Magasin::parDefaut();
    $stock->entree($med, 50, 'L-ART', now()->addYear(), null, null, 'entree', $central);

    expect($stock->disponible($med))->toBe(0);
    $stock->transferer($med, 20, $central, $depot);

    expect($stock->disponible($med))->toBe(20)
        ->and($stock->disponible($med, $central))->toBe(30)
        ->and($med->fresh()->stock)->toBe(50)
        ->and(Lot::where('numero', 'L-ART')->count())->toBe(2);
});

it('bloque la validation d\'une dépense qui dépasse sa ligne budgétaire (EF-12.03)', function () {
    $ligne = LigneBudgetaire::create(['exercice' => now()->year, 'code' => '6061', 'libelle' => 'Carburant', 'montant_prevu' => 100000]);
    $comptable = Fabrique::utilisateur(['depenses.view', 'depenses.create']);
    $directeur = Fabrique::utilisateur(['depenses.view', 'depenses.valider']);

    $this->actingAs($comptable)->post(route('depenses.store'), ['nouvelle_categorie' => 'Carburant', 'ligne_budgetaire_id' => $ligne->id, 'objet' => 'Ambulance', 'montant' => 120000, 'date_depense' => today()->toDateString(), 'soumettre' => 1]);
    $depense = Depense::firstOrFail();

    $this->actingAs($directeur)->post(route('depenses.action', [$depense, 'valider']))->assertSessionHas('error');
    expect($depense->fresh()->statut)->toBe('soumise');
});

it('suit une référence sortante jusqu\'à la contre-référence et imprime les fiches (EF-11)', function () {
    $medecin = Fabrique::utilisateur(['references.gerer', 'references.view']);
    $this->actingAs($medecin)->post(route('references.store'), ['sens' => 'entrante', 'patient_id' => Fabrique::patient()->id, 'structure_origine' => 'CSCom A', 'motif' => 'Anémie', 'date_reference' => now()->toDateTimeString()]);
    $ref = Reference::firstOrFail();
    expect($ref->statut)->toBe('recue');

    $this->post(route('references.etape', $ref), ['vers' => 'contre_reference_envoyee', 'contre_reference' => 'x'])->assertSessionHas('error');
    $this->post(route('references.etape', $ref), ['vers' => 'prise_en_charge']);
    $this->post(route('references.etape', $ref), ['vers' => 'contre_reference_envoyee', 'contre_reference' => 'Transfusé, retour au CSCom'])->assertSessionHas('success');
    $this->get(route('references.fiche', [$ref, 'contre-reference']))->assertOk()->assertHeader('content-type', 'application/pdf');
});
