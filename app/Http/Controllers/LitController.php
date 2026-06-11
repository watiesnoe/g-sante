<?php

namespace App\Http\Controllers;

use App\Models\Lit;
use App\Models\Salle;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LitController extends Controller
{
    // 1. Liste des lits (Avec DataTable Ajax & Protection par permission)
    public function index(Request $request)
    {
        // Protection de la route avec la permission dédiée aux lits
        abort_unless(Auth::user()->can('parametres.lits'), 403, 'Accès non autorisé.');

        if ($request->ajax()) {
            // Récupération des lits triés par date décroissante avec leur salle liée
            $lits = Lit::with('salle')->orderBy('created_at', 'desc')->select('lits.*');

            return DataTables::of($lits)
                ->addIndexColumn() // Numéro de ligne automatique
                ->editColumn('statut', function ($row) {
                    $class = match ($row->statut) {
                        'Libre' => 'success',
                        'Occupé' => 'warning',
                        'Maintenance' => 'danger',
                        default => 'secondary',
                    };
                    return '<span class="badge bg-' . $class . '">' . $row->statut . '</span>';
                })
                ->addColumn('salle', function ($row) {
                    return $row->salle->nom ?? '-';
                })
                ->addColumn('actions', function ($row) {
                    // Utilisation de la colonne uuid (ou id si l'id principal de la table est une string UUID)
                    $uuid = $row->uuid ?? $row->id;

                    $view   = '<button type="button" class="btn btn-sm btn-outline-primary view" data-id="' . $uuid . '" title="Détails"><i class="fa fa-eye"></i></button>';
                    $edit   = '<button type="button" class="btn btn-sm btn-outline-info edit" data-id="' . $uuid . '" title="Modifier"><i class="fa fa-pencil-alt"></i></button>';
                    $delete = '<button type="button" class="btn btn-sm btn-outline-danger delete" data-id="' . $uuid . '" title="Supprimer"><i class="fa fa-trash"></i></button>';

                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $view . $edit . $delete . '</div>';
                })
                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at)->format('d-m-Y H:i');
                })
                ->rawColumns(['actions', 'statut'])
                ->make(true);
        }

        // Si ce n'est pas de l'AJAX, on charge la vue principale avec la liste des salles pour le modal
        $salles = Salle::all();
        return view('application.lit.index', compact('salles'));
    }

    // 2. Enregistrement d'un nouveau lit (Via Ajax POST)
    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('parametres.lits'), 403, 'Accès non autorisé.');

        $request->validate([
            'numero' => 'required|string|unique:lits,numero',
            'salle_id' => 'required|exists:salles,id',
            'statut' => 'required|in:Libre,Occupé,Maintenance',
        ]);

        $lit = Lit::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Lit ajouté avec succès ✨',
            'data' => $lit
        ], 201);
    }

    // 3. Récupération des détails d'un lit pour la Vue (Via Ajax GET avec UUID)
    public function show($id)
    {
        abort_unless(Auth::user()->can('parametres.lits'), 403, 'Accès non autorisé.');

        // Recherche du lit via l'UUID passé par le JavaScript
        $lit = Lit::where('uuid', $id)->orWhere('id', $id)->firstOrFail();

        return response()->json($lit);
    }

    // 4. Récupération des détails pour la Modification (Via Ajax GET avec UUID)
    public function edit($id)
    {
        abort_unless(Auth::user()->can('parametres.lits'), 403, 'Accès non autorisé.');

        $lit = Lit::where('uuid', $id)->orWhere('id', $id)->firstOrFail();
        return response()->json($lit);
    }

    // 5. Mise à jour d'un lit (Via Ajax PUT avec UUID)
    public function update(Request $request, $id)
    {
        abort_unless(Auth::user()->can('parametres.lits'), 403, 'Accès non autorisé.');

        // Recherche du lit par UUID avant modification
        $lit = Lit::where('uuid', $id)->orWhere('id', $id)->firstOrFail();

        $request->validate([
            'numero' => 'required|string|unique:lits,numero,' . $lit->id,
            'salle_id' => 'required|exists:salles,id',
            'statut' => 'required|in:Libre,Occupé,Maintenance',
        ]);

        $lit->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Lit mis à jour avec succès ✨',
            'data' => $lit
        ], 200);
    }

    // 6. Suppression d'un lit (Via Ajax DELETE avec UUID)
    public function destroy($id)
    {
        abort_unless(Auth::user()->can('parametres.lits'), 403, 'Accès non autorisé.');

        // Recherche du lit par UUID avant suppression
        $lit = Lit::where('uuid', $id)->orWhere('id', $id)->firstOrFail();
        $lit->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lit supprimé avec succès ✨'
        ], 200);
    }
}
