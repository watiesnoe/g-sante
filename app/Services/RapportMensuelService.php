<?php

namespace App\Services;

use App\Models\Accouchement;
use App\Models\Consultation;
use App\Models\ConsultationPrenatale;
use App\Models\Hospitalisation;
use App\Models\NouveauNe;
use App\Models\Passage;
use App\Models\Reference;
use App\Models\TicketItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * EF-13.03 : indicateurs du rapport mensuel d'activité, calculés uniquement à
 * partir de données structurées (RG-14). Chaque événement est compté une fois
 * dans sa rubrique (REC-06). La liste définitive des indicateurs et leurs codes
 * nationaux restent à valider avec le district (section 18).
 */
class RapportMensuelService
{
    /** Libellé de chaque indicateur, dans l'ordre du rapport. */
    public const INDICATEURS = [
        'consultations.total'          => 'Consultations — total',
        'consultations.sexe_f'         => 'Consultations — femmes',
        'consultations.sexe_m'         => 'Consultations — hommes',
        'consultations.age_0_11m'      => 'Consultations — moins de 1 an',
        'consultations.age_1_4'        => 'Consultations — 1 à 4 ans',
        'consultations.age_5_14'       => 'Consultations — 5 à 14 ans',
        'consultations.age_15_49'      => 'Consultations — 15 à 49 ans',
        'consultations.age_50_plus'    => 'Consultations — 50 ans et plus',
        'cpn.total'                    => 'Consultations prénatales — total',
        'cpn.cpn1'                     => 'CPN 1',
        'cpn.cpn4_plus'                => 'CPN 4 et plus',
        'accouchements.total'          => 'Accouchements — total',
        'accouchements.cesarienne'     => 'Accouchements par césarienne',
        'naissances.vivantes'          => 'Naissances vivantes',
        'naissances.mort_nes'          => 'Mort-nés',
        'naissances.faible_poids'      => 'Nouveau-nés de moins de 2 500 g',
        'deces.maternels'              => 'Décès maternels',
        'urgences.passages'            => 'Passages aux urgences',
        'hospitalisations.admissions'  => 'Admissions en hospitalisation',
        'hospitalisations.sorties'     => 'Sorties d\'hospitalisation',
        'hospitalisations.deces'       => 'Décès en hospitalisation',
        'hospitalisations.journees'    => 'Journées d\'hospitalisation',
        'references.recues'            => 'Références reçues',
        'references.emises'            => 'Références émises',
        'references.evacuations'       => 'Évacuations sanitaires émises',
        'references.contre_references' => 'Contre-références envoyées',
        'gratuites.montant'            => 'Montant des actes gratuits (FCFA)',
    ];

    public function calculer(Carbon $du, Carbon $au): array
    {
        $periode = [$du->copy()->startOfDay(), $au->copy()->endOfDay()];

        $consultations = Consultation::join('patients', 'patients.id', '=', 'consultations.patient_id')
            ->whereBetween('consultations.date_consultation', $periode);
        // Âge au jour de la consultation : date de naissance si connue, sinon âge déclaré
        $age = "COALESCE(TIMESTAMPDIFF(MONTH, patients.date_naissance, consultations.date_consultation), patients.age * 12)";
        $parAge = fn(string $condition) => (clone $consultations)->whereRaw("{$age} {$condition}")->count();

        $cpn = ConsultationPrenatale::whereBetween('date_cpn', [$du->toDateString(), $au->toDateString()]);
        $accouchements = Accouchement::whereBetween('date_heure', $periode);
        $naissances = NouveauNe::whereHas('accouchement', fn($q) => $q->whereBetween('date_heure', $periode));
        $sorties = Hospitalisation::where('etat', 'terminé')->whereBetween('date_sortie', [$du->toDateString(), $au->toDateString()]);
        $references = Reference::whereBetween('date_reference', $periode)->where('statut', '!=', 'annulee');

        $valeurs = [
            'consultations.total'          => (clone $consultations)->count(),
            'consultations.sexe_f'         => (clone $consultations)->where('patients.genre', 'F')->count(),
            'consultations.sexe_m'         => (clone $consultations)->where('patients.genre', 'M')->count(),
            'consultations.age_0_11m'      => $parAge('< 12'),
            'consultations.age_1_4'        => $parAge('BETWEEN 12 AND 59'),
            'consultations.age_5_14'       => $parAge('BETWEEN 60 AND 179'),
            'consultations.age_15_49'      => $parAge('BETWEEN 180 AND 599'),
            'consultations.age_50_plus'    => $parAge('>= 600'),
            'cpn.total'                    => (clone $cpn)->count(),
            'cpn.cpn1'                     => (clone $cpn)->where('numero_cpn', 1)->count(),
            'cpn.cpn4_plus'                => (clone $cpn)->where('numero_cpn', '>=', 4)->count(),
            'accouchements.total'          => (clone $accouchements)->count(),
            'accouchements.cesarienne'     => (clone $accouchements)->where('mode', 'cesarienne')->count(),
            'naissances.vivantes'          => (clone $naissances)->where('etat', 'vivant')->count(),
            'naissances.mort_nes'          => (clone $naissances)->whereIn('etat', ['mort_ne_frais', 'mort_ne_macere'])->count(),
            'naissances.faible_poids'      => (clone $naissances)->where('etat', 'vivant')->where('poids_g', '<', 2500)->count(),
            'deces.maternels'              => (clone $accouchements)->where('etat_mere', 'deces')->count(),
            'urgences.passages'            => Passage::where('urgence', true)->whereBetween('arrive_at', $periode)->count(),
            'hospitalisations.admissions'  => Hospitalisation::whereBetween('date_entree', [$du->toDateString(), $au->toDateString()])->count(),
            'hospitalisations.sorties'     => (clone $sorties)->count(),
            'hospitalisations.deces'       => (clone $sorties)->where('statut_sortie', 'Décès')->count(),
            'hospitalisations.journees'    => (int) (clone $sorties)->sum(DB::raw('GREATEST(DATEDIFF(date_sortie, date_entree), 1)')),
            'references.recues'            => (clone $references)->where('sens', 'entrante')->count(),
            'references.emises'            => (clone $references)->where('sens', 'sortante')->count(),
            'references.evacuations'       => (clone $references)->where('sens', 'sortante')->where('evacuation', true)->count(),
            'references.contre_references' => (clone $references)->whereNotNull('contre_reference_at')->count(),
            'gratuites.montant'            => (float) TicketItem::whereHas('ticket', fn($q) => $q->whereBetween('created_at', $periode)->where('statut', '!=', 'annule'))->sum('part_gratuite'),
        ];

        $diagnostics = Consultation::join('diagnostics', 'diagnostics.id', '=', 'consultations.diagnostic_id')
            ->whereBetween('consultations.date_consultation', $periode)
            ->selectRaw('diagnostics.code, diagnostics.libelle, COUNT(*) as total')
            ->groupBy('diagnostics.code', 'diagnostics.libelle')->orderByDesc('total')->limit(20)->get()
            ->map(fn($d) => ['code' => $d->code, 'libelle' => $d->libelle, 'total' => (int) $d->total])->all();

        return ['indicateurs' => $valeurs, 'diagnostics' => $diagnostics, 'calcule_le' => now()->toDateTimeString()];
    }
}
