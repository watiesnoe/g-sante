<?php

namespace App\Http\Controllers;

use App\Models\Unite;
use Carbon\Carbon;
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
                ->editColumn('created_at', fn($row) => $row->created_at ? Carbon::parse($row->created_at)->format('d-m-Y') : '-')
                ->addColumn('actions', function ($row) {
                    $viewBtn = '<span class="btn-sm view" data-id="'.$row->id.'" title="Détails"><i class="fa fa-eye text-primary"></i></span> ';
                    $editBtn = '<span class="btn-sm edit" data-id="'.$row->id.'" data-nom="'.$row->nom.'" title="Modifier"><i class="fa fa-pencil-alt text-info"></i></span> ';
                    $deleteBtn = '<span class="btn-sm delete" data-id="'.$row->id.'" title="Supprimer"><i class="fa fa-trash text-danger"></i></span>';
                    return $viewBtn . $editBtn . $deleteBtn;
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

    public function show($id) {
        $unite = Unite::findOrFail($id);
        return response()->json($unite);
    }
}
