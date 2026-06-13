<?php

namespace App\Http\Controllers;

use App\Models\Famille;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables; // ✅ Utilisation de la Facade standard

class FamilleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('stock.familles'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les familles.');

        if ($request->ajax()) {
            $famille = Famille::query(); // ✅ Utiliser query()

            return DataTables::of($famille)
                ->addIndexColumn()
                ->editColumn('created_at', fn($row) => $row->created_at ? Carbon::parse($row->created_at)->format('d-m-Y') : '-') // ✅ Ajout du formatage de la date requis pour le tableau HTML
                ->addColumn('actions', function ($row) {
                    $view   = '<button type="button" class="btn btn-sm btn-outline-primary view" data-id="'.$row->id.'" title="Détails"><i class="fa fa-eye"></i></button>';
                    $edit   = '<button type="button" class="btn btn-sm btn-outline-info edit" data-id="'.$row->id.'" data-nom="'.$row->nom.'" title="Modifier"><i class="fa fa-pencil-alt"></i></button>';
                    $delete = '<button type="button" class="btn btn-sm btn-outline-danger delete" data-id="'.$row->id.'" title="Supprimer"><i class="fa fa-trash"></i></button>';
                    
                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $view . $edit . $delete . '</div>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
        return view('application.famille.index');
    }

    /**
     * Enregistrer une famille
     */
    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('stock.familles'), 403, 'Accès non autorisé.');

        $request->validate([
            'nom' => 'required|string|unique:familles,nom|max:255',
        ]);

        $famille = Famille::create(['nom' => $request->nom]);

        return response()->json([
            'message' => 'Famille ajoutée avec succès', 
            'famille' => $famille
        ]); // ✅ Remplacement de 'success' par 'message'
    }

    /**
     * Afficher les détails d'une famille (.view)
     */
    public function show($id) {
        abort_unless(Auth::user()->can('stock.familles'), 403, 'Accès non autorisé.');

        $famille = Famille::findOrFail($id);
        return response()->json($famille);
    }

    /**
     * Récupérer les données pour la modification (.edit)
     */
    public function edit($id) {
        abort_unless(Auth::user()->can('stock.familles'), 403, 'Accès non autorisé.');

        $famille = Famille::findOrFail($id);
        return response()->json($famille); // ✅ Ajout de la méthode edit manquante
    }

    /**
     * Mettre à jour une famille
     */
    public function update(Request $request, $id)
    {
        abort_unless(Auth::user()->can('stock.familles'), 403, 'Accès non autorisé.');

        $famille = Famille::findOrFail($id);

        $request->validate([
            'nom' => 'required|string|unique:familles,nom,' . $famille->id,
        ]);

        $famille->update(['nom' => $request->nom]);

        return response()->json([
            'message' => 'Famille mise à jour avec succès'
        ]); // ✅ Remplacement de 'success' par 'message'
    }

    /**
     * Supprimer une famille
     */
    public function destroy($id)
    {
        abort_unless(Auth::user()->can('stock.familles'), 403, 'Accès non autorisé.');

        $famille = Famille::findOrFail($id);
        $famille->delete();

        return response()->json([
            'message' => 'Famille supprimée avec succès'
        ]); // ✅ Remplacement de 'success' par 'message'
    }
}