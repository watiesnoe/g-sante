<?php

namespace App\Http\Middleware;

use App\Models\EnLigne\Compte;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Espace patient : exige un compte connecté et le met à disposition de la requête. */
class PatientConnecte
{
    public const SESSION = 'espace_patient.compte_id';

    public function handle(Request $request, Closure $next): Response
    {
        $compte = ($id = $request->session()->get(self::SESSION)) ? Compte::find($id) : null;
        if (!$compte) {
            return redirect()->route('patient.connexion');
        }
        $request->attributes->set('compte', $compte);

        // Le nom et le prénom sont demandés une fois, avant toute demande
        if (!$compte->profilComplet() && !$request->routeIs('patient.profil*', 'patient.deconnexion')) {
            return redirect()->route('patient.profil');
        }

        return $next($request);
    }
}
