<?php

namespace App\Services\EnLigne;

use App\Models\EnLigne\Compte;
use App\Services\SmsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * Connexion à l'espace patient par code SMS à usage unique (EF-14.02, SEC-11) :
 * code de 6 chiffres valable 10 minutes, 5 essais au plus, 3 demandes de code
 * par numéro et par quart d'heure, 10 par adresse IP et par heure.
 */
class ConnexionPatientService
{
    public const VALIDITE_MINUTES = 10;
    public const ESSAIS_MAX = 5;

    public function __construct(private SmsService $sms) {}

    /** Envoie un code ; renvoie le code en clair pour le mode démonstration uniquement. */
    public function demanderCode(string $telephone, ?string $ip): string
    {
        $telephone = Compte::normaliser($telephone);
        if (strlen($telephone) < 8 || strlen($telephone) > 15) {
            throw ValidationException::withMessages(['telephone' => 'Numéro de téléphone invalide.']);
        }

        foreach (['code-tel:' . $telephone => [3, 900], 'code-ip:' . $ip => [10, 3600]] as $cle => [$max, $duree]) {
            if (RateLimiter::tooManyAttempts($cle, $max)) {
                $minutes = (int) ceil(RateLimiter::availableIn($cle) / 60);
                throw ValidationException::withMessages(['telephone' => "Trop de demandes de code. Réessayez dans {$minutes} minute(s)."]);
            }
            RateLimiter::hit($cle, $duree);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        DB::transaction(function () use ($telephone, $code, $ip) {
            // Un nouveau code annule les précédents
            DB::table('en_ligne_codes')->where('telephone', $telephone)->whereNull('utilise_at')->update(['utilise_at' => now()]);
            DB::table('en_ligne_codes')->insert([
                'telephone' => $telephone, 'empreinte' => Hash::make($code), 'expire_at' => now()->addMinutes(self::VALIDITE_MINUTES),
                'ip' => $ip, 'created_at' => now(),
            ]);
        });

        $this->sms->envoyer($telephone, "G-SANTÉ : votre code de connexion est {$code}. Il expire dans " . self::VALIDITE_MINUTES . ' minutes. Ne le communiquez à personne.');

        return $code;
    }

    public function verifier(string $telephone, string $code): Compte
    {
        $telephone = Compte::normaliser($telephone);
        $refus = ValidationException::withMessages(['code' => 'Code incorrect ou expiré. Demandez un nouveau code.']);

        // Le résultat est décidé dans la transaction, mais l'erreur n'est levée qu'après :
        // sinon l'annulation de la transaction effacerait le décompte des essais
        $compte = DB::transaction(function () use ($telephone, $code) {
            $ligne = DB::table('en_ligne_codes')->where('telephone', $telephone)->whereNull('utilise_at')
                ->where('expire_at', '>', now())->orderByDesc('id')->lockForUpdate()->first();
            if (!$ligne || $ligne->tentatives >= self::ESSAIS_MAX) {
                return null;
            }
            if (!Hash::check(trim($code), $ligne->empreinte)) {
                DB::table('en_ligne_codes')->where('id', $ligne->id)->increment('tentatives');

                return null;
            }
            DB::table('en_ligne_codes')->where('id', $ligne->id)->update(['utilise_at' => now()]);

            $compte = Compte::firstOrCreate(['telephone' => $telephone]);
            $compte->update(['verifie_at' => $compte->verifie_at ?? now(), 'derniere_connexion_at' => now()]);

            return $compte;
        });

        return $compte ?? throw $refus;
    }
}
