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
            $fournisseurs = Fournisseur::select(['id', 'nom', 'contact', 'adresse']);
            return Datatables::of($fournisseurs)
                ->addColumn('actions', function($fournisseur) {
                    return '
                    <a href="'.route('fournisseurs.edit', $fournisseur->id).'" class="btn btn-sm btn-warning">✏️</a>
                    <form action="'.route('fournisseurs.destroy', $fournisseur->id).'" method="POST" class="d-inline">
                        '.csrf_field().method_field("DELETE").'
                        <button class="btn btn-sm btn-danger">🗑️</button>
                    </form>
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
            'nom'=>'required|string|max:255',
            'contact'=>'nullable|string|max:255',
            'adresse'=>'nullable|string|max:255',
        ]);

        Fournisseur::create($request->all());
        return redirect()->route('fournisseurs.index')->with('success','Fournisseur ajouté !');
    }

    public function edit(Fournisseur $fournisseur)
    {
        return view('application.fournisseur.create', compact('fournisseur'));
    }

    public function update(Request $request, Fournisseur $fournisseur)
    {
        $request->validate([
            'nom'=>'required|string|max:255',
            'contact'=>'nullable|string|max:255',
            'adresse'=>'nullable|string|max:255',
        ]);

        $fournisseur->update($request->all());
        return redirect()->route('fournisseurs.index')->with('success','Fournisseur mis à jour !');
    }

    public function destroy(Fournisseur $fournisseur)
    {
        $fournisseur->delete();
        return redirect()->route('fournisseurs.index')->with('success','Fournisseur supprimé !');
    }
}
