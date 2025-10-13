<?php

namespace App\Services;

use App\Models\Medicament;
use App\Models\Patient;

/**
 * EF-04.04 : rapproche les médicaments prescrits des allergies notées au dossier
 * (nom du produit ou de sa famille). Les allergies sont saisies en texte libre,
 * séparées par des virgules, points-virgules ou retours à la ligne.
 */
class AllergieService
{
    /** @return string[] Une ligne par médicament concerné */
    public static function alertes(?Patient $patient, array $medicamentIds): array
    {
        $allergies = collect(preg_split('/[,;\n]+/', mb_strtolower((string) $patient?->allergies)))
            ->map(fn($a) => trim($a))->filter(fn($a) => mb_strlen($a) >= 4)->values();

        if ($allergies->isEmpty()) {
            return [];
        }

        return Medicament::with('famille')->whereIn('id', array_filter($medicamentIds))->get()
            ->map(function ($m) use ($allergies) {
                $texte = mb_strtolower($m->nom . ' ' . ($m->famille->nom ?? ''));
                $touchee = $allergies->first(fn($a) => str_contains($texte, $a));

                return $touchee ? "{$m->nom} (allergie : {$touchee})" : null;
            })->filter()->values()->all();
    }
}
