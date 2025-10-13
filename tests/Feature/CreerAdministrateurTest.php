<?php

use App\Models\Etablissement;
use App\Models\User;
use Tests\Support\Fabrique;

it('crée un super-administrateur rattaché aux établissements, qui peut se connecter', function () {
    \App\Models\Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    Fabrique::autreEtablissement();

    $this->artisan('gsante:admin', ['email' => 'Responsable@Exemple.ml', '--nom' => 'Traoré', '--prenom' => 'Siaka'])
        ->expectsQuestion('Mot de passe (10 caractères au moins, lettres et chiffres)', 'Solide2026x')
        ->expectsQuestion('Confirmez le mot de passe', 'Solide2026x')
        ->assertSuccessful();

    $user = User::where('email', 'responsable@exemple.ml')->sole();
    expect($user->hasRole('super_admin'))->toBeTrue()
        ->and($user->etablissements()->count())->toBe(Etablissement::count());

    $this->post('/login', ['email' => 'responsable@exemple.ml', 'password' => 'Solide2026x'])->assertRedirect();
    $this->assertAuthenticatedAs($user);
});

it('refuse un mot de passe trop faible ou mal confirmé', function () {
    \App\Models\Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    $this->artisan('gsante:admin', ['email' => 'a@b.ml', '--nom' => 'A', '--prenom' => 'B'])
        ->expectsQuestion('Mot de passe (10 caractères au moins, lettres et chiffres)', 'password')
        ->expectsQuestion('Confirmez le mot de passe', 'password')
        ->assertFailed();
    $this->artisan('gsante:admin', ['email' => 'a@b.ml', '--nom' => 'A', '--prenom' => 'B'])
        ->expectsQuestion('Mot de passe (10 caractères au moins, lettres et chiffres)', 'Solide2026x')
        ->expectsQuestion('Confirmez le mot de passe', 'Autre2026xx')
        ->assertFailed();
    expect(User::where('email', 'a@b.ml')->exists())->toBeFalse();
});

it('affiche un message de connexion en français, pas une clé de traduction', function () {
    $this->post('/login', ['email' => 'inconnu@exemple.ml', 'password' => 'Faux2026xx'])
        ->assertSessionHasErrors(['email' => 'Ces identifiants ne correspondent à aucun compte actif.']);
});
