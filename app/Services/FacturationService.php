<?php

namespace App\Services;

use App\Models\Prestation;
use Illuminate\Support\Carbon;

/**
 * Calcul unique des montants facturés (QUA-01, EF-05.02) : prix au tarif en
 * vigueur à la date de l'acte, remise, part de l'organisme et part du patient.
 * Les prix calculés sont recopiés sur les lignes, donc figés (RG-07).
 */
class FacturationService
{
    /**
     * @param  array  $items  [['prestation_id' => .., 'quantite' => .., 'remise' => %, 'service' => ..], ...]
     * @return array{lignes: array, total: float, taux_couverture: float, part_assurance: float, part_patient: float}
     */
    public function calculer(array $items, ?int $assuranceId = null, $date = null, ?\App\Models\Patient $patient = null): array
    {
        $gratuites = app(GratuiteService::class);
        $totalGratuit = 0.0;
        $date = $date ? Carbon::parse($date) : now();
        // EF-07.01 : convention de l'organisme en vigueur à la date de l'acte
        $convention = \App\Models\Convention::applicable($assuranceId, $date);
        $lignes = [];
        $total = 0.0;
        $totalAssurance = 0.0;

        foreach ($items as $item) {
            $prestation = Prestation::findOrFail($item['prestation_id']);
            $quantite = max(1, (int) ($item['quantite'] ?? 1));
            $remise = min(100, max(0, (float) ($item['remise'] ?? 0)));
            $prixUnitaire = $prestation->prixAu($date);
            $sousTotal = round($prixUnitaire * $quantite * (1 - $remise / 100), 2);
            // EF-05.03 : gratuité automatique si le patient et l'acte remplissent une règle
            $regle = $gratuites->pourPrestation($patient, $prestation->id, $date);
            $partGratuite = $regle ? $sousTotal : 0.0;
            // EF-07.03 : part de l'organisme calculée ligne par ligne, selon les actes couverts
            $partAssurance = !$regle && $convention && $convention->couvre($prestation->id) ? round($sousTotal * $convention->taux / 100, 2) : 0.0;

            $lignes[] = [
                'prestation_id' => $prestation->id,
                'service'       => $item['service'] ?? ($prestation->serviceMedical->nom ?? 'N/A'),
                'prix_unitaire' => $prixUnitaire,
                'quantite'      => $quantite,
                'remise'        => $remise,
                'sous_total'    => $sousTotal,
                'part_assurance' => $partAssurance,
                'part_gratuite' => $partGratuite,
                'regle_gratuite_id' => $regle?->id,
                'part_patient'  => round($sousTotal - $partAssurance - $partGratuite, 2),
            ];
            $total += $sousTotal;
            $totalAssurance += $partAssurance;
            $totalGratuit += $partGratuite;
        }

        // Plafond de prise en charge par facture : le dépassement revient au patient
        if ($convention && $convention->plafond_par_facture !== null && $totalAssurance > $convention->plafond_par_facture) {
            $ratio = $convention->plafond_par_facture / $totalAssurance;
            foreach ($lignes as &$ligne) {
                $ligne['part_assurance'] = round($ligne['part_assurance'] * $ratio, 2);
                $ligne['part_patient'] = round($ligne['sous_total'] - $ligne['part_assurance'] - $ligne['part_gratuite'], 2);
            }
            unset($ligne);
            $totalAssurance = (float) $convention->plafond_par_facture;
        }

        return [
            'lignes'          => $lignes,
            'total'           => round($total, 2),
            'convention_id'   => $convention?->id,
            'taux_couverture' => $convention?->taux ?? 0.0,
            'part_assurance'  => round($totalAssurance, 2),
            'part_gratuite'   => round($totalGratuit, 2),
            'part_patient'    => round($total - $totalAssurance - $totalGratuit, 2),
        ];
    }
}
