<?php

use App\Models\CaisseMouvement;
use App\Models\Depense;
use App\Models\User;
use Tests\Support\Fabrique;

it('fait passer une dépense par saisie, validation par un autre responsable puis paiement en caisse (EF-12.01)', function () {
    Fabrique::etablissement('clinique'); // séparation des tâches active
    $comptable = Fabrique::utilisateur(['depenses.view', 'depenses.create', 'depenses.valider', 'depenses.payer']);
    $directeur = Fabrique::utilisateur(['depenses.view', 'depenses.valider']);
    Fabrique::ouvrirCaisse($comptable);

    $this->actingAs($comptable)->post(route('depenses.store'), [
        'nouvelle_categorie' => 'Électricité', 'objet' => 'Facture EDM septembre', 'montant' => 45000,
        'date_depense' => today()->toDateString(), 'soumettre' => 1,
    ])->assertRedirect();
    $depense = Depense::firstOrFail();
    expect($depense->statut)->toBe('soumise');

    // Paiement impossible avant validation ; validation impossible par l'auteur
    $this->post(route('depenses.action', [$depense, 'payer']))->assertSessionHas('error');
    $this->post(route('depenses.action', [$depense, 'valider']))->assertSessionHas('error');
    expect($depense->fresh()->statut)->toBe('soumise');

    $this->actingAs($directeur)->post(route('depenses.action', [$depense, 'valider']))->assertSessionHas('success');
    $this->actingAs($comptable)->post(route('depenses.action', [$depense, 'payer']), ['mode_paiement' => 'especes'])->assertSessionHas('success');

    $mouvement = CaisseMouvement::where('reference_type', Depense::class)->firstOrFail();
    expect($depense->fresh()->statut)->toBe('payee')
        ->and($mouvement->type)->toBe('sortie')
        ->and((float) $mouvement->montant)->toBe(45000.0);
});

it('verrouille un compte après 5 mots de passe erronés (SEC-02)', function () {
    Fabrique::etablissement();
    $user = User::factory()->create(['email' => 'agent@test.ml']);

    foreach (range(1, 5) as $i) {
        $this->post('/login', ['email' => 'agent@test.ml', 'password' => 'mauvais']);
        \Illuminate\Support\Facades\RateLimiter::clear('agent@test.ml|127.0.0.1');
    }

    $this->post('/login', ['email' => 'agent@test.ml', 'password' => 'password'])->assertSessionHasErrors('email');
    $this->assertGuest();
    expect($user->fresh()->verrouille_jusqua)->not->toBeNull();
});

it('déconnecte après la période d\'inactivité de l\'établissement (SEC-02)', function () {
    Fabrique::etablissement();
    $user = Fabrique::utilisateur();

    $this->actingAs($user)->withSession(['derniere_activite' => now()->subMinutes(16)->timestamp])
        ->get(route('dashboard'))->assertRedirect(route('login'));
    $this->assertGuest();
});

it('exige des mots de passe d\'au moins 10 caractères avec lettres et chiffres (SEC-02)', function () {
    // tests/Pest.php assouplit la règle pour les autres tests : on recharge celle de production
    (new \App\Providers\AppServiceProvider(app()))->boot();
    $regle = ['p' => \Illuminate\Validation\Rules\Password::defaults()];

    expect(validator(['p' => 'abc12345'], $regle)->fails())->toBeTrue()
        ->and(validator(['p' => 'motdepasse'], $regle)->fails())->toBeTrue()
        ->and(validator(['p' => 'sante2026ok'], $regle)->passes())->toBeTrue();
});

it('affiche au tableau de bord les indicateurs des modules actifs et des droits de l\'utilisateur (EF-13.01)', function () {
    Fabrique::etablissement();
    $user = Fabrique::utilisateur(['dashboard.view', 'accueil.view', 'depenses.valider']);

    $this->actingAs($user);
    $tuiles = collect(app(\App\Services\TableauDeBordService::class)->indicateurs())->pluck('titre');
    expect($tuiles)->toContain('Passages du jour', 'Dépenses à valider')->not->toContain('Alertes de stock');
});
