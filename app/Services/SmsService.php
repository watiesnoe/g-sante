<?php

namespace App\Services;

use App\Models\Etablissement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Envoi de SMS par la passerelle configurée (config/sms.php). Chaque envoi est
 * tracé ; un échec n'interrompt jamais l'activité.
 */
class SmsService
{
    /** @param bool $horsQuota messages de la plateforme elle-même (rappels d'abonnement), non décomptés */
    public function envoyer(string $telephone, string $message, ?Model $source = null, bool $horsQuota = false): bool
    {
        $pilote = config('sms.pilote');
        $erreur = null;

        // Quota mensuel de la formule d'abonnement (serveur local seulement)
        if (!$horsQuota && config('gsante.mode') !== 'en_ligne'
            && ($quota = app(AbonnementService::class)->depassement(Etablissement::courant(), 'sms'))) {
            DB::table('sms_envois')->insert([
                'etablissement_id' => Etablissement::courantId(), 'telephone' => $telephone, 'message' => $message, 'pilote' => $pilote,
                'statut' => 'echec', 'erreur' => $quota,
                'source_type' => $source?->getMorphClass(), 'source_id' => $source?->getKey(), 'created_at' => now(), 'updated_at' => now(),
            ]);

            return false;
        }

        try {
            if ($pilote === 'http') {
                $reponse = Http::timeout(10)->withToken((string) config('sms.http.jeton'))->post((string) config('sms.http.url'), [
                    'to' => $telephone, 'from' => config('sms.http.expediteur'), 'message' => $message,
                ]);
                if (!$reponse->successful()) {
                    $erreur = 'HTTP ' . $reponse->status();
                }
            } else {
                Log::info("[SMS] {$telephone} : {$message}");
            }
        } catch (\Throwable $e) {
            $erreur = mb_substr($e->getMessage(), 0, 250);
        }

        DB::table('sms_envois')->insert([
            // Serveur en ligne : pas d'établissement courant (codes de connexion des patients)
            'etablissement_id' => config('gsante.mode') === 'en_ligne' ? null : Etablissement::courantId(),
            'telephone' => $telephone, 'message' => $message, 'pilote' => $pilote,
            'statut' => $erreur ? 'echec' : 'envoye', 'erreur' => $erreur,
            'source_type' => $source?->getMorphClass(), 'source_id' => $source?->getKey(),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return $erreur === null;
    }
}
