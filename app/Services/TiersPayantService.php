<?php

namespace App\Services;

use App\Models\Assurance;
use App\Models\Bordereau;
use App\Models\Convention;
use App\Models\OrdonnancePaiement;
use App\Models\Recouvrement;
use App\Models\Ticket;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Tiers payants (EF-07.03, EF-07.04) : part des médicaments prise en charge,
 * bordereaux par organisme et période, recouvrements et créances.
 */
class TiersPayantService
{
    public function __construct(private NumerotationService $numerotation) {}

    /**
     * Parts d'une vente de médicament pour un patient assuré.
     *
     * @return array{assurance_id: ?int, convention_id: ?int, part_assurance: float, part_patient: float}
     */
    public function partsMedicament(?\App\Models\Patient $patient, float $montant): array
    {
        $assuranceValide = $patient && $patient->assurance_id
            && (!$patient->fin_validite_assurance || Carbon::parse($patient->fin_validite_assurance)->gte(today()));
        $convention = $assuranceValide ? Convention::applicable($patient->assurance_id) : null;

        if (!$convention || !$convention->couvre_medicaments) {
            return ['assurance_id' => null, 'convention_id' => null, 'part_assurance' => 0.0, 'part_patient' => $montant];
        }

        $partAssurance = round($montant * $convention->taux / 100, 2);

        return ['assurance_id' => $patient->assurance_id, 'convention_id' => $convention->id, 'part_assurance' => $partAssurance, 'part_patient' => round($montant - $partAssurance, 2)];
    }

    /** Regroupe les parts organisme non encore facturées de la période dans un bordereau. */
    public function genererBordereau(Assurance $assurance, Carbon $du, Carbon $au): Bordereau
    {
        return DB::transaction(function () use ($assurance, $du, $au) {
            $periode = [$du->copy()->startOfDay(), $au->copy()->endOfDay()];
            $dejaFactures = fn($q, $type) => $q->whereNotExists(fn($s) => $s->from('bordereau_lignes')
                ->whereColumn('bordereau_lignes.source_id', $q->getModel()->getTable() . '.id')
                ->where('bordereau_lignes.source_type', $type));

            $tickets = Ticket::with('patient')->where('assurance_id', $assurance->id)->where('part_assurance', '>', 0)
                ->where('statut', '!=', 'annule')->whereBetween('created_at', $periode)
                ->where(fn($q) => $dejaFactures($q, Ticket::class))->lockForUpdate()->get();

            $ventes = OrdonnancePaiement::with(['ordonnance.consultation.patient', 'medicament'])->where('assurance_id', $assurance->id)
                ->where('part_assurance', '>', 0)->where('statut', 'payé')->whereBetween('created_at', $periode)
                ->where(fn($q) => $dejaFactures($q, OrdonnancePaiement::class))->lockForUpdate()->get();

            if ($tickets->isEmpty() && $ventes->isEmpty()) {
                throw ValidationException::withMessages(['periode' => 'Aucune prise en charge à facturer pour cet organisme sur la période.']);
            }

            $bordereau = Bordereau::create([
                'assurance_id' => $assurance->id,
                'numero'       => $this->numerotation->suivant('bordereau'),
                'periode_du'   => $du, 'periode_au' => $au,
                'montant'      => round($tickets->sum('part_assurance') + $ventes->sum('part_assurance'), 2),
                'statut'       => 'emis',
                'user_id'      => Auth::id(),
            ]);

            foreach ($tickets as $t) {
                $bordereau->lignes()->create([
                    'source_type' => Ticket::class, 'source_id' => $t->id, 'patient_id' => $t->patient_id,
                    'date_acte' => $t->created_at, 'libelle' => 'Ticket ' . ($t->numero ?? $t->id) . ($t->numero_assure ? ' — assuré ' . $t->numero_assure : ''),
                    'montant' => $t->part_assurance,
                ]);
            }
            foreach ($ventes as $v) {
                $bordereau->lignes()->create([
                    'source_type' => OrdonnancePaiement::class, 'source_id' => $v->id, 'patient_id' => $v->ordonnance?->consultation?->patient_id,
                    'date_acte' => $v->created_at, 'libelle' => 'Médicament : ' . ($v->medicament->nom ?? '') . ' x' . $v->quantite,
                    'montant' => $v->part_assurance,
                ]);
            }

            return $bordereau;
        });
    }

    public function recouvrer(Bordereau $bordereau, float $montant, $date, string $mode, ?string $reference): Recouvrement
    {
        if ($bordereau->statut === 'annule') {
            throw ValidationException::withMessages(['montant' => 'Ce bordereau est annulé.']);
        }
        if ($montant <= 0 || $montant > $bordereau->resteDu() + 0.001) {
            throw ValidationException::withMessages(['montant' => 'Le montant doit être compris entre 1 et le reste dû (' . number_format($bordereau->resteDu(), 0, ',', ' ') . ' F).']);
        }

        return DB::transaction(function () use ($bordereau, $montant, $date, $mode, $reference) {
            $recouvrement = $bordereau->recouvrements()->create([
                'montant' => $montant, 'date_recouvrement' => $date, 'mode' => $mode, 'reference' => $reference, 'user_id' => Auth::id(),
            ]);
            $recouvre = round($bordereau->montant_recouvre + $montant, 2);
            $bordereau->update(['montant_recouvre' => $recouvre, 'statut' => $recouvre >= $bordereau->montant ? 'regle' : 'partiel']);

            return $recouvrement;
        });
    }

    /** Créances par organisme : facturé non réglé, et parts pas encore mises en bordereau. */
    public function creances(): array
    {
        $nonFacture = fn($builder, $type) => (clone $builder)->whereNotExists(fn($s) => $s->from('bordereau_lignes')
            ->whereColumn('bordereau_lignes.source_id', $builder->getModel()->getTable() . '.id')->where('bordereau_lignes.source_type', $type));

        return Assurance::orderBy('nom')->get()->map(function ($assurance) use ($nonFacture) {
            $tickets = Ticket::where('assurance_id', $assurance->id)->where('statut', '!=', 'annule')->where('part_assurance', '>', 0);
            $ventes = OrdonnancePaiement::where('assurance_id', $assurance->id)->where('statut', 'payé')->where('part_assurance', '>', 0);
            $bordereaux = Bordereau::where('assurance_id', $assurance->id)->where('statut', '!=', 'annule');

            return [
                'assurance'    => $assurance,
                'non_facture'  => (float) $nonFacture($tickets, Ticket::class)->sum('part_assurance') + (float) $nonFacture($ventes, OrdonnancePaiement::class)->sum('part_assurance'),
                'facture'      => (float) (clone $bordereaux)->sum('montant'),
                'recouvre'     => (float) (clone $bordereaux)->sum('montant_recouvre'),
            ];
        })->filter(fn($c) => $c['non_facture'] + $c['facture'] > 0)->values()->all();
    }
}
