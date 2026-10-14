<?php

namespace App\Services;

use App\Exceptions\StockInsuffisantException;
use App\Models\Lot;
use App\Models\Magasin;
use App\Models\Medicament;
use App\Models\MouvementStock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Seul point d'écriture du stock (RG-08 à RG-11, QUA-01).
 *
 * Chaque opération s'exécute dans une transaction et verrouille en écriture le
 * médicament concerné, ce qui sérialise les sorties simultanées (RG-09, ENF-03).
 * Les quantités sont exprimées en unité de base du médicament.
 */
class StockService
{
    /**
     * Entrée de stock dans un lot (créé s'il n'existe pas encore).
     */
    public function entree(Medicament $medicament, int $quantite, ?string $numeroLot, $datePeremption, ?Model $source, ?string $motif = null, string $type = 'entree', ?Magasin $magasin = null): MouvementStock
    {
        $this->exigerPositif($quantite);

        return DB::transaction(function () use ($medicament, $quantite, $numeroLot, $datePeremption, $source, $motif, $type, $magasin) {
            $this->verrouiller($medicament);
            $lot = $this->trouverOuCreerLot($medicament, $numeroLot, $datePeremption, $magasin);

            return $this->appliquer($medicament, $lot, $quantite, $type, $source, $motif);
        });
    }

    /**
     * Sortie FEFO : prélève dans les lots non périmés, du plus proche de la
     * péremption au plus lointain (RG-10). Refuse si la quantité utilisable
     * est insuffisante (RG-09).
     *
     * @return Collection<MouvementStock>
     */
    public function sortie(Medicament $medicament, int $quantite, ?Model $source, ?string $motif = null, string $type = 'sortie', ?Magasin $magasin = null): Collection
    {
        $this->exigerPositif($quantite);
        $magasin ??= Magasin::parDefaut();

        return DB::transaction(function () use ($medicament, $quantite, $source, $motif, $type, $magasin) {
            $this->verrouiller($medicament);

            // EF-08.07 : on ne prélève que dans le magasin concerné (par défaut, celui de la dispensation)
            $lots = Lot::where('medicament_id', $medicament->id)->where('magasin_id', $magasin->id)->disponibles()->fefo()->lockForUpdate()->get();
            $disponible = (int) $lots->sum('quantite');

            if ($disponible < $quantite) {
                throw new StockInsuffisantException("Stock insuffisant pour « {$medicament->nom} » ({$magasin->nom}) : {$disponible} disponible(s) hors produits périmés, {$quantite} demandé(s).");
            }

            $mouvements = collect();
            $reste = $quantite;
            foreach ($lots as $lot) {
                $preleve = min($reste, $lot->quantite);
                $mouvements->push($this->appliquer($medicament, $lot, -$preleve, $type, $source, $motif));
                $reste -= $preleve;
                if ($reste === 0) {
                    break;
                }
            }

            return $mouvements;
        });
    }

    /**
     * Retire une quantité d'un lot précis (retour fournisseur, perte d'un lot
     * périmé, correction d'une réception). Refuse si le lot n'en contient plus assez.
     */
    public function retirerDuLot(Lot $lot, int $quantite, ?Model $source, ?string $motif = null, string $type = 'retour'): MouvementStock
    {
        $this->exigerPositif($quantite);

        return DB::transaction(function () use ($lot, $quantite, $source, $motif, $type) {
            $medicament = $lot->medicament;
            $this->verrouiller($medicament);
            $lot = Lot::whereKey($lot->id)->lockForUpdate()->firstOrFail();

            if ($lot->quantite < $quantite) {
                throw new StockInsuffisantException("Le lot {$lot->numero} de « {$medicament->nom} » ne contient plus que {$lot->quantite} unité(s) : impossible d'en retirer {$quantite}.");
            }

            return $this->appliquer($medicament, $lot, -$quantite, $type, $source, $motif);
        });
    }

    /**
     * Ajustement d'inventaire (RG-11) : crée les mouvements qui amènent le stock
     * à la quantité comptée, sans écraser la quantité.
     *
     * @return Collection<MouvementStock>
     */
    public function ajuster(Medicament $medicament, int $stockCompte, ?Model $source, ?string $motif = null): Collection
    {
        return DB::transaction(function () use ($medicament, $stockCompte, $source, $motif) {
            $this->verrouiller($medicament);
            $ecart = $stockCompte - $this->stockReel($medicament);

            if ($ecart > 0) {
                $lot = $this->trouverOuCreerLot($medicament, 'AJUSTEMENT', null, null);

                return collect([$this->appliquer($medicament, $lot, $ecart, 'ajustement', $source, $motif)]);
            }

            // Manquant : on retire d'abord des lots périmés, puis dans l'ordre FEFO
            $mouvements = collect();
            $reste = -$ecart;
            $lots = Lot::where('medicament_id', $medicament->id)->where('quantite', '>', 0)->fefo()->lockForUpdate()->get();
            foreach ($lots as $lot) {
                if ($reste === 0) {
                    break;
                }
                $preleve = min($reste, $lot->quantite);
                $mouvements->push($this->appliquer($medicament, $lot, -$preleve, 'ajustement', $source, $motif));
                $reste -= $preleve;
            }

            return $mouvements;
        });
    }

    /**
     * Annule tous les mouvements d'une source (ex. paiement d'ordonnance annulé)
     * par des écritures inverses sur les mêmes lots. Les originaux sont conservés.
     *
     * @return Collection<MouvementStock>
     */
    public function annulerSource(Model $source, string $motif): Collection
    {
        return DB::transaction(function () use ($source, $motif) {
            $mouvements = MouvementStock::where('source_type', $source->getMorphClass())
                ->where('source_id', $source->getKey())
                ->whereNull('mouvement_annule_id')
                ->whereDoesntHave('annulation')
                ->orderBy('id')
                ->get();

            return $mouvements->map(function (MouvementStock $mouvement) use ($source, $motif) {
                $medicament = $mouvement->medicament;
                $this->verrouiller($medicament);
                $lot = Lot::whereKey($mouvement->lot_id)->lockForUpdate()->firstOrFail();

                if ($lot->quantite - $mouvement->quantite < 0) {
                    throw new StockInsuffisantException("Annulation impossible : le lot {$lot->numero} de « {$medicament->nom} » a déjà été en partie utilisé.");
                }

                $type = $mouvement->quantite < 0 ? 'retour' : 'ajustement';

                return $this->appliquer($medicament, $lot, -$mouvement->quantite, $type, $source, 'Annulation : '.$motif, $mouvement->id);
            });
        });
    }

    /** Quantité utilisable dans un magasin (par défaut, celui de la dispensation) : lots non périmés. */
    public function disponible(Medicament $medicament, ?Magasin $magasin = null): int
    {
        $magasin ??= Magasin::parDefaut();

        return (int) Lot::where('medicament_id', $medicament->id)->where('magasin_id', $magasin->id)->disponibles()->sum('quantite');
    }

    /**
     * EF-08.07 : transfert entre magasins. Sortie FEFO du magasin d'origine,
     * entrée des mêmes lots (numéro, péremption) dans le magasin de destination.
     *
     * @return Collection<MouvementStock>
     */
    public function transferer(Medicament $medicament, int $quantite, Magasin $de, Magasin $vers, ?string $motif = null): Collection
    {
        if ($de->is($vers)) {
            throw new \InvalidArgumentException('Choisissez deux magasins différents.');
        }

        return DB::transaction(function () use ($medicament, $quantite, $de, $vers, $motif) {
            $mouvements = collect();
            foreach ($this->sortie($medicament, $quantite, null, 'Transfert vers ' . $vers->nom . ($motif ? ' — ' . $motif : ''), 'transfert', $de) as $sortie) {
                $mouvements->push($sortie);
                $mouvements->push($this->entree($medicament, -$sortie->quantite, $sortie->lot->numero, $sortie->lot->date_peremption, $sortie, 'Transfert depuis ' . $de->nom, 'transfert', $vers));
            }

            return $mouvements;
        });
    }

    /** Stock réel = somme des mouvements (RG-08). */
    public function stockReel(Medicament $medicament): int
    {
        return (int) MouvementStock::where('medicament_id', $medicament->id)->sum('quantite');
    }

    private function appliquer(Medicament $medicament, Lot $lot, int $quantite, string $type, ?Model $source, ?string $motif, ?int $mouvementAnnuleId = null): MouvementStock
    {
        $lot->quantite += $quantite;
        $lot->save();

        $stockApres = $this->stockReel($medicament) + $quantite;
        \App\Models\Etablissement::table('medicaments')->where('id', $medicament->id)->update(['stock' => $stockApres]);
        $medicament->stock = $stockApres;

        return MouvementStock::create([
            'etablissement_id' => $lot->etablissement_id,
            'medicament_id' => $medicament->id,
            'lot_id' => $lot->id,
            'type' => $type,
            'quantite' => $quantite,
            'stock_apres' => $stockApres,
            'source_type' => $source?->getMorphClass(),
            'source_id' => $source?->getKey(),
            'motif' => $motif,
            'mouvement_annule_id' => $mouvementAnnuleId,
            'user_id' => Auth::id(),
        ]);
    }

    private function trouverOuCreerLot(Medicament $medicament, ?string $numero, $datePeremption, ?Magasin $magasin): Lot
    {
        $numero = trim((string) $numero) ?: 'SANS-LOT';
        $date = $datePeremption ? Carbon::parse($datePeremption)->toDateString() : null;
        $magasin ??= Magasin::parDefaut();

        $lot = Lot::where('medicament_id', $medicament->id)->where('magasin_id', $magasin->id)->where('numero', $numero)
            ->when($date, fn ($q) => $q->whereDate('date_peremption', $date), fn ($q) => $q->whereNull('date_peremption'))
            ->lockForUpdate()->first();

        return $lot ?? Lot::create([
            'medicament_id' => $medicament->id,
            'magasin_id' => $magasin->id,
            'numero' => $numero,
            'date_peremption' => $date,
            'quantite' => 0,
        ]);
    }

    /** Verrou en écriture sur le médicament : sérialise les opérations simultanées. */
    private function verrouiller(Medicament $medicament): void
    {
        \App\Models\Etablissement::table('medicaments')->where('id', $medicament->id)->lockForUpdate()->first();
    }

    private function exigerPositif(int $quantite): void
    {
        if ($quantite <= 0) {
            throw new \InvalidArgumentException('La quantité doit être strictement positive.');
        }
    }
}
