<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        if (!$request->user() || !$request->user()->hasModuleAccess($module)) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Accès refusé pour ce module.'], 403);
            }
            return redirect()->route('dashboard')->with('error', 'Vous n\'avez pas la permission d\'accéder à ce module.');
        }

        return $next($request);
    }
}
