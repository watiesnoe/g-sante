<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class FournisseurController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('stock.fournisseurs'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les fournisseurs.');

        // Si c'est une requête AJAX pour DataTable
        if ($request->ajax()) {
            $fournisseurs = Fournisseur::select(['id', 'uuid', 'nom', 'contact', 'adresse']);
            return Datatables::of($fournisseurs)
                ->addColumn('actions', function($row) {
                    $view   = '<button type="button" class="btn btn-sm btn-outline-primary view" data-id="'.$row->id.'" title="Détails"><i class="fa fa-eye"></i></button>';
                    $edit   = '<button type="button" class="btn btn-sm btn-outline-info edit" data-id="'.$row->id.'" title="Modifier"><i class="fa fa-pencil-alt"></i></button>';
                    $delete = '<button type="button" class="btn btn-sm btn-outline-danger delete" data-id="'.$row->id.'" title="Supprimer"><i class="fa fa-trash"></i></button>';
                    
                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $view . $edit . $delete . '</div>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        // Sinon, on retourne la vue normale
        return view('application.fournisseur.index');
    }
    public function create()
    {
        abort_unless(Auth::user()->can('stock.fournisseurs'), 403, 'Accès non autorisé.');

        return view('application.fournisseur.create');
    }

   public function store(Request $request)
    {
        abort_unless(Auth::user()->can('stock.fournisseurs'), 403, 'Accès non autorisé.');

        $request->validate([
            'nom' => 'required|string|max:255',
            'contact' => 'nullable|string|max:255',
            'adresse' => 'nullable|string|max:255',
        ]);

        $fournisseur = Fournisseur::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Fournisseur ajouté avec succès ✅',
            'data' => $fournisseur
        ]);
    }

    public function edit(Fournisseur $fournisseur)
    {
        abort_unless(Auth::user()->can('stock.fournisseurs'), 403, 'Accès non autorisé.');

        return view('application.fournisseur.create', compact('fournisseur'));
    }

    public function update(Request $request, Fournisseur $fournisseur)
    {
        abort_unless(Auth::user()->can('stock.fournisseurs'), 403, 'Accès non autorisé.');

        $request->validate([
            'nom' => 'required|string|max:255',
            'contact' => 'nullable|string|max:255',
            'adresse' => 'nullable|string|max:255',
        ]);

        $fournisseur->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Fournisseur modifié avec succès ✏️'
        ]);
    }
    public function destroy(Fournisseur $fournisseur)
    {
        abort_unless(Auth::user()->can('stock.fournisseurs'), 403, 'Accès non autorisé.');

        $fournisseur->delete();
        return response()->json(['success' => true, 'message' => 'Fournisseur supprimé avec succès !']);
    }

    public function show($id) {
        abort_unless(Auth::user()->can('stock.fournisseurs'), 403, 'Accès non autorisé.');

        $fournisseur = Fournisseur::findOrFail($id);
        return response()->json($fournisseur);
    }
}
