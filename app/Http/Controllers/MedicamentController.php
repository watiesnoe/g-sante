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

            if ($request->filled('famille_id')) {
                $query->where('famille_id', $request->famille_id);
            }

            return DataTables::of($query)
                ->addColumn('checkbox', function($m) {
                    return '<input type="checkbox" class="form-check-input medicament-checkbox" value="'.$m->uuid.'">';
                })
                ->addColumn('unite', function($m) {
                    return $m->unite?->nom ?? '-';
                })
                ->addColumn('famille', function($m) {
                    return $m->famille?->nom ?? '-';
                })
                ->addColumn('actions', function($m) {
                    $show   = '<a href="'.route('medicaments.show', $m).'" class="btn btn-sm btn-outline-primary" title="Détails"><i class="fa fa-eye"></i></a>';
                    $edit   = '<a href="'.route('medicaments.edit', $m).'" class="btn btn-sm btn-outline-info" title="Modifier"><i class="fa fa-pencil-alt"></i></a>';
                    $delete = '<form action="'.route('medicaments.destroy', $m).'" method="POST" class="d-inline" onsubmit="return confirm(\'Supprimer ce médicament ?\');">'.csrf_field().method_field('DELETE').'<button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="fa fa-trash"></i></button></form>';
                    
                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $show . $edit . $delete . '</div>';
                })
                ->rawColumns(['actions', 'checkbox'])
                ->make(true);
        }

        $familles = Famille::orderBy('nom')->get();
        
        // Stats
        $totalMolecules = Medicament::count();
        $stockCritique = Medicament::whereColumn('stock', '<=', 'stock_min')->count();

        return view('application.medicament.index', compact('familles', 'totalMolecules', 'stockCritique'));
    }

    public function show(Medicament $medicament)
    {
        $medicament->load(['unite', 'famille', 'protocoles.maladie']);
        return view('application.medicament.show', compact('medicament'));
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
