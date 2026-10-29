<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Espace patient intégré à l'application interne (même domaine) : cookie de session
 * distinct, pour qu'une connexion patient et une connexion du personnel dans le
 * même navigateur ne partagent jamais la même session. Placé avant StartSession.
 */
class SessionEspacePatient
{
    public const COOKIE = 'gsante_espace_patient';

    public function handle(Request $request, Closure $next): Response
    {
        if (config('gsante.mode') !== 'en_ligne' && !app()->runningUnitTests()) {
            config(['session.cookie' => self::COOKIE]);
        }

        return $next($request);
    }
}
