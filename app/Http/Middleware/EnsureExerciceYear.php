<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

class EnsureExerciceYear
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Initialiser l'année de l'exercice si elle n'existe pas en session
        if (!Session::has('exercice_year')) {
            Session::put('exercice_year', date('Y'));
        }

        // Partager l'année active avec toutes les vues
        View::share('exercice_year', Session::get('exercice_year'));

        return $next($request);
    }
}
