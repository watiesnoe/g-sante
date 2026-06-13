<?php

namespace App\Http\Controllers;

use App\Models\Unite;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class UniteController extends Controller
{
    /**
     * Liste des unités avec DataTables
     */
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('stock.unites'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les unités.');

        if ($request->ajax()) {
            $unites = Unite::query();

            return DataTables::of($unites)
                ->addIndexColumn()
                ->editColumn('created_at', fn($row) => $row->created_at ? Carbon::parse($row->created_at)->format('d-m-Y') : '-')
                ->addColumn('actions', function ($row) {
                    $view   = '<button type="button" class="btn btn-sm btn-outline-primary view" data-id="'.$row->id.'" title="Détails"><i class="fa fa-eye"></i></button>';
                    // On garde data-nom pour pouvoir modifier instantanément côté JS si besoin
                    $edit   = '<button type="button" class="btn btn-sm btn-outline-info edit" data-id="'.$row->id.'" data-nom="'.$row->nom.'" title="Modifier"><i class="fa fa-pencil-alt"></i></button>';
                    $delete = '<button type="button" class="btn btn-sm btn-outline-danger delete" data-id="'.$row->id.'" title="Supprimer"><i class="fa fa-trash"></i></button>';
                    
                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $view . $edit . $delete . '</div>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('application.unite.index');
    }

    /**
     * Enregistrer une nouvelle unité
     */
    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('stock.unites'), 403, 'Accès non autorisé.');

        $request->validate([
            'nom' => 'required|string|unique:unites,nom|max:255',
        ]);

        $unite = Unite::create(['nom' => $request->nom]);

        return response()->json([
            'message' => 'Unité ajoutée avec succès', 
            'unite' => $unite
        ]);
    }

    /**
     * Récupérer les données d'une unité (via AJAX pour la modification/détails)
     */
    public function show($id) 
    {
        abort_unless(Auth::user()->can('stock.unites'), 403, 'Accès non autorisé.');

        $unite = Unite::findOrFail($id);
        
        return response()->json($unite);
    }

    /**
     * Formulaire de modification (Optionnel si vous utilisez la route show, mais requis par Route::resource)
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

        $request->validate([
            'nom' => 'required|string|unique:unites,nom,' . $unite->id,
        ]);

        $unite->update(['nom' => $request->nom]);

        return response()->json([
            'message' => 'Unité mise à jour avec succès'
        ]);
    }

    /**
     * Supprimer une unité
     */
    public function destroy($id)
    {
        abort_unless(Auth::user()->can('stock.unites'), 403, 'Accès non autorisé.');

        $unite = Unite::findOrFail($id);
        $unite->delete();

        return response()->json([
            'message' => 'Unité supprimée avec succès'
        ]);
    }
}