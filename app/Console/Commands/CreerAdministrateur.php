<?php

namespace App\Console\Commands;

use App\Models\Etablissement;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

/**
 * Crée (ou réactive) le compte super-administrateur d'une installation, avec un
 * mot de passe saisi de façon masquée : à utiliser en production à la place des
 * comptes de démonstration.
 */
class CreerAdministrateur extends Command
{
    protected $signature = 'gsante:admin {email} {--nom=} {--prenom=}';

    protected $description = 'Créer ou réinitialiser un compte super-administrateur rattaché à tous les établissements';

    public function handle(): int
    {
        $email = mb_strtolower(trim($this->argument('email')));
        if (Validator::make(['email' => $email], ['email' => 'required|email'])->fails()) {
            $this->error('Adresse e-mail invalide.');

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();
        $this->info($user ? "Le compte {$email} existe : son mot de passe sera remplacé." : "Création du compte {$email}.");

        $nom = $this->option('nom') ?: ($user->nom ?? $this->ask('Nom'));
        $prenom = $this->option('prenom') ?: ($user->prenom ?? $this->ask('Prénom'));
        $motDePasse = $this->secret('Mot de passe (10 caractères au moins, lettres et chiffres)');
        if ($motDePasse !== $this->secret('Confirmez le mot de passe')) {
            $this->error('Les deux saisies ne correspondent pas.');

            return self::FAILURE;
        }
        $validation = Validator::make(['password' => $motDePasse], ['password' => ['required', Password::min(10)->letters()->numbers()]]);
        if ($validation->fails()) {
            $this->error($validation->errors()->first());

            return self::FAILURE;
        }

        $role = Role::where('name', 'super_admin')->where('guard_name', 'web')->first();
        if (!$role) {
            $this->error('Rôle super_admin absent : lancez d\'abord « artisan db:seed --class=PermissionRoleSeeder --force ».');

            return self::FAILURE;
        }

        $user ??= new User(['email' => $email]);
        $user->forceFill([
            'uuid'     => $user->uuid ?? (string) Str::uuid(),
            'name'     => $user->name ?? Str::slug($prenom . ' ' . $nom, '_'),
            'nom'      => $nom,
            'prenom'   => $prenom,
            'password' => Hash::make($motDePasse),
            'statut'   => 'actif',
            // Un compte verrouillé après trop d'échecs est débloqué
            'echecs_connexion'  => 0,
            'verrouille_jusqua' => null,
        ])->save();
        $user->syncRoles([$role]);
        // Sans établissement affecté, la connexion est refusée
        $user->etablissements()->syncWithoutDetaching(Etablissement::pluck('id'));

        $this->info("Compte prêt : {$email} (super-administrateur, " . $user->etablissements()->count() . ' établissement(s)).');

        return self::SUCCESS;
    }
}
