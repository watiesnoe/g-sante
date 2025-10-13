<?php

namespace App\Http\Controllers;

use App\Models\Unite;
use App\Models\Medicament;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class UniteController extends Controller
{
    /**
     * Liste des unités avec DataTables
     */
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('stock.unites'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les unités.');

        if ($request->ajax()) {
            $unites = Unite::with('medicament')->select('unites.*');

            return DataTables::of($unites)
                ->addIndexColumn()
                ->addColumn('medicament_nom', function ($row) {
                    return $row->medicament?->nom ?? '-';
                })
                ->editColumn('prix_achat', fn($row) => number_format($row->prix_achat, 2, ',', ' ') . ' F')
                ->editColumn('prix_vente', fn($row) => number_format($row->prix_vente, 2, ',', ' ') . ' F')
                ->editColumn('created_at', fn($row) => $row->created_at ? Carbon::parse($row->created_at)->format('d-m-Y') : '-')
                ->addColumn('actions', function ($row) {
                    $view   = '<button type="button" class="btn btn-sm btn-outline-primary view" data-id="'.$row->id.'" title="Détails"><i class="fa fa-eye"></i></button>';
                    $edit   = '<button type="button" class="btn btn-sm btn-outline-info edit" data-id="'.$row->id.'" title="Modifier"><i class="fa fa-pencil-alt"></i></button>';
                    $delete = '<button type="button" class="btn btn-sm btn-outline-danger delete" data-id="'.$row->id.'" title="Supprimer"><i class="fa fa-trash"></i></button>';

                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $view . $edit . $delete . '</div>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        $medicaments = Medicament::orderBy('nom')->get();
        // Types d'unités (Comprimé, Flacon, ...) pour la saisie du prix forfaitaire
        $typesUnites = Unite::selectRaw('nom, COUNT(*) as total')->groupBy('nom')->orderBy('nom')->get();
        return view('application.unite.index', compact('medicaments', 'typesUnites'));
    }

    /**
     * Appliquer un prix de vente (et, si saisi, d'achat) forfaitaire (FCFA) à toutes les unités d'un même type
     * (ex : tous les "Comprimé"), multiplié par le facteur pour les conditionnements.
     */
    public function prixForfaitaire(Request $request)
    {
        abort_unless(Auth::user()->can('stock.unites'), 403, 'Accès non autorisé.');

        $validated = $request->validate([
            'nom'              => ['required', 'string', $request->nom === '*' ? 'in:*' : 'exists:unites,nom'],
            'prix_forfaitaire' => 'required|numeric|min:0',
            'prix_achat'       => 'nullable|numeric|min:0',
            'conserver'        => 'nullable|boolean',
        ]);

        // nom = "*" : toutes les unités, uniquement celles qui n'ont pas encore de prix
        $toutes = $validated['nom'] === '*';
        $conserver = $toutes || $request->boolean('conserver');
        $cible = fn() => Unite::when(!$toutes, fn($q) => $q->where('nom', $validated['nom']));

        $nb = $cible()
            ->when($conserver, fn($q) => $q->where('prix_vente', 0))
            ->update(['prix_vente' => DB::raw('facteur * ' . (float) $validated['prix_forfaitaire'])]);

        // Prix d'achat forfaitaire : facultatif
        if ($request->filled('prix_achat')) {
            $cible()
                ->when($conserver, fn($q) => $q->where('prix_achat', 0))
                ->update(['prix_achat' => DB::raw('facteur * ' . (float) $validated['prix_achat'])]);
        }

        return response()->json([
            'message' => $toutes
                ? "Prix forfaitaire appliqué à $nb médicament(s) sans prix."
                : "Prix forfaitaire appliqué à $nb médicament(s) en « {$validated['nom']} »."
        ]);
    }

    /**
     * Enregistrer une nouvelle unité
     */
    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('stock.unites'), 403, 'Accès non autorisé.');

        $validated = $request->validate([
            'nom'           => 'required|string|max:255',
            'symbole'       => 'required|string|max:50',
            'facteur'       => 'required|numeric|min:0.01',
            'prix_achat'    => 'required|numeric|min:0',
            'prix_vente'    => 'required|numeric|min:0',
            'medicament_id' => 'required|exists:medicaments,id',
            'is_default'    => 'nullable|boolean',
        ]);

        $validated['uuid'] = (string) Str::uuid();
        $validated['is_default'] = $request->boolean('is_default');

        // If marked default, unmark other units of the same medication
        if ($validated['is_default']) {
            Unite::where('medicament_id', $validated['medicament_id'])->update(['is_default' => false]);
        }

        $unite = Unite::create($validated);

        // Ensure at least one unit is default
        if (Unite::where('medicament_id', $unite->medicament_id)->where('is_default', true)->count() === 0) {
            $unite->is_default = true;
            $unite->save();
        }

        return response()->json([
            'message' => 'Unité ajoutée avec succès ✅',
            'unite' => $unite
        ]);
    }

    /**
     * Récupérer les données d'une unité
     */
    public function show($id)
    {
        abort_unless(Auth::user()->can('stock.unites'), 403, 'Accès non autorisé.');

        $unite = Unite::with('medicament')->findOrFail($id);

        return response()->json($unite);
    }

    /**
     * Formulaire de modification
     */
    public function edit($id)
    {
        abort_unless(Auth::user()->can('stock.unites'), 403, 'Accès non autorisé.');

        $unite = Unite::findOrFail($id);

        return response()->json($unite);
    }

    /**
     * Mettre à jour une unité existante
     */
    public function update(Request $request, $id)
    {
        abort_unless(Auth::user()->can('stock.unites'), 403, 'Accès non autorisé.');

        $unite = Unite::findOrFail($id);

        $validated = $request->validate([
            'nom'           => 'required|string|max:255',
            'symbole'       => 'required|string|max:50',
            'facteur'       => 'required|numeric|min:0.01',
            'prix_achat'    => 'required|numeric|min:0',
            'prix_vente'    => 'required|numeric|min:0',
            'medicament_id' => 'required|exists:medicaments,id',
            'is_default'    => 'nullable|boolean',
        ]);

        $validated['is_default'] = $request->boolean('is_default');

        if ($validated['is_default']) {
            Unite::where('medicament_id', $validated['medicament_id'])->where('id', '!=', $unite->id)->update(['is_default' => false]);
        }

        $unite->update($validated);

        // Ensure at least one unit is default
        if (Unite::where('medicament_id', $unite->medicament_id)->where('is_default', true)->count() === 0) {
            $unite->is_default = true;
            $unite->save();
        }

        return response()->json([
            'message' => 'Unité mise à jour avec succès ✏️'
        ]);
    }

    /**
     * Supprimer une unité
     */
    public function destroy($id)
    {
        abort_unless(Auth::user()->can('stock.unites'), 403, 'Accès non autorisé.');

        $unite = Unite::findOrFail($id);
        $medicamentId = $unite->medicament_id;
        $unite->delete();

        // Ensure at least one unit is default
        if (Unite::where('medicament_id', $medicamentId)->where('is_default', true)->count() === 0) {
            $firstUnite = Unite::where('medicament_id', $medicamentId)->first();
            if ($firstUnite) {
                $firstUnite->is_default = true;
                $firstUnite->save();
            }
        }

        return response()->json([
            'message' => 'Unité supprimée avec succès 🗑️'
        ]);
    }
}
