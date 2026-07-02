<?php

namespace App\Http\Controllers;

use App\Models\Famille;
use App\Models\Inventaire;
use App\Models\InventaireLigne;
use App\Models\Medicament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventaireController extends Controller
{
    /**
     * Vérifie si l'utilisateur connecté peut accéder au module inventaire.
     * Les rôles super_admin et admin ont toujours accès.
     */
    private function checkAccess(): void
    {
        $user = Auth::user();
        abort_unless(
            $user->hasRole(['super_admin', 'admin']) || $user->can('stock.inventaire'),
            403,
            'Accès non autorisé.'
        );
    }

    /**
     * Liste de tous les inventaires (DataTable)
     */
    public function index(Request $request)
    {
        $this->checkAccess();

        if ($request->ajax()) {
            $inventaires = Inventaire::with('user')
                ->latest()
                ->get();

            return datatables()->of($inventaires)
                ->editColumn('date_inventaire', fn($i) => Carbon::parse($i->date_inventaire)->format('d/m/Y'))
                ->editColumn('statut', function ($i) {
                    return match ($i->statut) {
                        'validé'  => '<span class="badge bg-success">Validé</span>',
                        'annulé'  => '<span class="badge bg-danger">Annulé</span>',
                        default   => '<span class="badge bg-warning text-dark">Brouillon</span>',
                    };
                })
                ->addColumn('responsable', fn($i) => $i->user->name ?? '-')
                ->addColumn('nb_medicaments', fn($i) => $i->lignes()->count())
                ->addColumn('taux_conformite', fn($i) => $i->taux_conformite . ' %')
                ->addColumn('actions', function ($i) {
                    $html = '<div class="d-flex gap-1 justify-content-center">';
                    $html .= '<a href="' . route('inventaires.show', $i->uuid) . '" class="btn btn-sm btn-alt-secondary" title="Voir"><i class="fa fa-eye text-primary"></i></a>';
                    if ($i->statut === 'brouillon') {
                        $html .= '<a href="' . route('inventaires.edit', $i->uuid) . '" class="btn btn-sm btn-alt-secondary" title="Modifier"><i class="fa fa-pencil text-warning"></i></a>';
                        $html .= '<button type="button" class="btn btn-sm btn-alt-secondary btn-valider" data-id="' . $i->uuid . '" title="Valider"><i class="fa fa-check-circle text-success"></i></button>';
                        $html .= '<button type="button" class="btn btn-sm btn-alt-secondary btn-delete" data-url="' . route('inventaires.destroy', $i->uuid) . '" title="Supprimer"><i class="fa fa-trash text-danger"></i></button>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['statut', 'actions'])
                ->make(true);
        }

        return view('application.inventaire.index');
    }

    /**
     * Formulaire de création — pré-rempli avec tous les médicaments en stock
     */
    public function create()
    {
        $this->checkAccess();

        // Grouper les médicaments par famille pour les onglets de catégories
        $familles = Famille::with(['medicaments' => function ($q) {
                $q->with(['unite'])->orderBy('nom');
            }])
            ->whereHas('medicaments')
            ->orderBy('nom')
            ->get();

        // Médicaments sans famille regroupés dans un onglet spécial
        $sansFamille = Medicament::with(['unite'])
            ->whereNull('famille_id')
            ->orderBy('nom')
            ->get();

        return view('application.inventaire.create', compact('familles', 'sansFamille'));
    }

    /**
     * Enregistrement d'un nouvel inventaire
     */
    public function store(Request $request)
    {
        $this->checkAccess();

        $request->validate([
            'date_inventaire'  => 'required|date',
            'observations'     => 'nullable|string|max:1000',
            'stock_reel'       => 'required|array',
            'stock_reel.*'     => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $inventaire = Inventaire::create([
                'reference'       => Inventaire::genererReference(),
                'date_inventaire' => $request->date_inventaire,
                'observations'    => $request->observations,
                'statut'          => 'brouillon',
                'user_id'         => Auth::id(),
            ]);

            $medicaments = Medicament::whereIn('id', array_keys($request->stock_reel))->get()->keyBy('id');

            foreach ($request->stock_reel as $medId => $stockReel) {
                if (!isset($medicaments[$medId])) continue;
                $medicament = $medicaments[$medId];

                InventaireLigne::create([
                    'inventaire_id'    => $inventaire->id,
                    'medicament_id'    => $medId,
                    'stock_theorique'  => $medicament->stock,
                    'stock_reel'       => (int) $stockReel,
                    'observations'     => $request->obs_ligne[$medId] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('inventaires.show', $inventaire->uuid)
                ->with('success', 'Inventaire "' . $inventaire->reference . '" créé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur : ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Afficher le détail d'un inventaire
     */
    public function show(Inventaire $inventaire)
    {
        $this->checkAccess();

        $inventaire->load(['user', 'lignes.medicament.unite', 'lignes.medicament.famille']);

        $stats = [
            'total'         => $inventaire->lignes->count(),
            'conformes'     => $inventaire->lignes->filter(fn($l) => $l->ecart === 0)->count(),
            'excedents'     => $inventaire->lignes->filter(fn($l) => $l->ecart > 0)->count(),
            'manquants'     => $inventaire->lignes->filter(fn($l) => $l->ecart < 0)->count(),
            'taux'          => $inventaire->taux_conformite,
        ];

        return view('application.inventaire.show', compact('inventaire', 'stats'));
    }

    /**
     * Formulaire d'édition (seulement si brouillon)
     */
    public function edit(Inventaire $inventaire)
    {
        $this->checkAccess();
        abort_if($inventaire->statut !== 'brouillon', 403, 'Cet inventaire ne peut plus être modifié.');

        $inventaire->load(['lignes.medicament.unite', 'lignes.medicament.famille']);

        // Grouper les lignes de l'inventaire par famille
        $lignesParFamille = $inventaire->lignes
            ->groupBy(fn($l) => $l->medicament->famille?->nom ?? '__sans_famille__');

        return view('application.inventaire.edit', compact('inventaire', 'lignesParFamille'));
    }

    /**
     * Mise à jour d'un inventaire brouillon
     */
    public function update(Request $request, Inventaire $inventaire)
    {
        $this->checkAccess();
        abort_if($inventaire->statut !== 'brouillon', 403, 'Cet inventaire ne peut plus être modifié.');

        $request->validate([
            'date_inventaire' => 'required|date',
            'observations'    => 'nullable|string|max:1000',
            'stock_reel'      => 'required|array',
            'stock_reel.*'    => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $inventaire->update([
                'date_inventaire' => $request->date_inventaire,
                'observations'    => $request->observations,
            ]);

            foreach ($request->stock_reel as $ligneId => $stockReel) {
                InventaireLigne::where('id', $ligneId)
                    ->where('inventaire_id', $inventaire->id)
                    ->update([
                        'stock_reel'    => (int) $stockReel,
                        'observations'  => $request->obs_ligne[$ligneId] ?? null,
                    ]);
            }

            DB::commit();
            return redirect()->route('inventaires.show', $inventaire->uuid)
                ->with('success', 'Inventaire mis à jour avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur : ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Valider un inventaire et appliquer les corrections de stock
     */
    public function valider(Inventaire $inventaire)
    {
        $this->checkAccess();
        abort_if($inventaire->statut !== 'brouillon', 403, 'Cet inventaire est déjà validé ou annulé.');

        DB::beginTransaction();
        try {
            // Appliquer les corrections de stock
            foreach ($inventaire->lignes as $ligne) {
                if ($ligne->ecart !== 0) {
                    $ligne->medicament->update(['stock' => $ligne->stock_reel]);
                }
            }

            $inventaire->update(['statut' => 'validé']);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Inventaire validé. Les stocks ont été mis à jour.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un inventaire brouillon
     */
    public function destroy(Inventaire $inventaire)
    {
        $this->checkAccess();
        abort_if($inventaire->statut !== 'brouillon', 403, 'Seuls les inventaires en brouillon peuvent être supprimés.');

        $inventaire->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Inventaire supprimé.']);
        }

        return redirect()->route('inventaires.index')->with('success', 'Inventaire supprimé.');
    }
}
