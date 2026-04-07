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
                    <button class="btn btn-sm btn-primary view"
                        data-id="'.$row->id.'" title="Détails"><i class="fa fa-eye"></i></button>
                    <button class="btn btn-sm btn-info edit"
                        data-id="'.$row->id.'"
                        data-nom="'.$row->nom.'" title="Modifier"><i class="fa fa-pencil-alt"></i></button>
                    <button class="btn btn-sm btn-danger delete"
                        data-id="'.$row->id.'" title="Supprimer"><i class="fa fa-trash"></i></button>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
        return view('application.famille.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|unique:familles,nom|max:255',
        ]);

        $famille= Famille::create(['nom' => $request->nom]);

        return response()->json(['success' => 'Famille ajoutée avec succès', 'famille' => $famille]);
    }

    // Mettre à jour une famille
    public function update(Request $request, $id)
    {
        $famille = Famille::findOrFail($id);

        $request->validate([
            'nom' => 'required|string|unique:familles,nom,' . $famille->id,
        ]);

        $famille->update(['nom' => $request->nom]);

        return response()->json(['success' => 'Famille mise à jour avec succès']);
    }

    // Supprimer une famille
    public function destroy($id)
    {
        $famille = Famille::findOrFail($id);
        $famille->delete();

        return response()->json(['success' => 'Famille supprimée avec succès']);
    }

    public function show($id) {
        $famille = Famille::findOrFail($id);
        return response()->json($famille);
    }
}
