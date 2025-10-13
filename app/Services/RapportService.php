<?php

namespace App\Services;

use App\Models\CaisseMouvement;
use App\Models\Consultation;
use App\Models\Depense;
use App\Models\Passage;
use App\Models\Ticket;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * EF-13.02 : rapports d'activité et financiers de l'établissement courant,
 * filtrés par période et par service. Les chiffres viennent de colonnes
 * structurées, jamais de texte libre (RG-14).
 */
class RapportService
{
    public function generer(Carbon $du, Carbon $au, ?int $serviceId = null): array
    {
        $periode = [$du->copy()->startOfDay(), $au->copy()->endOfDay()];

        $passages = Passage::whereBetween('arrive_at', $periode)->when($serviceId, fn($q) => $q->where('service_medical_id', $serviceId));
        $consultations = Consultation::whereBetween('date_consultation', $periode)
            ->when($serviceId, fn($q) => $q->whereHas('medecin', fn($m) => $m->where('service_medical_id', $serviceId)));
        $tickets = Ticket::whereBetween('created_at', $periode)->where('statut', '!=', 'annule')
            ->when($serviceId, fn($q) => $q->whereHas('items.prestation', fn($p) => $p->where('service_medical_id', $serviceId)));

        return [
            'du' => $du, 'au' => $au,
            'activite' => [
                'passages'            => (clone $passages)->count(),
                'passages_par_statut' => (clone $passages)->selectRaw('statut, COUNT(*) as total')->groupBy('statut')->pluck('total', 'statut')->all(),
                'consultations'       => (clone $consultations)->count(),
                'par_medecin'         => (clone $consultations)->join('users', 'users.id', '=', 'consultations.medecin_id')
                    ->selectRaw('users.name as libelle, COUNT(*) as total')->groupBy('users.name')->orderByDesc('total')->pluck('total', 'libelle')->all(),
                'par_type'            => (clone $consultations)->selectRaw('type_activite as libelle, COUNT(*) as total')->groupBy('type_activite')->pluck('total', 'libelle')->all(),
                'diagnostics'         => (clone $consultations)->join('diagnostics', 'diagnostics.id', '=', 'consultations.diagnostic_id')
                    ->selectRaw("CONCAT(diagnostics.code, ' — ', diagnostics.libelle) as libelle, COUNT(*) as total")->groupBy('diagnostics.code', 'diagnostics.libelle')->orderByDesc('total')->limit(10)->pluck('total', 'libelle')->all(),
            ],
            'facturation' => [
                'tickets'        => (clone $tickets)->count(),
                'total'          => (float) (clone $tickets)->sum('total'),
                'part_patient'   => (float) (clone $tickets)->sum('part_patient'),
                'part_assurance' => (float) (clone $tickets)->sum('part_assurance'),
            ],
            'caisse' => [
                'par_mode' => CaisseMouvement::whereBetween('created_at', $periode)
                    ->selectRaw("mode, SUM(CASE WHEN type = 'entree' THEN montant ELSE 0 END) as entrees, SUM(CASE WHEN type = 'sortie' THEN montant ELSE 0 END) as sorties")
                    ->groupBy('mode')->get()->keyBy('mode')->map(fn($l) => ['entrees' => (float) $l->entrees, 'sorties' => (float) $l->sorties])->all(),
                'par_jour' => CaisseMouvement::whereBetween('created_at', $periode)
                    ->selectRaw("DATE(created_at) as jour, SUM(CASE WHEN type = 'entree' THEN montant ELSE -montant END) as net")
                    ->groupBy(DB::raw('DATE(created_at)'))->orderBy('jour')->pluck('net', 'jour')->map(fn($v) => (float) $v)->all(),
            ],
            // EF-12.02 : recettes facturées et dépenses payées rattachées à chaque service
            'analytique' => $this->analytique($du, $au, $periode),
            'depenses' => Depense::whereBetween('date_depense', [$du->toDateString(), $au->toDateString()])->where('statut', 'payee')
                ->when($serviceId, fn($q) => $q->where('service_medical_id', $serviceId))
                ->join('categories_depense', 'categories_depense.id', '=', 'depenses.categorie_depense_id')
                ->selectRaw('categories_depense.nom as libelle, SUM(depenses.montant) as total')->groupBy('categories_depense.nom')
                ->pluck('total', 'libelle')->map(fn($v) => (float) $v)->all(),
        ];
    }

    private function analytique(Carbon $du, Carbon $au, array $periode): array
    {
        $recettes = DB::table('ticket_items')
            ->join('tickets', 'tickets.id', '=', 'ticket_items.ticket_id')
            ->join('prestations', 'prestations.id', '=', 'ticket_items.prestation_id')
            ->leftJoin('service_medicals', 'service_medicals.id', '=', 'prestations.service_medical_id')
            ->where('tickets.etablissement_id', \App\Models\Etablissement::courantId())
            ->where('tickets.statut', '!=', 'annule')->whereBetween('tickets.created_at', $periode)
            ->selectRaw("COALESCE(service_medicals.nom, 'Sans service') as service, SUM(ticket_items.sous_total) as total")
            ->groupBy('service_medicals.nom')->pluck('total', 'service');

        $depenses = Depense::where('statut', 'payee')->whereBetween('date_depense', [$du->toDateString(), $au->toDateString()])
            ->leftJoin('service_medicals', 'service_medicals.id', '=', 'depenses.service_medical_id')
            ->selectRaw("COALESCE(service_medicals.nom, 'Sans service') as service, SUM(depenses.montant) as total")
            ->groupBy('service_medicals.nom')->pluck('total', 'service');

        return $recettes->keys()->merge($depenses->keys())->unique()->sort()->mapWithKeys(fn($s) => [$s => [
            'recettes' => (float) ($recettes[$s] ?? 0),
            'depenses' => (float) ($depenses[$s] ?? 0),
            'resultat' => (float) ($recettes[$s] ?? 0) - (float) ($depenses[$s] ?? 0),
        ]])->all();
    }

    /** Lignes à plat pour l'export Excel : [rubrique, libellé, valeur]. */
    public function lignes(array $r): array
    {
        $lignes = [['Rubrique', 'Libellé', 'Valeur']];
        $ajouter = function (string $rubrique, array $valeurs) use (&$lignes) {
            foreach ($valeurs as $libelle => $valeur) {
                $lignes[] = [$rubrique, (string) $libelle, $valeur];
            }
        };

        $ajouter('Activité', ['Passages' => $r['activite']['passages'], 'Consultations' => $r['activite']['consultations']]);
        $ajouter('Passages par statut', $r['activite']['passages_par_statut']);
        $ajouter('Consultations par médecin', $r['activite']['par_medecin']);
        $ajouter('Consultations par type', $r['activite']['par_type']);
        $ajouter('Diagnostics les plus fréquents', $r['activite']['diagnostics']);
        $ajouter('Facturation', [
            'Tickets' => $r['facturation']['tickets'], 'Total facturé' => $r['facturation']['total'],
            'Part patients' => $r['facturation']['part_patient'], 'Part organismes' => $r['facturation']['part_assurance'],
        ]);
        foreach ($r['caisse']['par_mode'] as $mode => $m) {
            $ajouter('Caisse — ' . (CaisseMouvement::MODES[$mode] ?? $mode), ['Entrées' => $m['entrees'], 'Sorties' => $m['sorties']]);
        }
        $ajouter('Caisse — net par jour', $r['caisse']['par_jour']);
        $ajouter('Dépenses payées', $r['depenses']);
        foreach ($r['analytique'] as $service => $a) {
            $ajouter('Analytique — ' . $service, ['Recettes' => $a['recettes'], 'Dépenses' => $a['depenses'], 'Résultat' => $a['resultat']]);
        }

        return $lignes;
    }
}
