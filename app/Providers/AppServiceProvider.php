<?php

namespace App\Providers;

use App\Models\Commande;
use App\Observers\CommandeObserver;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        Commande::observe(CommandeObserver::class);

        // SEC-02 : mots de passe d'au moins 10 caractères, avec lettres et chiffres
        \Illuminate\Validation\Rules\Password::defaults(fn() => \Illuminate\Validation\Rules\Password::min(10)->letters()->numbers());

        // Serveur en ligne : cookie de session distinct de celui de l'application interne.
        // Sur une même machine (127.0.0.1:8000 et :8001), le navigateur partage les cookies
        // entre les ports : un nom commun ferait perdre sa session à chaque serveur.
        if (config('gsante.mode') === 'en_ligne' && !env('SESSION_COOKIE')) {
            config(['session.cookie' => 'gsante_espace_patient']);
        }
    }
}
