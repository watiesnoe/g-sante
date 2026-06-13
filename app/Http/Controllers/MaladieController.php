<?php

namespace App\Http\Controllers;

use App\Models\Maladie;
use App\Models\Symptome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class MaladieController extends Controller
{
    /**
     * Liste des maladies avec DataTables
     */
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('parametres.maladies'), 403, 'Accès non autorisé.');

        if ($request->ajax()) {
            $maladies = Maladie::with('symptomes');

            return DataTables::of($maladies)
                ->addColumn('symptomes', function ($row) {
                    return $row->symptomes->map(function ($s) {
                        return '<span class="badge bg-secondary me-1">' . e($s->nom) . '</span>';
                    })->implode('');
                })
                ->addColumn('actions', function ($row) {
                    $view   = '<button type="button" class="btn btn-sm btn-outline-primary view" data-id="'.$row->id.'" title="Détails"><i class="fa fa-eye"></i></button>';
                    $edit   = '<button type="button" class="btn btn-sm btn-outline-info edit" data-id="'.$row->id.'" title="Modifier"><i class="fa fa-pencil-alt"></i></button>';
                    $delete = '<button type="button" class="btn btn-sm btn-outline-danger delete" data-id="'.$row->id.'" title="Supprimer"><i class="fa fa-trash"></i></button>';

                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $view . $edit . $delete . '</div>';
                })
                ->rawColumns(['symptomes', 'actions'])
                ->make(true);
        }

        $symptomes = Symptome::all();
        return view('application.maladie.index', compact('symptomes'));
    }

    /**
     * Enregistrer une nouvelle maladie
     */
    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('parametres.maladies'), 403, 'Accès non autorisé.');

        $request->validate([
            'nom'         => 'required|string|unique:maladies,nom|max:255',
            'description' => 'nullable|string',
            'symptomes'   => 'nullable|array',
            'symptomes.*' => 'exists:symptomes,id',
        ]);

        $maladie = Maladie::create([
            'nom'         => $request->nom,
            'description' => $request->description,
        ]);

        if ($request->has('symptomes')) {
            $maladie->symptomes()->sync($request->symptomes);
        }

        return response()->json([
            'message' => 'Maladie ajoutée avec succès',
            'maladie' => $maladie
        ]);
    }

    /**
     * Afficher les détails d'une maladie
     */
    public function show($id)
    {
        abort_unless(Auth::user()->can('parametres.maladies'), 403, 'Accès non autorisé.');

        $maladie = Maladie::with('symptomes')->findOrFail($id);
        return response()->json($maladie);
    }

    /**
     * Charger les données pour la modification
     */
    public function edit($id)
    {
        abort_unless(Auth::user()->can('parametres.maladies'), 403, 'Accès non autorisé.');

        $maladie = Maladie::with('symptomes')->findOrFail($id);
        return response()->json($maladie);
    }

    /**
     * Mettre à jour une maladie
     */
    public function update(Request $request, $id)
    {
        abort_unless(Auth::user()->can('parametres.maladies'), 403, 'Accès non autorisé.');

        $maladie = Maladie::findOrFail($id);

        $request->validate([
            'nom'         => 'required|string|max:255|unique:maladies,nom,' . $maladie->id,
            'description' => 'nullable|string',
            'symptomes'   => 'nullable|array',
            'symptomes.*' => 'exists:symptomes,id',
        ]);

        $maladie->update([
            'nom'         => $request->nom,
            'description' => $request->description,
        ]);

        $maladie->symptomes()->sync($request->symptomes ?? []);

        return response()->json([
            'message' => 'Maladie mise à jour avec succès'
        ]);
    }

    /**
     * Supprimer une maladie
     */
    public function destroy($id)
    {
        abort_unless(Auth::user()->can('parametres.maladies'), 403, 'Accès non autorisé.');

        $maladie = Maladie::findOrFail($id);
        $maladie->symptomes()->detach();
        $maladie->delete();

        return response()->json([
            'message' => 'Maladie supprimée avec succès'
        ]);
    }
}