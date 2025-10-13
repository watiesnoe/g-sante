<?php

namespace App\Http\Middleware;

use App\Models\Etablissement;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fixe l'établissement de travail de l'utilisateur connecté (EF-01.03) :
 * celui choisi en session s'il lui est accessible, sinon le premier qui lui est affecté.
 */
class EnsureEtablissement
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return $next($request);
        }

        $accessibles = $user->etablissementsAccessibles();

        if ($accessibles->isEmpty()) {
            Auth::logout();
            $request->session()->invalidate();

            // Rattaché seulement à des espaces suspendus par la plateforme
            $suspendu = $user->etablissements()->where('actif', false)->first();

            return redirect()->route('login')->withErrors(['email' => $suspendu
                ? 'L\'espace « ' . $suspendu->nom . ' » est suspendu' . ($suspendu->motif_suspension ? ' (' . $suspendu->motif_suspension . ')' : '') . '. Contactez l\'administrateur de la plateforme.'
                : 'Votre compte n\'est affecté à aucun établissement. Contactez l\'administrateur.']);
        }

        $choisi = $request->session()->get(Etablissement::SESSION);
        if (!$accessibles->contains('id', $choisi)) {
            // Par défaut : un établissement auquel le compte est affecté, plutôt que le premier venu
            $parDefaut = $user->etablissements()->where('actif', true)->orderBy('etablissements.id')->value('etablissements.id') ?? $accessibles->first()->id;
            $request->session()->put(Etablissement::SESSION, $parDefaut);
        }

        Etablissement::oublierCourant();
        View::share('etablissementsAccessibles', $accessibles);

        return $next($request);
    }
}
