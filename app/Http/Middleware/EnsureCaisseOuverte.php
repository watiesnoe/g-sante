<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\CaisseSession;

class EnsureCaisseOuverte
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // On vérifie si l'utilisateur a une caisse ouverte
        $hasCaisse = CaisseSession::where('user_id', auth()->id())
            ->where('statut', 'ouverte')
            ->exists();

        if (!$hasCaisse) {
            // Si c'est une requête AJAX, on renvoie une erreur JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Action refusée : Vous devez ouvrir votre caisse avant de pouvoir effectuer cette opération.',
                    'redirect' => route('caisse.open')
                ], 403);
            }

            return redirect()->route('caisse.open')
                ->with('error', 'Vous devez ouvrir votre caisse avant de pouvoir effectuer des encaissements ou décaissements.');
        }

        return $next($request);
    }
}
