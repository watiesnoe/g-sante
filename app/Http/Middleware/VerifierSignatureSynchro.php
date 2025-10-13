<?php

namespace App\Http\Middleware;

use App\Models\EnLigne\Site;
use App\Support\SignatureSynchro;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/** Serveur en ligne : n'accepte que les requêtes signées par un établissement actif (SEC-14). */
class VerifierSignatureSynchro
{
    public function handle(Request $request, Closure $next): Response
    {
        $cle = (string) $request->header(SignatureSynchro::ENTETE_CLE);
        $horodatage = (int) $request->header(SignatureSynchro::ENTETE_HORODATAGE);
        $signature = (string) $request->header(SignatureSynchro::ENTETE_SIGNATURE);
        $nonce = (string) $request->header(SignatureSynchro::ENTETE_NONCE);
        $tolerance = config('gsante.synchro.tolerance_horodatage');

        $site = $cle !== '' ? Site::where('cle', $cle)->where('actif', true)->first() : null;
        $refus = match (true) {
            !$site                                        => 'établissement inconnu ou désactivé',
            abs(now()->getTimestamp() - $horodatage) > $tolerance => 'horodatage hors délai',
            strlen($nonce) < 16                           => 'nonce absent',
            !hash_equals(SignatureSynchro::calculer($site->secret, $request->method(), $request->getRequestUri(), $horodatage, $nonce, $request->getContent()), $signature)
                                                          => 'signature invalide',
            // Une signature ne sert qu'une fois : un message intercepté ne peut pas être rejoué
            !Cache::add('synchro-signature:' . $signature, true, $tolerance * 2) => 'requête déjà reçue',
            default                                       => null,
        };

        if ($refus) {
            Log::warning('Synchronisation refusée : ' . $refus, ['cle' => $cle, 'ip' => $request->ip()]);

            return response()->json(['erreur' => 'Accès refusé'], 401);
        }

        $request->attributes->set('site', $site);

        return $next($request);
    }
}
