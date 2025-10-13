<?php

namespace App\Support;

/**
 * Signature des échanges entre serveur local et serveur en ligne (SEC-14).
 *
 * Chaque requête porte l'identifiant de l'établissement, un horodatage, un nonce
 * aléatoire et un HMAC-SHA256 calculé avec le secret de l'établissement sur la
 * méthode, l'adresse (chemin et paramètres), l'horodatage, le nonce et l'empreinte
 * du corps. Le serveur en ligne refuse une signature fausse, trop ancienne ou déjà utilisée.
 */
final class SignatureSynchro
{
    public const ENTETE_CLE = 'X-GSante-Cle';
    public const ENTETE_HORODATAGE = 'X-GSante-Horodatage';
    public const ENTETE_SIGNATURE = 'X-GSante-Signature';
    public const ENTETE_NONCE = 'X-GSante-Nonce';

    public static function calculer(string $secret, string $methode, string $uri, int $horodatage, string $nonce, string $corps): string
    {
        $message = strtoupper($methode) . "\n" . $uri . "\n" . $horodatage . "\n" . $nonce . "\n" . hash('sha256', $corps);

        return hash_hmac('sha256', $message, $secret);
    }

    /** En-têtes à joindre à une requête sortante. */
    public static function entetes(string $cle, string $secret, string $methode, string $url, string $corps): array
    {
        $horodatage = now()->getTimestamp();
        $nonce = bin2hex(random_bytes(16));

        return [
            self::ENTETE_CLE        => $cle,
            self::ENTETE_HORODATAGE => (string) $horodatage,
            self::ENTETE_NONCE      => $nonce,
            self::ENTETE_SIGNATURE  => self::calculer($secret, $methode, self::uri($url), $horodatage, $nonce, $corps),
        ];
    }

    /** Chemin et paramètres d'une adresse, tels que le serveur les reçoit. */
    public static function uri(string $url): string
    {
        $parties = parse_url($url);

        return ($parties['path'] ?? '/') . (isset($parties['query']) ? '?' . $parties['query'] : '');
    }
}
