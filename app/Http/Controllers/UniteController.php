<?php

namespace App\Http\Controllers;

use App\Models\Unite;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class UniteController extends Controller
{
    // Liste des unités avec DataTables
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $unites = Unite::query(); // ✅ Utiliser query()

            return DataTables::of($unites)
                ->addIndexColumn()
                ->addColumn('actions', function ($row) {
                    return '
                    <button class="btn btn-sm btn-warning editUnite"
                        data-id="'.$row->id.'"
                        data-nom="'.$row->nom.'">Modifier</button>
                    <button class="btn btn-sm btn-danger deleteUnite"
                        data-id="'.$row->id.'">Supprimer</button>
                ';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('application.unite.index');
    }

    // Enregistrer une unité
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|unique:unites,nom|max:255',
        ]);

        $unite = Unite::create(['nom' => $request->nom]);

        return response()->json(['success' => 'Unité ajoutée avec succès', 'unite' => $unite]);
    }

    // Mettre à jour une unité
    public function update(Request $request, $id)
    {
        $unite = Unite::findOrFail($id);

        $request->validate([
            'nom' => 'required|string|unique:unites,nom,' . $unite->id,
        ]);

        $unite->update(['nom' => $request->nom]);

        return response()->json(['success' => 'Unité mise à jour avec succès']);
    }

    // Supprimer une unité
    public function destroy($id)
    {
        $unite = Unite::findOrFail($id);
        $unite->delete();

        return response()->json(['success' => 'Unité supprimée avec succès']);
    }
}
