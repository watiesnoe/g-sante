<?php

namespace App\Http\Controllers;

use App\Exceptions\StockInsuffisantException;
use App\Models\Etablissement;
use App\Models\Magasin;
use App\Models\Medicament;
use App\Models\Requisition;
use App\Models\ServiceMedical;
use App\Services\NumerotationService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Réquisitions internes : un service demande des produits à la pharmacie, qui le
 * sert depuis son stock. Ce n'est pas un achat ; seule la pharmacie propose des
 * commandes aux fournisseurs (voir DemandeAchatController).
 */
class RequisitionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->can('stock.requisition') || $user->can('stock.servir'), 403, 'Accès non autorisé.');

        $statut = $request->statut ?: 'a_traiter';
        $requisitions = Requisition::with(['lignes.medicament', 'service', 'demandeur', 'serveur', 'magasin'])
            // Le service ne voit que ses demandes ; la pharmacie les voit toutes
            ->when(!$user->can('stock.servir'), fn($q) => $q->where('demandeur_id', $user->id))
            ->when($statut === 'a_traiter', fn($q) => $q->where('statut', 'soumise'), fn($q) => $q->when($statut !== 'toutes', fn($s) => $s->where('statut', $statut)))
            ->latest()->limit(200)->get();

        $stock = app(StockService::class);
        $magasins = Magasin::where('actif', true)->orderByDesc('par_defaut')->orderBy('nom')->get();
        $disponibles = [];
        if ($user->can('stock.servir')) {
            foreach ($requisitions->where('statut', 'soumise') as $r) {
                foreach ($r->lignes as $l) {
                    $disponibles[$l->medicament_id] ??= $stock->disponible($l->medicament, $r->magasin ?? Magasin::parDefaut());
                }
            }
        }

        return view('application.stock.requisitions', [
            'requisitions' => $requisitions,
            'statut'       => $statut,
            'disponibles'  => $disponibles,
            'magasins'     => $magasins,
            'services'     => ServiceMedical::orderBy('nom')->get(['id', 'nom']),
            'medicaments'  => Medicament::orderBy('nom')->get(['id', 'nom']),
        ]);
    }

    public function store(Request $request, NumerotationService $numerotation)
    {
        abort_unless(Auth::user()->can('stock.requisition'), 403, 'Accès non autorisé.');

        $validated = $request->validate([
            'service_medical_id'     => 'required|exists:service_medicals,id',
            'motif'                  => 'nullable|string|max:255',
            'lignes'                 => 'required|array|min:1',
            'lignes.*.medicament_id' => 'required|distinct|exists:medicaments,id',
            'lignes.*.quantite'      => 'required|integer|min:1',
        ], ['lignes.*.medicament_id.distinct' => 'Un même produit apparaît deux fois : regroupez les quantités.']);

        $requisition = DB::transaction(function () use ($validated, $numerotation) {
            $requisition = Requisition::create([
                'numero'             => $numerotation->suivant('requisition'),
                'service_medical_id' => $validated['service_medical_id'],
                'magasin_id'         => Magasin::parDefaut()->id,
                'motif'              => $validated['motif'] ?? null,
                'statut'             => 'soumise',
                'demandeur_id'       => Auth::id(),
            ]);
            foreach ($validated['lignes'] as $ligne) {
                $requisition->lignes()->create(['medicament_id' => $ligne['medicament_id'], 'quantite_demandee' => $ligne['quantite']]);
            }

            return $requisition;
        });

        return back()->with('success', 'Réquisition ' . $requisition->numero . ' envoyée à la pharmacie.');
    }

    /** La pharmacie sert la réquisition : une sortie « dotation » par ligne, depuis le magasin choisi. */
    public function servir(Request $request, Requisition $requisition, StockService $stock)
    {
        $user = Auth::user();
        abort_unless($user->can('stock.servir'), 403, 'Accès non autorisé.');

        if ($requisition->statut !== 'soumise') {
            return back()->with('error', 'Cette réquisition est déjà traitée.');
        }
        if (Etablissement::courant()->parametre('securite.separation_taches') && $requisition->demandeur_id === $user->id) {
            return back()->with('error', 'Séparation des tâches : un autre agent doit servir votre propre réquisition.');
        }

        $validated = $request->validate([
            'magasin_id'   => 'required|exists:magasins,id',
            'quantites'    => 'required|array',
            'quantites.*'  => 'nullable|integer|min:0',
        ]);
        if (array_sum(array_map('intval', $validated['quantites'])) === 0) {
            return back()->with('error', 'Aucune quantité servie : rejetez la réquisition en indiquant le motif.');
        }
        $magasin = Magasin::findOrFail($validated['magasin_id']);
        $lignes = $requisition->lignes()->with('medicament')->get();

        foreach ($lignes as $ligne) {
            if ((int) ($validated['quantites'][$ligne->id] ?? 0) > $ligne->quantite_demandee) {
                return back()->with('error', 'On ne sert pas plus que la quantité demandée (' . $ligne->medicament->nom . ').');
            }
        }

        try {
            DB::transaction(function () use ($validated, $requisition, $lignes, $magasin, $stock, $user) {
                $motif = 'Réquisition ' . $requisition->numero . ($requisition->service ? ' — ' . $requisition->service->nom : '');
                foreach ($lignes as $ligne) {
                    $quantite = (int) ($validated['quantites'][$ligne->id] ?? 0);
                    if ($quantite > 0) {
                        $stock->sortie($ligne->medicament, $quantite, $requisition, $motif, 'dotation', $magasin);
                    }
                    $ligne->update(['quantite_servie' => $quantite]);
                }
                $complete = $lignes->every(fn($l) => $l->quantite_servie >= $l->quantite_demandee);
                $requisition->update([
                    'statut'     => $complete ? 'servie' : 'partielle',
                    'magasin_id' => $magasin->id,
                    'servie_par' => $user->id,
                    'servie_at'  => now(),
                ]);
            });
        } catch (StockInsuffisantException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Réquisition ' . $requisition->numero . ' : ' . Requisition::STATUTS[$requisition->fresh()->statut] . '.');
    }

    public function rejeter(Request $request, Requisition $requisition)
    {
        abort_unless(Auth::user()->can('stock.servir'), 403, 'Accès non autorisé.');
        $request->validate(['motif' => 'required|string|max:255']);

        if ($requisition->statut !== 'soumise') {
            return back()->with('error', 'Cette réquisition est déjà traitée.');
        }
        $requisition->update(['statut' => 'rejetee', 'motif_rejet' => $request->motif, 'servie_par' => Auth::id(), 'servie_at' => now()]);

        return back()->with('success', 'Réquisition ' . $requisition->numero . ' rejetée.');
    }

    /** Le demandeur retire sa réquisition tant qu'elle n'est pas servie. */
    public function annuler(Requisition $requisition)
    {
        abort_unless($requisition->demandeur_id === Auth::id(), 403, 'Seul le demandeur peut annuler sa réquisition.');

        if ($requisition->statut !== 'soumise') {
            return back()->with('error', 'Cette réquisition est déjà traitée.');
        }
        $requisition->update(['statut' => 'annulee']);

        return back()->with('success', 'Réquisition ' . $requisition->numero . ' annulée.');
    }
}
