<?php

namespace App\Http\Middleware;

use App\Models\Etablissement;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * SEC-02 : déconnexion automatique après une période d'inactivité,
 * réglable par établissement (15 minutes par défaut).
 */
class DeconnexionInactivite
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $minutes = max(1, (int) Etablissement::courant()->parametre('securite.inactivite_minutes'));
        $derniere = $request->session()->get('derniere_activite');

        if ($derniere && now()->timestamp - $derniere > $minutes * 60) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = "Vous avez été déconnecté après {$minutes} minutes d'inactivité.";
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $message], 401);
            }

            return redirect()->route('login')->withErrors(['email' => $message]);
        }

        $request->session()->put('derniere_activite', now()->timestamp);

        return $next($request);
    }
}
