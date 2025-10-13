<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Un mot de passe provisoire (remis par la plateforme) doit être changé avant tout travail. */
class ExigerChangementMotDePasse
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user?->doit_changer_mot_de_passe && !$request->routeIs('profile.edit', 'password.update', 'logout')) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Changez d\'abord votre mot de passe provisoire.'], 403)
                : redirect()->route('profile.edit')->with('warning', 'Bienvenue ! Pour votre sécurité, choisissez votre propre mot de passe avant de commencer.');
        }

        return $next($request);
    }
}
