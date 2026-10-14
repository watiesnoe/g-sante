<?php

use App\Models\CaisseSession;
use App\Models\Consultation;
use App\Models\Ordonnance;
use App\Models\Passage;
use App\Services\CaisseService;
use App\Services\StockService;
use Tests\Support\Fabrique;

/**
 * REC-02 : journée cabinet, 10 patients — accueil → consultation → ordonnance →
 * paiement → clôture. Montants exacts, écart de caisse nul.
 */
it('déroule une journée de cabinet de 10 patients avec des montants exacts et un écart nul (REC-02)', function () {
    Fabrique::etablissement('cabinet'); // paiement après la consultation, une caisse, pas de validation
    $medecin = Fabrique::utilisateur([
        'accueil.view', 'accueil.create', 'accueil.orienter', 'consultations.create', 'tickets.create',
        'ordonnances.payer', 'patients.view',
    ]);
    $this->actingAs($medecin);
    $session = Fabrique::ouvrirCaisse($medecin, 20000);

    $consultation = Fabrique::prestation(5000);
    $paracetamol = Fabrique::medicament('Paracétamol 500', 250);
    app(StockService::class)->entree($paracetamol, 100, 'L1', now()->addYear(), null);

    $attendu = 20000;
    foreach (range(1, 10) as $i) {
        $patient = Fabrique::patient();

        // 1. Accueil
        $this->postJson(route('passages.store'), ['patient_id' => $patient->id, 'motif' => 'Fièvre'])->assertOk();

        // 2. Consultation avec ordonnance (2 boîtes)
        $this->postJson(route('consultations.store'), [
            'patient_id' => $patient->id, 'medecin_id' => $medecin->id, 'motif' => 'Fièvre',
            'diagnostic' => 'Syndrome grippal', 'medicaments' => [$paracetamol->id], 'posologies' => ['1 x 3'],
            'duree_jours' => [3], 'quantites' => [2],
        ])->assertOk();

        // 3. Paiement de la consultation à la sortie (ticket), remise à un patient sur deux
        $remise = $i % 2 === 0 ? 10 : 0;
        $this->postJson(route('tickets.store'), [
            'patient_id' => $patient->id, 'service_medical_id' => $consultation->service_medical_id, 'medecin_id' => $medecin->id,
            'mode_paiement' => $i % 3 === 0 ? 'mobile' : 'especes', 'motif_remise' => $remise ? 'Indigent' : null,
            'items' => [['prestation_id' => $consultation->id, 'prix_unitaire' => 0, 'quantite' => 1, 'remise' => $remise, 'sous_total' => 0, 'service' => 'MG']],
        ])->assertOk();
        $attendu += 5000 * (1 - $remise / 100);

        // 4. Dispensation de l'ordonnance
        $ordonnance = Ordonnance::whereHas('consultation', fn($q) => $q->where('patient_id', $patient->id))->firstOrFail();
        $this->postJson(route('ordonnances.payer', $ordonnance), ['medicaments' => [$paracetamol->id => 2]])->assertOk();
        $attendu += 2 * 250;
    }

    // Tous les passages sont terminés et rattachés à leur consultation
    expect(Passage::count())->toBe(10)
        ->and(Passage::where('statut', 'termine')->count())->toBe(10)
        ->and(Consultation::whereNotNull('passage_id')->count())->toBe(10)
        ->and($paracetamol->fresh()->stock)->toBe(80);

    // 5. Clôture : le compté égal au théorique donne un écart nul
    $caisse = app(CaisseService::class);
    expect($caisse->soldeTheorique($session->fresh()))->toBe((float) $attendu);

    $cloture = $caisse->cloturer($attendu, null);
    expect((float) $cloture->ecart)->toBe(0.0)
        ->and($cloture->statut)->toBe('validee') // cabinet : pas de circuit de validation
        ->and(CaisseSession::find($session->id)->totauxParMode())->toHaveKeys(['especes', 'mobile']);
});
