<?php

namespace App\Http\Controllers;

use App\Models\Famille;
use App\Models\Medicament;
use App\Models\Unite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class MedicamentController extends Controller
{
    public function index(Request $request, $famille = null)
    {
        abort_unless(Auth::user()->can('stock.medicaments'), 403, 'Accès non autorisé : vous n\'avez pas accès à la gestion des médicaments.');

        $selectedFamilleId = null;
        if ($famille) {
            $familleModel = Famille::where('uuid', $famille)
                ->orWhere('id', $famille)
                ->first();
            $selectedFamilleId = $familleModel?->id;
        } else {
            $selectedFamilleId = $request->get('famille_id');
        }

        // 🎯 Détection stricte pour différencier l'état actif (1) de l'état inactif (0)
        $filterStockCritique = $request->input('stock_critique') == 1 || $request->input('stock_critique') === '1';

        if ($request->ajax()) {
            $query = Medicament::with(['unite', 'famille']);

            if ($selectedFamilleId) {
                $query->where('famille_id', $selectedFamilleId);
            }

            // 🎯 Utilisation du scope centralisé du modèle
            if ($filterStockCritique) {
                $query->critique();
            }

            return DataTables::of($query)
                ->addColumn('checkbox', function ($m) {
                    return '<input type="checkbox" class="form-check-input medicament-checkbox" value="' . $m->uuid . '">';
                })
                ->addColumn('unite', function ($m) {
                    return $m->unite?->nom ?? '-';
                })
                ->addColumn('famille', function ($m) {
                    return $m->famille?->nom ?? '-';
                })
                ->addColumn('actions', function ($m) {
                    $user = Auth::user();
                    $html = '';

                    if ($user->can('stock.medicaments')) {
                        $html .= '<button type="button" class="btn btn-sm btn-outline-primary view"   data-id="' . $m->uuid . '" title="Détails"><i class="fa fa-eye"></i></button>';
                        $html .= '<button type="button" class="btn btn-sm btn-outline-info edit"    data-id="' . $m->uuid . '" title="Modifier"><i class="fa fa-pencil-alt"></i></button>';
                        $html .= '<button type="button" class="btn btn-sm btn-outline-danger delete" data-id="' . $m->uuid . '" title="Supprimer"><i class="fa fa-trash"></i></button>';
                    }

                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $html . '</div>';
                })
                ->rawColumns(['actions', 'checkbox'])
                ->make(true);
        }

        $familles       = Famille::orderBy('nom')->get();
        $unites         = Unite::all();
        $totalMolecules = Medicament::count();

        // 🎯 Utilisation du scope pour le compteur global
        $stockCritique  = Medicament::critique()->count();

        $pageTitle = '🩺 Gestion des Médicaments';
        if ($selectedFamilleId) {
            $familleObj = $familles->find($selectedFamilleId);
            if ($familleObj) {
                $pageTitle = '💊 Gestion des ' . $familleObj->nom;
            }
        }

        return view('application.medicament.index', compact('familles', 'unites', 'totalMolecules', 'stockCritique', 'pageTitle', 'selectedFamilleId', 'filterStockCritique'));
    }

    public function show($id)
    {
        abort_unless(Auth::user()->can('stock.medicaments'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les médicaments.');

        $medicament = Medicament::where('uuid', $id)->orWhere('id', $id)->firstOrFail();

        if (request()->ajax()) {
            return response()->json($medicament->load(['unite', 'famille']));
        }

        $medicament->load(['unite', 'famille', 'protocoles.maladie']);
        return view('application.medicament.show', compact('medicament'));
    }

    public function create()
    {
        abort_unless(Auth::user()->can('stock.medicaments'), 403, 'Accès non autorisé : vous n\'avez pas la permission de créer un médicament.');

        $unites  = Unite::all();
        $familles = Famille::all();

        return view('application.medicament.create', compact('unites', 'familles'));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('stock.medicaments'), 403, 'Accès non autorisé : vous n\'avez pas la permission de créer un médicament.');

        $validated = $request->validate([
            'nom'         => 'required|string|max:255',
            'description' => 'nullable|string',
            'stock'       => 'required|integer|min:0',
            'stock_min'   => 'required|integer|min:0',
            'prix_achat'  => 'required|numeric|min:0',
            'prix_vente'  => 'required|numeric|min:0',
            'unite_id'    => 'required|exists:unites,id',
            'famille_id'  => 'required|exists:familles,id',
        ]);

        $medicament = Medicament::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Médicament ajouté avec succès ✅',
            'data'    => $medicament,
        ]);
    }

    public function edit($id)
    {
        abort_unless(Auth::user()->can('stock.medicaments'), 403, 'Accès non autorisé : vous n\'avez pas la permission de modifier un médicament.');

        $medicament = Medicament::where('uuid', $id)->orWhere('id', $id)->firstOrFail();
        $unites  = Unite::all();
        $familles = Famille::all();

        return view('application.medicament.create', compact('medicament', 'unites', 'familles'));
    }

    public function update(Request $request, $id)
    {
        abort_unless(Auth::user()->can('stock.medicaments'), 403, 'Accès non autorisé : vous n\'avez pas la permission de modifier un médicament.');

        $medicament = Medicament::where('uuid', $id)->orWhere('id', $id)->firstOrFail();

        $validated = $request->validate([
            'nom'         => 'required|string|max:255',
            'description' => 'nullable|string',
            'stock'       => 'required|integer|min:0',
            'stock_min'   => 'required|integer|min:0',
            'prix_achat'  => 'required|numeric|min:0',
            'prix_vente'  => 'required|numeric|min:0',
            'unite_id'    => 'required|exists:unites,id',
            'famille_id'  => 'required|exists:familles,id',
        ]);

        $medicament->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Médicament mis à jour avec succès ✏️',
        ]);
    }

    public function destroy($id)
    {
        abort_unless(Auth::user()->can('stock.medicaments'), 403, 'Accès non autorisé : vous n\'avez pas la permission de supprimer un médicament.');

        $medicament = Medicament::where('uuid', $id)->orWhere('id', $id)->firstOrFail();
        $medicament->delete();

        return response()->json([
            'success' => true,
            'message' => 'Médicament supprimé avec succès 🗑️',
        ]);
    }
}
