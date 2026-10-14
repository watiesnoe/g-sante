<?php

use App\Models\Consultation;
use App\Models\Examen;
use App\Models\Lit;
use App\Models\PrescriptionExamen;
use App\Models\Salle;
use App\Services\HospitalisationService;
use App\Services\LaboratoireService;
use App\Services\StockService;
use Tests\Support\Fabrique;

beforeEach(fn() => Fabrique::etablissement('clinique'));

it('fait suivre à un examen les étapes demandé → prélevé → réalisé → saisi → validé et signale une valeur anormale (EF-09.02 à EF-09.04)', function () {
    $laborantin = Fabrique::utilisateur(['examens.view', 'examens.results', 'examens.valider']);
    $biologiste = Fabrique::utilisateur(['examens.view', 'examens.valider']);
    $catalogue = Examen::create(['nom' => 'Glycémie', 'type' => 'laboratoire', 'unite' => 'g/L', 'valeur_min' => 0.7, 'valeur_max' => 1.1]);
    $consultation = Consultation::create(['patient_id' => Fabrique::patient()->id, 'medecin_id' => $biologiste->id, 'diagnostic' => 'x']);
    $examen = PrescriptionExamen::create(['consultation_id' => $consultation->id, 'examen_id' => $catalogue->id, 'examen' => 'Glycémie', 'type' => 'laboratoire', 'statut' => 'demande']);

    $this->actingAs($laborantin)->post(route('laboratoire.etape', $examen), ['vers' => 'realise'])->assertSessionHas('error');
    $this->post(route('laboratoire.etape', $examen), ['vers' => 'preleve'])->assertSessionHas('success');
    expect($examen->fresh()->preleve_par)->toBe($laborantin->id);

    $this->postJson(route('reponses.store'), ['prescription_examen_id' => $examen->id, 'valeur' => '1.45'])->assertOk()->assertJsonPath('data.anormal', true);
    expect($examen->fresh()->statut)->toBe('saisi');

    // Séparation des tâches : le laborantin ne valide pas sa propre saisie
    $this->post(route('laboratoire.valider', $examen))->assertSessionHas('error');
    $this->actingAs($biologiste)->post(route('laboratoire.valider', $examen))->assertSessionHas('success');
    expect($examen->fresh()->statut)->toBe('valide')->and($examen->fresh()->valide_par)->toBe($biologiste->id);

    // Un résultat validé ne se modifie plus
    $this->actingAs($laborantin)->postJson(route('reponses.store'), ['prescription_examen_id' => $examen->id, 'valeur' => '1.0'])->assertStatus(422);
});

it('n\'occupe un lit que par un patient et ne laisse qu\'un séjour ouvert par patient ; transfert et sortie libèrent le lit (RG-12, EF-10.01)', function () {
    $this->actingAs(Fabrique::utilisateur(['hospitalisations.view', 'hospitalisations.edit']));
    $salle = Salle::create(['nom' => 'Médecine', 'type' => 'commune', 'capacite' => 2, 'prix' => 5000, 'service_medical_id' => Fabrique::prestation()->service_medical_id]);
    $lit1 = Lit::create(['numero' => 'M1', 'salle_id' => $salle->id, 'statut' => 'Libre']);
    $lit2 = Lit::create(['numero' => 'M2', 'salle_id' => $salle->id, 'statut' => 'Libre']);
    $service = app(HospitalisationService::class);
    $awa = Fabrique::patient();
    $moussa = Fabrique::patient();

    $sejour = $service->admettre($awa, $lit1, ['motif' => 'Paludisme grave']);
    expect(fn() => $service->admettre($moussa, $lit1, []))->toThrow(\Illuminate\Validation\ValidationException::class)
        ->and(fn() => $service->admettre($awa, $lit2, []))->toThrow(\Illuminate\Validation\ValidationException::class);

    $this->post(route('lits.transferer', $sejour), ['lit_id' => $lit2->id])->assertSessionHas('success');
    expect($lit1->fresh()->statut)->toBe('Libre')->and($lit2->fresh()->statut)->toBe('Occupé')
        ->and($sejour->occupations()->count())->toBe(2)->and($sejour->occupations()->whereNull('fin')->value('lit_id'))->toBe($lit2->id);

    $service->sortir($sejour->fresh(), 'Guérison');
    expect($lit2->fresh()->statut)->toBe('Libre')->and($sejour->fresh()->etat)->toBe('terminé');
    $this->get(route('lits.tableau'))->assertOk()->assertSee('Lit M1');
});

it('affiche les alertes de péremption et de seuil selon les paramètres (REC-11, EF-08.04)', function () {
    $this->actingAs(Fabrique::utilisateur(['stock.view']));
    $stock = app(StockService::class);
    $proche = Fabrique::medicament('Ceftriaxone 1g');
    $proche->update(['stock_min' => 50]);
    $stock->entree($proche->fresh(), 10, 'L-20J', today()->addDays(20), null);
    $loin = Fabrique::medicament('Métronidazole');
    $stock->entree($loin, 100, 'L-LOIN', today()->addYears(2), null);

    $this->get(route('stock.alertes'))->assertOk()
        ->assertSeeInOrder(['Péremption dans moins de 90 jours (1)', 'Ceftriaxone 1g', 'L-20J'])
        ->assertSeeInOrder(['Sous le seuil d', 'Ceftriaxone 1g'])
        ->assertDontSee('L-LOIN');

    // Avec un délai de 15 jours, le lot à 20 jours n'est plus signalé
    \App\Models\Etablissement::courant()->update(['parametres' => ['stock.alerte_peremption_jours' => 15]]);
    $this->get(route('stock.alertes'))->assertSee('Péremption dans moins de 15 jours (0)');
});
