<?php

namespace App\Services;

use App\Models\Hospitalisation;
use App\Models\Lit;
use App\Models\OccupationLit;
use Illuminate\Support\Carbon;

/**
 * EF-13.05 : indicateurs hospitaliers sur une période.
 * - taux d'occupation = journées-lit occupées ÷ (lits × jours de la période) ;
 * - durée moyenne de séjour = journées des séjours sortis ÷ nombre de sorties ;
 * - décès et taux de décès parmi les sorties.
 * Une journée d'hospitalisation est comptée par nuit (au moins une par séjour).
 */
class IndicateursHospitaliersService
{
    public function calculer(Carbon $du, Carbon $au): array
    {
        $debut = $du->copy()->startOfDay();
        $fin = $au->copy()->endOfDay();
        $jours = (int) $debut->diffInDays($fin->copy()->addSecond());
        $lits = Lit::where('statut', '!=', 'Maintenance')->count();

        $journeesOccupees = OccupationLit::whereHas('hospitalisation')
            ->where('debut', '<=', $fin)->where(fn($q) => $q->whereNull('fin')->orWhere('fin', '>=', $debut))
            ->get()
            ->sum(function ($o) use ($debut, $fin) {
                $a = $o->debut->max($debut);
                $b = ($o->fin ?? now())->min($fin);

                return max(0, $a->copy()->startOfDay()->diffInDays($b->copy()->startOfDay()));
            });

        $sorties = Hospitalisation::where('etat', 'terminé')->whereBetween('date_sortie', [$du->toDateString(), $au->toDateString()])->get();
        $dureeSejours = $sorties->sum(fn($h) => max(1, (int) Carbon::parse($h->date_entree)->startOfDay()->diffInDays(Carbon::parse($h->date_sortie)->startOfDay())));
        $deces = $sorties->where('statut_sortie', 'Décès')->count();

        return [
            'lits'                 => $lits,
            'jours'                => $jours,
            'journees_occupees'    => (int) $journeesOccupees,
            'taux_occupation'      => $lits && $jours ? round($journeesOccupees / ($lits * $jours) * 100, 1) : 0.0,
            'admissions'           => Hospitalisation::whereBetween('date_entree', [$du->toDateString(), $au->toDateString()])->count(),
            'sorties'              => $sorties->count(),
            'duree_moyenne_sejour' => $sorties->count() ? round($dureeSejours / $sorties->count(), 1) : 0.0,
            'deces'                => $deces,
            'taux_deces'           => $sorties->count() ? round($deces / $sorties->count() * 100, 1) : 0.0,
        ];
    }
}
