<?php

use App\Models\Etablissement;
use App\Models\User;
use Database\Seeders\PermissionRoleSeeder;
use Tests\Support\Fabrique;

beforeEach(function () {
    Fabrique::etablissement('csref');
    $this->seed(PermissionRoleSeeder::class);
    $this->superAdmin = User::where('email', 'superadmin@exemple.com')->sole();
});

function creerEspace($test, array $proprietaire = []): array
{
    $test->actingAs($test->superAdmin)->post(route('plateforme.store'), [
        'nom' => 'Clinique Espoir', 'type' => 'clinique', 'code' => 'ESPOIR', 'district' => 'Kati',
        'modules' => ['ordonnance', 'stock', 'examens'],
        'proprietaire' => $proprietaire + ['prenom' => 'Fatoumata', 'nom' => 'Sidibé', 'email' => 'f.sidibe@espoir.ml'],
    ])->assertRedirect(route('plateforme.index'));
    $identifiants = session('identifiants');
    auth()->logout();

    return [Etablissement::where('code', 'ESPOIR')->sole(), User::where('email', 'f.sidibe@espoir.ml')->sole(), $identifiants['mot_de_passe']];
}

function connecter($test, string $email, string $motDePasse): void
{
    $test->post('/login', ['email' => $email, 'password' => $motDePasse])->assertRedirect();
}

it('crée un espace et son propriétaire, qui change son mot de passe provisoire à la première connexion', function () {
    [$espace, $proprietaire, $provisoire] = creerEspace($this);

    expect($espace->modules)->toBe(['ordonnance', 'stock', 'examens'])
        ->and($proprietaire->hasRole('admin'))->toBeTrue()
        ->and($proprietaire->etablissements->pluck('id')->all())->toBe([$espace->id])
        ->and($proprietaire->doit_changer_mot_de_passe)->toBeTrue()
        ->and(strlen($provisoire))->toBe(12);

    connecter($this, 'f.sidibe@espoir.ml', $provisoire);
    $this->assertAuthenticatedAs($proprietaire);
    // Rien d'autre n'est accessible tant que le mot de passe provisoire n'est pas changé
    $this->get(route('dashboard'))->assertRedirect(route('profile.edit'));
    $this->get(route('profile.edit'))->assertOk()->assertSee('mot de passe provisoire');

    $this->put(route('password.update'), ['current_password' => $provisoire, 'password' => 'Espoir2026ok', 'password_confirmation' => 'Espoir2026ok']);
    expect($proprietaire->fresh()->doit_changer_mot_de_passe)->toBeFalse();
    $this->get(route('dashboard'))->assertOk();

    // Il travaille dans son seul établissement et ne voit pas la plateforme
    expect(Etablissement::courantId())->toBe($espace->id)
        ->and($proprietaire->fresh()->etablissementsAccessibles()->pluck('id')->all())->toBe([$espace->id]);
    $this->get(route('plateforme.index'))->assertForbidden();
});

it('laisse le propriétaire configurer son établissement mais pas ses modules ni son type', function () {
    [$espace, $proprietaire] = creerEspace($this);
    $proprietaire->forceFill(['doit_changer_mot_de_passe' => false])->save();
    $this->actingAs($proprietaire);

    $this->get(route('etablissement.edit'))->assertOk()->assertSee('gérés par l\'administrateur de la plateforme', false)
        ->assertDontSee('Règles de fonctionnement')->assertDontSee('Modules activés')->assertDontSee('Serveur en ligne');
    $this->put(route('etablissement.update'), ['nom' => 'Clinique Espoir de Kati', 'type' => 'hopital', 'modules' => ['bloc', 'maternity']])
        ->assertRedirect(route('etablissement.edit'));

    $espace->refresh();
    expect($espace->nom)->toBe('Clinique Espoir de Kati')
        ->and($espace->type)->toBe('clinique')
        ->and($espace->modules)->toBe(['ordonnance', 'stock', 'examens']);

    // La plateforme, elle, change les modules
    $this->actingAs($this->superAdmin)->put(route('plateforme.modules', $espace), ['type' => 'clinique', 'modules' => ['ordonnance', 'stock', 'examens', 'maternity']]);
    expect($espace->fresh()->modules)->toContain('maternity');
});

it('empêche un propriétaire d\'accorder des droits de plateforme ou des droits qu\'il n\'a pas', function () {
    [$espace, $proprietaire] = creerEspace($this);
    $proprietaire->forceFill(['doit_changer_mot_de_passe' => false])->save();
    $this->actingAs($proprietaire);
    expect($proprietaire->can('roles.edit'))->toBeFalse();

    $this->post(route('users.store'), [
        'prenom' => 'Ali', 'nom' => 'Koné', 'email' => 'ali@espoir.ml', 'role' => 'comptable',
        'password' => 'Comptable2026', 'password_confirmation' => 'Comptable2026',
        'permissions' => ['etablissements.gerer', 'roles.edit', 'stock.view', 'consultations.create'],
    ])->assertRedirect(route('users.index'));

    $ali = User::where('email', 'ali@espoir.ml')->sole();
    expect($ali->getDirectPermissions()->pluck('name')->all())->toBe(['stock.view'])
        ->and($ali->can('etablissements.gerer'))->toBeFalse()
        ->and($ali->etablissements->pluck('id')->all())->toBe([$espace->id]);

    // Pas de création de super-admin
    $this->post(route('users.store'), ['prenom' => 'X', 'nom' => 'Y', 'email' => 'x@y.ml', 'role' => 'super_admin', 'password' => 'Superadmin2026', 'password_confirmation' => 'Superadmin2026'])
        ->assertForbidden();
});

it('empêche un membre du personnel de changer son propre rôle en modifiant son profil', function () {
    $medecin = User::where('email', 'medecin@exemple.com')->sole();
    $medecin->etablissements()->sync([Etablissement::courantId()]);
    $this->actingAs($medecin);

    $this->put(route('users.update', $medecin), [
        'prenom' => 'Jean', 'nom' => 'Dupont', 'email' => 'medecin@exemple.com', 'role' => 'admin', 'statut' => 'actif',
        'permissions' => ['etablissements.gerer'],
    ]);

    $medecin->refresh();
    expect($medecin->hasRole('admin'))->toBeFalse()->and($medecin->hasRole('medecin'))->toBeTrue()
        ->and($medecin->can('etablissements.gerer'))->toBeFalse();
});

it('suspend un espace : ses utilisateurs ne peuvent plus travailler, ses données restent (puis réactivation)', function () {
    [$espace, $proprietaire] = creerEspace($this);
    $proprietaire->forceFill(['doit_changer_mot_de_passe' => false])->save();

    $this->actingAs($this->superAdmin)->post(route('plateforme.suspendre', $espace), ['motif' => 'Abonnement échu'])->assertRedirect();
    expect($espace->fresh()->actif)->toBeFalse();

    $this->actingAs($proprietaire)->get(route('dashboard'))->assertRedirect(route('login'));
    $this->assertGuest();
    expect(session('errors')->first('email'))->toContain('suspendu')->toContain('Abonnement échu');

    $this->actingAs($this->superAdmin)->post(route('plateforme.reactiver', $espace));
    $this->actingAs($proprietaire->fresh())->get(route('dashboard'))->assertOk();
});

it('donne un nouveau mot de passe provisoire à un propriétaire qui a perdu le sien', function () {
    [$espace, $proprietaire, $ancien] = creerEspace($this);

    $this->actingAs($this->superAdmin)->post(route('plateforme.reinitialiser', [$espace, $proprietaire]))->assertRedirect();
    $nouveau = session('identifiants')['mot_de_passe'];
    auth()->logout();

    expect($nouveau)->not->toBe($ancien);
    $this->post('/login', ['email' => 'f.sidibe@espoir.ml', 'password' => $ancien]);
    $this->assertGuest();
    connecter($this, 'f.sidibe@espoir.ml', $nouveau);
    $this->assertAuthenticatedAs($proprietaire);
    $this->get(route('dashboard'))->assertRedirect(route('profile.edit'));

    // Un compte qui n'est pas propriétaire de cet espace n'est pas concerné
    $this->actingAs($this->superAdmin)->post(route('plateforme.reinitialiser', [$espace, $this->superAdmin]))->assertNotFound();
});

it('réserve l\'espace plateforme au super-admin', function () {
    $admin = User::where('email', 'admin@exemple.com')->sole();
    $admin->etablissements()->sync([Etablissement::courantId()]);
    $this->actingAs($admin);
    $this->get(route('plateforme.index'))->assertForbidden();
    $this->post(route('plateforme.store'), [])->assertForbidden();

    $this->actingAs($this->superAdmin)->get(route('plateforme.index'))->assertOk()->assertSee('Nouvel espace');
    $this->get('/etablissements')->assertRedirect('/plateforme');
});

it('organise l\'administration : tableau de bord, propriétaires, administrateurs, rôles et permissions', function () {
    [$espace, $proprietaire] = creerEspace($this);
    $this->actingAs($this->superAdmin);

    $this->get(route('plateforme.tableau'))->assertOk()->assertSee('Tableau de bord')->assertSee('Propriétaires');
    $this->get(route('plateforme.proprietaires'))->assertOk()->assertSee('Fatoumata')->assertSee('Clinique Espoir')->assertSee('jamais connecté');
    $this->get(route('admin.roles.index'))->assertOk()->assertSee('Gestion des accès');
    $this->get(route('admin.permissions.index'))->assertOk()
        ->assertSee('Pharmacie / Stock')->assertSee('stock.servir')->assertSee('etablissements.gerer');

    // Désactiver puis réactiver un propriétaire
    $this->post(route('plateforme.comptes.basculer', $proprietaire))->assertSessionHas('success');
    expect($proprietaire->fresh()->statut)->toBe('inactif');
    $this->post(route('plateforme.comptes.basculer', $proprietaire));
    expect($proprietaire->fresh()->statut)->toBe('actif');

    // Le propriétaire n'accède pas à l'administration
    $proprietaire->forceFill(['doit_changer_mot_de_passe' => false])->save();
    $this->actingAs($proprietaire);
    foreach (['plateforme.tableau', 'plateforme.proprietaires', 'plateforme.administrateurs'] as $route) {
        $this->get(route($route))->assertForbidden();
    }
});

it('ajoute un administrateur de la plateforme, qui ne peut pas se désactiver lui-même', function () {
    $this->actingAs($this->superAdmin)->post(route('plateforme.administrateurs.store'), ['prenom' => 'Awa', 'nom' => 'Diallo', 'email' => 'awa@plateforme.ml'])
        ->assertRedirect(route('plateforme.administrateurs'));
    $awa = User::where('email', 'awa@plateforme.ml')->sole();
    expect($awa->hasRole('super_admin'))->toBeTrue()->and($awa->doit_changer_mot_de_passe)->toBeTrue()
        ->and(strlen(session('identifiants')['mot_de_passe']))->toBe(12);

    // On ne désactive pas son propre compte ; un autre administrateur actif le peut
    $this->post(route('plateforme.comptes.basculer', $this->superAdmin))->assertSessionHas('error');
    $awa->forceFill(['doit_changer_mot_de_passe' => false])->save();
    $this->actingAs($awa)->post(route('plateforme.comptes.basculer', $this->superAdmin))->assertSessionHas('success');
    expect($this->superAdmin->fresh()->statut)->toBe('inactif');
});

it('ne montre au propriétaire que les paramètres de son établissement, sans les entrées de la plateforme', function () {
    [$espace, $proprietaire] = creerEspace($this);
    $proprietaire->forceFill(['doit_changer_mot_de_passe' => false])->save();
    $this->actingAs($proprietaire);

    $page = $this->get(route('etablissement.edit'))->assertOk();
    $page->assertSee('Clinique Espoir')->assertSee(route('users.index'))
        ->assertDontSee(route('plateforme.index'))->assertDontSee(route('admin.roles.index'))->assertDontSee('Rapport Migrations');
    $this->get(route('admin.roles.index'))->assertForbidden();
    $this->get(route('admin.permissions.index'))->assertForbidden();
    $this->get(route('admin.migrations.report-pdf'))->assertForbidden();
});

it('règle type, modules, règles de fonctionnement et serveur en ligne depuis l\'Administration seulement', function () {
    [$espace, $proprietaire] = creerEspace($this);
    $this->actingAs($this->superAdmin);

    $this->get(route('plateforme.configuration', $espace))->assertOk()
        ->assertSee('Type d\'établissement', false)->assertSee('Modules activés')->assertSee('Règles de fonctionnement')->assertSee('Serveur en ligne');
    $this->put(route('plateforme.configuration.update', $espace), [
        'type' => 'clinique', 'modules' => ['ordonnance', 'stock'],
        'parametres' => ['caisse_validation_cloture' => 1, 'en_ligne_delai_annulation_heures' => 48],
    ])->assertRedirect(route('plateforme.configuration', $espace));
    $espace->refresh();
    expect($espace->modules)->toBe(['ordonnance', 'stock'])
        ->and($espace->parametre('caisse.validation_cloture'))->toBeTrue()
        ->and($espace->parametre('en_ligne.delai_annulation_heures'))->toBe(48);

    $this->put(route('plateforme.en_ligne', $espace), ['en_ligne_url' => 'https://en-ligne.exemple.ml', 'en_ligne_cle' => 'GS-ABC', 'en_ligne_secret' => str_repeat('x', 40)])
        ->assertRedirect(route('plateforme.configuration', $espace));
    expect($espace->fresh()->connecteEnLigne())->toBeTrue();

    // Le propriétaire n'y a pas accès
    $proprietaire->forceFill(['doit_changer_mot_de_passe' => false])->save();
    $this->actingAs($proprietaire);
    $this->get(route('plateforme.configuration', $espace))->assertForbidden();
    $this->put(route('plateforme.configuration.update', $espace), ['type' => 'hopital', 'modules' => ['bloc']])->assertForbidden();
    $this->put(route('plateforme.en_ligne', $espace), ['en_ligne_url' => ''])->assertForbidden();
    expect($espace->fresh()->modules)->toBe(['ordonnance', 'stock']);
});

it('réserve le journal d\'audit, qui couvre toute la plateforme, au super-admin', function () {
    [$espace, $proprietaire] = creerEspace($this);
    $proprietaire->forceFill(['doit_changer_mot_de_passe' => false])->save();

    $this->actingAs($proprietaire);
    $this->get(route('audit.index'))->assertForbidden();
    $this->get(route('etablissement.edit'))->assertDontSee(route('audit.index'));

    $this->actingAs($this->superAdmin)->get(route('audit.index'))->assertOk()->assertSee('Journal d\'audit', false);
});

it('rend les pages des paramètres et de l\'administration avec un HTML équilibré (menus latéraux)', function () {
    $equilibre = function (string $html): int {
        $main = substr($html, strpos($html, '<main'), strpos($html, '</main>') - strpos($html, '<main'));
        $main = preg_replace('/<!--.*?-->/s', '', $main);

        return preg_match_all('/<div\b/', $main) - preg_match_all('/<\/div>/', $main);
    };

    $this->actingAs($this->superAdmin);
    foreach (['etablissement.edit', 'services.index', 'prestations.index', 'users.index', 'plateforme.tableau', 'plateforme.index', 'plateforme.formules', 'admin.roles.index', 'admin.permissions.index', 'audit.index', 'familles.index', 'assurances.index'] as $route) {
        $html = $this->get(route($route))->assertOk()->getContent();
        expect($equilibre($html))->toBe(0, "Balises <div> déséquilibrées sur {$route}");
    }
});

it('conduit le super-admin dans l\'Administration à la connexion, et les autres comptes dans leur établissement', function () {
    [$espace, $proprietaire, $provisoire] = creerEspace($this);
    $this->superAdmin->forceFill(['password' => 'Plateforme2026', 'doit_changer_mot_de_passe' => false])->save();

    $this->post('/login', ['email' => 'superadmin@exemple.com', 'password' => 'Plateforme2026'])->assertRedirect(route('plateforme.tableau'));
    $this->get('/login')->assertRedirect(route('plateforme.tableau'));
    $this->post('/logout');

    $proprietaire->forceFill(['password' => 'Proprio2026x', 'doit_changer_mot_de_passe' => false])->save();
    $this->post('/login', ['email' => 'f.sidibe@espoir.ml', 'password' => 'Proprio2026x'])->assertRedirect(route('dashboard'));
});

it('mène le super-admin à l\'Administration même s\'il a d\'abord ouvert l\'adresse du site', function () {
    $this->superAdmin->forceFill(['password' => 'Plateforme2026', 'doit_changer_mot_de_passe' => false])->save();

    $this->get('/')->assertRedirect('/login');
    $this->post('/login', ['email' => 'superadmin@exemple.com', 'password' => 'Plateforme2026'])->assertRedirect(route('plateforme.tableau'));
    $this->get('/')->assertRedirect(route('plateforme.tableau'));
    $this->get(route('dashboard'))->assertOk(); // l'application de l'établissement reste accessible
});
