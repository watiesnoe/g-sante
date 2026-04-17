<?php

namespace App\Http\Controllers;

use App\Models\Famille;
use App\Models\Medicament;
use App\Models\Unite;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class MedicamentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Medicament::with(['unite','famille']);

            return DataTables::of($query)
                ->addColumn('unite', function($m) {
                    return $m->unite?->nom ?? '-';
                })
                ->addColumn('famille', function($m) {
                    return $m->famille?->nom ?? '-';
                })
                ->addColumn('actions', function($m) {
                    $edit = '<a href="'.route('medicaments.edit', $m->id).'" class="btn-sm" title="Modifier"><i class="fa fa-pencil-alt text-info"></i></a> ';
                    $delete = '<form action="'.route('medicaments.destroy', $m->id).'" method="POST" class="d-inline m-0 p-0" onsubmit="return confirm(\'Supprimer ?\');">'.csrf_field().method_field("DELETE").'<button type="submit" class="btn-sm border-0 bg-transparent" title="Supprimer"><i class="fa fa-trash text-danger"></i></button></form>';
                    return $edit.$delete;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('application.medicament.index');
    }

    public function create()
    {
        $medicaments = Medicament::with(['unite','famille'])->get(); // eager loading
        $unites = Unite::all();    // pour alimenter un <select>
        $familles = Famille::all();

        return view('application.medicament.create', compact('medicaments','unites','familles'));

    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'=>'required|string|max:255',
            'description'=>'nullable|string',
            'stock'=>'required|integer|min:0',
            'stock_min'=>'required|integer|min:0',
            'prix_achat'=>'required|numeric|min:0',
            'prix_vente'=>'required|numeric|min:0',
        ]);

        Medicament::create($request->all());
        return redirect()->route('medicaments.index')->with('success','Médicament ajouté !');
    }

    public function edit(Medicament $medicament)
    {
        $unites = Unite::all();    // pour alimenter le <select>
        $familles = Famille::all();

        // On passe bien $medicament au singulier
        return view('application.medicament.create', compact('medicament','unites','familles'));
    }

    public function update(Request $request, Medicament $medicament)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'stock' => 'required|integer|min:0',
            'stock_min' => 'required|integer|min:0',
            'prix_achat' => 'required|numeric|min:0',
            'prix_vente' => 'required|numeric|min:0',
            'unite_id' => 'required|exists:unites,id',
            'famille_id' => 'required|exists:familles,id',
        ]);

        // Mise à jour avec uniquement les champs validés
        $medicament->update($validated);

        return redirect()->route('medicaments.index')->with('success', 'Médicament mis à jour !');
    }


    public function destroy(Medicament $medicament)
    {
        $medicament->delete();
        return redirect()->route('medicaments.index')->with('success','Médicament supprimé !');
    }
}
