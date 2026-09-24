<?php

namespace App\Http\Controllers;

use App\Models\Symptome;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;

class SymptomeController extends Controller
{
    // Index avec Datatable
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('parametres.symptomes'), 403, 'Accès non autorisé : vous n\'avez pas accès à la gestion des symptômes.');

        if($request->ajax()){
            $symptomes = Symptome::all();

            return Datatables::of($symptomes)
                ->addIndexColumn()
                ->addColumn('checkbox', function($row){
                    return '<input type="checkbox" class="symptome-checkbox form-check-input" value="'.$row->uuid.'">';
                })
                ->addColumn('actions', function($row){
                    $view   = '<a href="'.route('symptomes.show', $row->uuid).'" class="btn btn-sm btn-outline-primary" title="Détails"><i class="fa fa-eye"></i></a>';
                    $edit   = '<button type="button" class="btn btn-sm btn-outline-info edit" data-id="'.$row->uuid.'" title="Modifier"><i class="fa fa-pencil-alt"></i></button>';
                    $delete = '<button type="button" class="btn btn-sm btn-outline-danger delete" data-id="'.$row->uuid.'" title="Supprimer"><i class="fa fa-trash"></i></button>';
                    
                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $view . $edit . $delete . '</div>';
                })
                ->rawColumns(['checkbox', 'actions'])
                ->make(true);
        }

        return view('application.symptome.index');
    }

    // Store
    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('parametres.symptomes'), 403, 'Accès non autorisé.');

        $request->validate([
            'nom' => 'required|unique:symptomes',
            'description' => 'nullable|string'
        ]);

        Symptome::create($request->only('nom','description'));

        return response()->json(['message' => 'Symptôme créé avec succès !']);
    }

    // Update
    public function update(Request $request, Symptome $symptome)
    {
        abort_unless(Auth::user()->can('parametres.symptomes'), 403, 'Accès non autorisé.');

        $request->validate([
            'nom' => 'required|unique:symptomes,nom,'.$symptome->id,
            'description' => 'nullable|string'
        ]);

        $symptome->update($request->only('nom','description'));

        return response()->json(['message' => 'Symptôme modifié avec succès !']);
    }

    // Delete
    public function destroy(Symptome $symptome)
    {
        abort_unless(Auth::user()->can('parametres.symptomes'), 403, 'Accès non autorisé.');

        $symptome->delete();
        return response()->json(['message' => 'Symptôme supprimé avec succès !']);
    }

    // Delete Bulk
    public function destroyBulk(Request $request)
    {
        abort_unless(Auth::user()->can('parametres.symptomes'), 403, 'Accès non autorisé.');

        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'string|exists:symptomes,uuid',
        ]);

        $symptomes = Symptome::whereIn('uuid', $request->ids)->get();
        $count = $symptomes->count();

        foreach ($symptomes as $symptome) {
            $symptome->delete();
        }

        return response()->json([
            'message' => "$count symptôme(s) supprimé(s) avec succès.",
        ]);
    }

    // Edit (pour récupérer les données AJAX)
    public function edit(Symptome $symptome)
    {
        abort_unless(Auth::user()->can('parametres.symptomes'), 403, 'Accès non autorisé.');

        return response()->json($symptome);
    }

    public function show($id) {
        abort_unless(Auth::user()->can('parametres.symptomes'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les symptômes.');

        $symptome = Symptome::with(['maladies.protocole'])->findOrFail($id);
        return view('application.symptome.show', compact('symptome'));
    }
}
