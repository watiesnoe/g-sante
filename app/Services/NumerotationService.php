<?php

namespace App\Services;

use App\Models\Etablissement;
use Illuminate\Support\Facades\DB;

/**
 * Numéros au format de l'établissement (EF-01.04, EF-02.01, EF-03.01).
 * Le compteur est verrouillé en écriture : deux enregistrements simultanés
 * n'obtiennent jamais le même numéro. Il repart à 1 chaque année.
 */
class NumerotationService
{
    /** Lettre(s) de chaque type de document dans le numéro. */
    public const TYPES = [
        'patient' => 'P',
        'passage' => 'V',
        'ticket'  => 'T',
        'depense' => 'D',
        'bordereau' => 'B',
        'demande_achat' => 'DA',
        'reference' => 'R',
        'requisition' => 'RQ',
    ];

    public function suivant(string $type, ?Etablissement $etablissement = null): string
    {
        $etablissement ??= Etablissement::courant();
        $annee = (int) now()->format('Y');

        $numero = DB::transaction(function () use ($type, $etablissement, $annee) {
            $compteur = DB::table('numerotations')
                ->where(['etablissement_id' => $etablissement->id, 'type' => $type, 'annee' => $annee])
                ->lockForUpdate()->first();

            if (!$compteur) {
                DB::table('numerotations')->insertOrIgnore([
                    'etablissement_id' => $etablissement->id, 'type' => $type, 'annee' => $annee,
                    'dernier' => 0, 'created_at' => now(), 'updated_at' => now(),
                ]);
                $compteur = DB::table('numerotations')
                    ->where(['etablissement_id' => $etablissement->id, 'type' => $type, 'annee' => $annee])
                    ->lockForUpdate()->first();
            }

            DB::table('numerotations')->where('id', $compteur->id)->update(['dernier' => $compteur->dernier + 1, 'updated_at' => now()]);

            return $compteur->dernier + 1;
        });

        return strtr($etablissement->parametre('numerotation.format'), [
            '{CODE}' => $etablissement->code ?? 'ETAB' . $etablissement->id,
            '{TYPE}' => self::TYPES[$type] ?? strtoupper($type),
            '{AAAA}' => $annee,
            '{NUM}'  => str_pad((string) $numero, 6, '0', STR_PAD_LEFT),
        ]);
    }
}
