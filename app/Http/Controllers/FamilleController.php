<?php
namespace App\Http\Controllers;

use App\Models\Famille;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class FamilleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $famille= Famille::query(); // ✅ Utiliser query()

            return DataTables::of($famille)
                ->addIndexColumn()
                ->addColumn('actions', function ($row) {
                    return '
                    <button class="btn btn-sm btn-warning editFamille"
                        data-id="'.$row->id.'"
                        data-nom="'.$row->nom.'">Modifier</button>
                    <button class="btn btn-sm btn-danger deleteFamille"
                        data-id="'.$row->id.'">Supprimer</button>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
        return view('application.famille.index');
    }

    // Enregistrer une unité
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|unique:unites,nom|max:255',
        ]);

        $famille= Famille::create(['nom' => $request->nom]);

        return response()->json(['success' => 'Unité ajoutée avec succès', 'famille' => $famille]);
    }

    // Mettre à jour une unité
    public function update(Request $request, $id)
    {
        $famille = Famille::findOrFail($id);

        $request->validate([
            'nom' => 'required|string|unique:unites,nom,' . $famille->id,
        ]);

        $famille->update(['nom' => $request->nom]);

        return response()->json(['success' => 'Unité mise à jour avec succès']);
    }

    // Supprimer une unité
    public function destroy($id)
    {
        $famille = Famille::findOrFail($id);
        $famille->delete();

        return response()->json(['success' => 'Unité supprimée avec succès']);
    }
}
