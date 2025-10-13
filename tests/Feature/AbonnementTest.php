<?php

use App\Models\Abonnement;
use App\Models\Etablissement;
use App\Models\Formule;
use App\Models\PaiementAbonnement;
use App\Models\User;
use App\Services\AbonnementService;
use App\Services\SmsService;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\Fabrique;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-11-01 08:00'));
    Fabrique::etablissement('csref');
    $this->seed(PermissionRoleSeeder::class);
    $this->superAdmin = User::where('email', 'superadmin@exemple.com')->sole();
    $this->actingAs($this->superAdmin);

    $this->post(route('plateforme.formules.store'), [
        'nom' => 'Silver', 'prix' => 150000, 'duree_mois' => 12, 'jours_grace' => 7,
        'modules' => ['rendezvous', 'ordonnance', 'stock', 'examens', 'urgences'],
        'modules_coupes' => ['stock', 'examens'], 'max_utilisateurs' => 3, 'max_lits' => 1, 'max_sms_mois' => 2,
    ])->assertSessionHasNoErrors();
    $this->post(route('plateforme.formules.store'), [
        'nom' => 'Gold', 'prix' => 400000, 'duree_mois' => 12, 'jours_grace' => 15,
        'modules' => ['rendezvous', 'ordonnance', 'stock', 'examens', 'urgences', 'hospitalisation', 'maternity'],
    ])->assertSessionHasNoErrors();
    $this->silver = Formule::where('nom', 'Silver')->sole();

    $this->post(route('plateforme.store'), [
        'nom' => 'Cabinet Bamako', 'type' => 'cabinet', 'code' => 'CABBKO', 'formule_id' => $this->silver->id, 'debut' => '2026-11-01',
        'proprietaire' => ['prenom' => 'Moussa', 'nom' => 'Coulibaly', 'email' => 'moussa@cab.ml', 'telephone' => '76001122'],
    ])->assertRedirect(route('plateforme.index'));
    $this->espace = Etablissement::where('code', 'CABBKO')->sole();
    $this->proprietaire = User::where('email', 'moussa@cab.ml')->sole();
    $this->proprietaire->forceFill(['doit_changer_mot_de_passe' => false])->save();
});

it('crée un espace abonné : ses modules sont ceux de la formule, pour la durée payée', function () {
    $abonnement = $this->espace->abonnement;
    expect($abonnement->formule->nom)->toBe('Silver')
        ->and($abonnement->debut->toDateString())->toBe('2026-11-01')
        ->and($abonnement->fin->toDateString())->toBe('2027-10-31')
        ->and($this->espace->modules)->toBe(['rendezvous', 'ordonnance', 'stock', 'examens', 'urgences']);

    $this->get(route('plateforme.index'))->assertOk()->assertSee('Silver')->assertSee('31/10/2027');
});

it('rappelle l\'échéance, accorde la grâce puis coupe seulement les modules prévus (jamais les urgences)', function () {
    $service = app(AbonnementService::class);

    $this->travelTo(Carbon::parse('2027-10-17'));   // J-14
    expect($service->verifier()['rappels'])->toBe(1);
    expect(DB::table('sms_envois')->where('telephone', '76001122')->where('message', 'like', '%arrive à échéance dans 14 jour%')->exists())->toBeTrue();
    expect($service->verifier()['rappels'])->toBe(0); // pas de doublon

    $this->travelTo(Carbon::parse('2027-10-29'));   // J-2
    expect($service->verifier()['rappels'])->toBe(1);

    $this->travelTo(Carbon::parse('2027-11-02'));   // échu, dans la grâce de 7 jours
    $service->verifier();
    expect($this->espace->abonnement->fresh()->statut)->toBe('grace')
        ->and($this->espace->fresh()->moduleActif('stock'))->toBeTrue();

    $this->travelTo(Carbon::parse('2027-11-08'));   // grâce dépassée
    expect($service->verifier()['restreints'])->toBe(1);
    $espace = $this->espace->fresh();
    expect($espace->modules_suspendus)->toBe(['stock', 'examens'])
        ->and($espace->moduleActif('stock'))->toBeFalse()
        ->and($espace->moduleActif('examens'))->toBeFalse()
        ->and($espace->moduleActif('urgences'))->toBeTrue()
        ->and($espace->moduleActif('ordonnance'))->toBeTrue();

    // Le personnel voit pourquoi ; les pages du module coupé sont bloquées
    $this->flushSession(); // session d'il y a un an : sinon déconnexion pour inactivité
    $this->actingAs($this->proprietaire);
    $this->get(route('dashboard'))->assertOk()->assertSee('certains modules sont temporairement coupés');
    expect($this->proprietaire->fresh()->hasModuleAccess('stock'))->toBeFalse();
});

it('rétablit tout au paiement et délivre un reçu', function () {
    $service = app(AbonnementService::class);
    $this->travelTo(Carbon::parse('2027-11-20'));
    $service->verifier();
    expect($this->espace->fresh()->moduleActif('stock'))->toBeFalse();

    $this->flushSession();
    $this->actingAs($this->superAdmin)->post(route('plateforme.payer', $this->espace), [
        'montant' => 150000, 'mode' => 'mobile', 'reference' => 'OM-123', 'paye_le' => '2027-11-20',
    ])->assertRedirect(route('plateforme.index'));

    $paiement = PaiementAbonnement::sole();
    $abonnement = $this->espace->abonnement->fresh();
    // Espace restreint : la nouvelle période part du jour du paiement
    expect($paiement->numero)->toBe('AB-2027-000001')
        ->and($paiement->periode_debut->toDateString())->toBe('2027-11-20')
        ->and($abonnement->fin->toDateString())->toBe('2028-11-19')
        ->and($abonnement->statut)->toBe('actif')
        ->and($this->espace->fresh()->moduleActif('stock'))->toBeTrue();

    $this->get(route('plateforme.recu', $paiement))->assertOk()->assertSee('AB-2027-000001')->assertSee('150 000 XOF');
});

it('prolonge sans trou ni cadeau un abonnement payé à l\'avance', function () {
    $this->post(route('plateforme.payer', $this->espace), ['montant' => 150000, 'mode' => 'especes', 'paye_le' => '2026-11-01']);
    expect(PaiementAbonnement::sole()->periode_debut->toDateString())->toBe('2027-11-01')
        ->and($this->espace->abonnement->fresh()->fin->toDateString())->toBe('2028-10-31');
});

it('change de formule : les modules suivent, les dates restent', function () {
    $gold = Formule::where('nom', 'Gold')->sole();
    $this->post(route('plateforme.abonner', $this->espace), ['formule_id' => $gold->id, 'action' => 'changer'])->assertRedirect();

    $espace = $this->espace->fresh();
    expect($espace->abonnement->formule_id)->toBe($gold->id)
        ->and($espace->abonnement->fin->toDateString())->toBe('2027-10-31')
        ->and($espace->moduleActif('hospitalisation'))->toBeTrue();
});

it('applique les limites de la formule : utilisateurs, lits et SMS', function () {
    $this->actingAs($this->proprietaire);
    app()->instance('etablissement.courant', $this->espace);

    // 3 utilisateurs max : le propriétaire + 2
    foreach (['a', 'b'] as $n) {
        $this->post(route('users.store'), ['prenom' => $n, 'nom' => 'Agent', 'email' => "{$n}@cab.ml", 'role' => 'secretaire', 'password' => 'Agent2026xx', 'password_confirmation' => 'Agent2026xx'])
            ->assertSessionHasNoErrors();
    }
    $this->post(route('users.store'), ['prenom' => 'c', 'nom' => 'Agent', 'email' => 'c@cab.ml', 'role' => 'secretaire', 'password' => 'Agent2026xx', 'password_confirmation' => 'Agent2026xx'])
        ->assertSessionHasErrors('email');
    expect(User::where('email', 'c@cab.ml')->exists())->toBeFalse();

    // 2 SMS par mois
    $sms = app(SmsService::class);
    expect($sms->envoyer('70000001', 'un'))->toBeTrue()
        ->and($sms->envoyer('70000002', 'deux'))->toBeTrue()
        ->and($sms->envoyer('70000003', 'trois'))->toBeFalse();
    expect(DB::table('sms_envois')->where('message', 'trois')->value('erreur'))->toContain('Quota de 2 SMS');

    // 1 lit
    $limite = app(AbonnementService::class);
    $salle = \App\Models\Salle::create(['nom' => 'Salle 1', 'type' => 'commune', 'capacite' => 2, 'prix' => 0, 'service_medical_id' => Fabrique::prestation()->service_medical_id]);
    \App\Models\Lit::create(['numero' => 'L1', 'salle_id' => $salle->id, 'statut' => 'Libre']);
    expect(\App\Models\Lit::count())->toBe(1);
    expect($limite->depassement($this->espace, 'lits'))->toContain('1 lit');
});

it('refuse de couper les urgences, l\'hospitalisation ou la maternité', function () {
    $this->post(route('plateforme.formules.store'), [
        'nom' => 'Risquée', 'duree_mois' => 12, 'jours_grace' => 0, 'modules' => ['urgences', 'maternity'], 'modules_coupes' => ['urgences'],
    ])->assertSessionHasErrors('modules_coupes.0');
    expect(Formule::where('nom', 'Risquée')->exists())->toBeFalse();
});

it('réserve les formules et les paiements au super-admin', function () {
    $this->actingAs($this->proprietaire);
    $this->get(route('plateforme.formules'))->assertForbidden();
    $this->post(route('plateforme.payer', $this->espace), ['montant' => 1, 'mode' => 'especes', 'paye_le' => '2026-11-01'])->assertForbidden();
    expect(PaiementAbonnement::count())->toBe(0);
});
