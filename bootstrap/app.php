<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Serveur en ligne : échanges avec les serveurs locaux (lot 7)
            if (config('gsante.mode') === 'en_ligne' || \App\Models\Etablissement::espacePatientDisponible() || app()->runningUnitTests()) {
                \Illuminate\Support\Facades\Route::group([], base_path('routes/en_ligne.php'));
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'module_access' => \App\Http\Middleware\EnsureModuleAccess::class,
            'caisse.ouverte' => \App\Http\Middleware\EnsureCaisseOuverte::class,
            'exercice.year' => \App\Http\Middleware\EnsureExerciceYear::class,
        ]);

        // Compte déjà connecté qui ouvre la page de connexion : même page d'arrivée qu'après connexion
        $middleware->redirectUsersTo(fn() => \App\Http\Controllers\Auth\AuthenticatedSessionController::accueil(auth()->user()));

        // Requêtes signées des serveurs locaux : pas de session, donc pas de jeton CSRF
        $middleware->validateCsrfTokens(except: ['api/synchro/*']);

        $middleware->web(append: [
            \App\Http\Middleware\EnsureEtablissement::class,
            \App\Http\Middleware\DeconnexionInactivite::class,
            \App\Http\Middleware\EnsureExerciceYear::class,
            \App\Http\Middleware\EnsureUserIsActive::class,
            \App\Http\Middleware\ExigerChangementMotDePasse::class,
        ]);

        // Désactiver la vérification CSRF en environnement de test
        if (env('APP_ENV') === 'testing') {
            $middleware->web(remove: [
                \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            ]);
        }
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Espace patient : session expirée (jeton du formulaire périmé) → reprise guidée, pas de page d'erreur
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, \Illuminate\Http\Request $request) {
            if ($e->getStatusCode() === 419 && $request->is('patient', 'patient/*')) {
                return redirect()->route($request->session()->has(\App\Http\Middleware\PatientConnecte::SESSION) ? 'patient.accueil' : 'patient.connexion')
                    ->with('erreur', 'La page avait expiré : recommencez votre dernière action.');
            }
        });
    })->create();
