<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'module_access' => \App\Http\Middleware\EnsureModuleAccess::class,
            'caisse.ouverte' => \App\Http\Middleware\EnsureCaisseOuverte::class,
            'exercice.year' => \App\Http\Middleware\EnsureExerciceYear::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\EnsureExerciceYear::class,
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        // Désactiver la vérification CSRF en environnement de test
        if (env('APP_ENV') === 'testing') {
            $middleware->web(remove: [
                \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            ]);
        }
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
