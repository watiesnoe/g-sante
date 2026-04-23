<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class FournisseurController extends Controller
{
    public function index(Request $request)
    {
        // Si c'est une requête AJAX pour DataTable
        if ($request->ajax()) {
            $fournisseurs = Fournisseur::select(['id', 'uuid', 'nom', 'contact', 'adresse']);
            return Datatables::of($fournisseurs)
                ->addColumn('actions', function($row) {
                    return '
                    <button class="btn btn-sm btn-primary view" data-id="'.$row->id.'" title="Détails"><i class="fa fa-eye"></i></button>
                    <button class="btn btn-sm btn-info edit" data-id="'.$row->id.'" title="Modifier"><i class="fa fa-pencil-alt text-info"></i></button>
                    <button class="btn btn-sm btn-danger delete" data-id="'.$row->id.'" title="Supprimer"><i class="fa fa-trash text-danger"></i></button>
                ';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        // Sinon, on retourne la vue normale
        return view('application.fournisseur.index');
    }
    public function create()
    {
        return view('application.fournisseur.create');
    }

   public function store(Request $request)
    {
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
        return view('application.fournisseur.create', compact('fournisseur'));
    }

    public function update(Request $request, Fournisseur $fournisseur)
    {
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
        $fournisseur->delete();
        return response()->json(['success' => true, 'message' => 'Fournisseur supprimé avec succès !']);
    }

    public function show($id) {
        $fournisseur = Fournisseur::findOrFail($id);
        return response()->json($fournisseur);
    }
}
