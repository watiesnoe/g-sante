<?php

namespace App\Services;

use App\Models\Etablissement;
use App\Models\Lot;
use App\Models\Medicament;
use App\Models\MouvementStock;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Quantification des besoins par la consommation moyenne mensuelle (CMM) :
 *
 *   point de commande = CMM × (délai + sécurité)
 *   stock maximum     = CMM × (délai + sécurité + période couverte)
 *   quantité proposée = stock maximum − stock disponible − quantités déjà commandées
 *
 * La consommation compte les dispensations et dotations aux services, nettes de
 * leurs annulations ; les transferts entre magasins n'en sont pas. Les jours de
 * rupture sont retirés de la période, sinon la CMM serait sous-estimée.
 * Toutes les quantités sont en unité de base du médicament.
 */
class BesoinsService
{
    public const TYPES_CONSOMMATION = ['sortie', 'dotation'];

    /** @return array{periode:int, delai:int, couverture:int, securite:int} */
    public function parametres(): array
    {
        $e = Etablissement::courant();

        return [
            'periode'    => max(1, (int) $e->parametre('stock.besoins_periode_mois')),
            'delai'      => max(0, (int) $e->parametre('stock.besoins_delai_mois')),
            'couverture' => max(1, (int) $e->parametre('stock.besoins_couverture_mois')),
            'securite'   => max(0, (int) $e->parametre('stock.besoins_securite_mois')),
        ];
    }

    /**
     * État des besoins, un élément par médicament à suivre.
     *
     * @param  array<int>|null  $medicamentIds  limite le calcul à ces médicaments
     * @return Collection<int, array>
     */
    public function etat(?array $medicamentIds = null): Collection
    {
        $p = $this->parametres();
        $fin = now();
        $debut = $fin->copy()->subMonthsNoOverflow($p['periode']);
        $joursPeriode = max(1, $debut->diffInDays($fin));

        $consommations = $this->consommations($debut, $medicamentIds);
        $rupture = $this->joursDeRupture($debut, $fin, $medicamentIds);
        $disponibles = Lot::disponibles()
            ->when($medicamentIds, fn($q) => $q->whereIn('medicament_id', $medicamentIds))
            ->groupBy('medicament_id')->selectRaw('medicament_id, SUM(quantite) as total')->pluck('total', 'medicament_id');
        $enCommande = $this->enCommande($medicamentIds);

        return Medicament::with('uniteDefault')
            ->when($medicamentIds, fn($q) => $q->whereIn('id', $medicamentIds))
            ->orderBy('nom')->get()
            ->map(function (Medicament $m) use ($p, $joursPeriode, $consommations, $rupture, $disponibles, $enCommande) {
                $consommation = (int) ($consommations[$m->id] ?? 0);
                $joursDisponibles = $joursPeriode - min($joursPeriode, (int) ($rupture[$m->id] ?? 0));
                // Moins d'une semaine de stock sur la période : trop peu pour corriger la rupture
                $cmm = $consommation > 0 ? $consommation / (max($joursDisponibles, 7) / 30) : 0.0;

                $disponible = (int) ($disponibles[$m->id] ?? 0);
                $commande = (int) ($enCommande[$m->id] ?? 0);

                if ($cmm > 0) {
                    $pointCommande = $cmm * ($p['delai'] + $p['securite']);
                    $stockMax = $cmm * ($p['delai'] + $p['securite'] + $p['couverture']);
                    $base = 'cmm';
                } elseif ($m->stock_min > 0) {
                    // Pas de consommation enregistrée : on retombe sur le seuil saisi à la main
                    $pointCommande = (float) $m->stock_min;
                    $stockMax = 2.0 * $m->stock_min;
                    $base = 'seuil';
                } else {
                    return null;
                }

                $proposeBase = max(0, (int) ceil($stockMax - $disponible - $commande));
                $facteur = max(1.0, (float) ($m->uniteDefault->facteur ?? 1));

                return [
                    'medicament'     => $m,
                    'base'           => $base,
                    'consommation'   => $consommation,
                    'jours_rupture'  => $joursPeriode - $joursDisponibles,
                    'cmm'            => round($cmm, 1),
                    'disponible'     => $disponible,
                    'en_commande'    => $commande,
                    'mois_stock'     => $cmm > 0 ? round($disponible / $cmm, 1) : null,
                    'point_commande' => (int) ceil($pointCommande),
                    'stock_max'      => (int) ceil($stockMax),
                    'propose_base'   => $proposeBase,
                    'unite'          => $m->uniteDefault,
                    'propose_unite'  => (int) ceil($proposeBase / $facteur),
                    'statut'         => match (true) {
                        $disponible <= 0                                  => 'rupture',
                        $disponible + $commande <= $pointCommande         => 'a_commander',
                        $cmm > 0 && $disponible > $stockMax               => 'surstock',
                        default                                           => 'ok',
                    },
                ];
            })
            ->filter()
            ->values();
    }

    /** CMM d'un médicament (unités de base par mois). */
    public function cmm(Medicament $medicament): float
    {
        return (float) ($this->etat([$medicament->id])->first()['cmm'] ?? 0);
    }

    /** Consommation nette depuis $debut : sorties et dotations, moins leurs annulations. */
    private function consommations($debut, ?array $ids): Collection
    {
        return MouvementStock::where('created_at', '>=', $debut)
            ->when($ids, fn($q) => $q->whereIn('medicament_id', $ids))
            ->where(function ($q) {
                $q->where(fn($s) => $s->whereIn('type', self::TYPES_CONSOMMATION)->whereNull('mouvement_annule_id'))
                    ->orWhereIn('mouvement_annule_id', MouvementStock::select('id')->whereIn('type', self::TYPES_CONSOMMATION));
            })
            ->groupBy('medicament_id')
            ->selectRaw('medicament_id, -SUM(quantite) as total')
            ->pluck('total', 'medicament_id');
    }

    /**
     * Jours où le stock total était nul, reconstitués à partir du stock après
     * chaque mouvement (et du dernier mouvement avant la période).
     *
     * @return array<int, int>
     */
    private function joursDeRupture($debut, $fin, ?array $ids): array
    {
        $initial = MouvementStock::whereIn('id',
            MouvementStock::where('created_at', '<', $debut)
                ->when($ids, fn($q) => $q->whereIn('medicament_id', $ids))
                ->groupBy('medicament_id')->selectRaw('MAX(id)')
        )->pluck('stock_apres', 'medicament_id');

        $mouvements = MouvementStock::where('created_at', '>=', $debut)
            ->when($ids, fn($q) => $q->whereIn('medicament_id', $ids))
            ->orderBy('id')->get(['medicament_id', 'stock_apres', 'created_at'])
            ->groupBy('medicament_id');

        $resultat = [];
        foreach ($mouvements->keys()->merge($initial->keys())->unique() as $id) {
            $stock = (int) ($initial[$id] ?? 0);
            $depuis = $debut->copy();
            $secondes = 0;
            foreach ($mouvements[$id] ?? [] as $mvt) {
                if ($stock <= 0) {
                    $secondes += $depuis->diffInSeconds($mvt->created_at);
                }
                $stock = (int) $mvt->stock_apres;
                $depuis = $mvt->created_at->copy();
            }
            if ($stock <= 0) {
                $secondes += $depuis->diffInSeconds($fin);
            }
            $resultat[$id] = (int) floor($secondes / 86400);
        }

        return $resultat;
    }

    /** Quantités commandées non encore reçues, converties en unités de base. */
    private function enCommande(?array $ids): Collection
    {
        return Etablissement::table('commandes')
            ->join('commande_medicaments as cm', 'cm.commande_id', '=', 'commandes.id')
            ->leftJoin('unites', 'unites.id', '=', 'cm.unite_id')
            ->where('commandes.statut', 'en_cours')
            ->when($ids, fn($q) => $q->whereIn('cm.medicament_id', $ids))
            ->groupBy('cm.medicament_id')
            ->selectRaw('cm.medicament_id, SUM(GREATEST(cm.quantite - cm.quantiterecue, 0) * COALESCE(NULLIF(unites.facteur, 0), 1)) as total')
            ->pluck('total', 'cm.medicament_id');
    }
}
