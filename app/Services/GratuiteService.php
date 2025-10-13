<?php

namespace App\Services;

use App\Models\Grossesse;
use App\Models\Patient;
use App\Models\RegleGratuite;
use Illuminate\Support\Carbon;

/**
 * EF-05.03 : applique automatiquement une gratuité quand le patient et l'acte
 * (ou le médicament) remplissent les conditions d'une règle active.
 */
class GratuiteService
{
    public function pourPrestation(?Patient $patient, int $prestationId, $date = null): ?RegleGratuite
    {
        return $this->regles($patient, $date)->first(fn($r) => $r->portee_actes === 'tous'
            || ($r->portee_actes === 'liste' && $r->prestations->contains('id', $prestationId)));
    }

    public function pourMedicament(?Patient $patient, int $medicamentId, $date = null): ?RegleGratuite
    {
        return $this->regles($patient, $date)->first(fn($r) => $r->couvre_medicaments
            && ($r->medicaments->isEmpty() || $r->medicaments->contains('id', $medicamentId)));
    }

    private function regles(?Patient $patient, $date)
    {
        if (!$patient) {
            return collect();
        }
        $date = $date ? Carbon::parse($date) : now();
        $ageMois = $patient->date_naissance ? (int) Carbon::parse($patient->date_naissance)->diffInMonths($date) : ((int) $patient->age) * 12;
        $enceinte = Grossesse::where('patient_id', $patient->id)->where('statut', 'En cours')->exists();

        return RegleGratuite::with(['prestations', 'medicaments'])->where('actif', true)
            ->whereDate('date_effet', '<=', $date)
            ->where(fn($q) => $q->whereNull('date_fin')->orWhereDate('date_fin', '>=', $date))
            ->orderBy('id')->get()
            ->filter(fn($r) => ($r->age_max_mois === null || $ageMois <= $r->age_max_mois)
                && ($r->genre === null || $r->genre === $patient->genre)
                && (!$r->grossesse_en_cours || $enceinte));
    }
}
