<?php

namespace App\Http\Controllers;

use App\Models\Symptome;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class SymptomeController extends Controller
{
    // Index avec Datatable
    public function index(Request $request)
    {
        if($request->ajax()){
            $symptomes = Symptome::all();

            return Datatables::of($symptomes)
                ->addIndexColumn()
                ->addColumn('actions', function($row){
                    $view   = '<a href="'.route('symptomes.show', $row->id).'" class="btn btn-sm btn-outline-primary" title="Détails"><i class="fa fa-eye"></i></a>';
                    $edit   = '<button type="button" class="btn btn-sm btn-outline-info edit" data-id="'.$row->id.'" title="Modifier"><i class="fa fa-pencil-alt"></i></button>';
                    $delete = '<button type="button" class="btn btn-sm btn-outline-danger delete" data-id="'.$row->id.'" title="Supprimer"><i class="fa fa-trash"></i></button>';
                    
                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $view . $edit . $delete . '</div>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('application.symptome.index');
    }

    // Store
    public function store(Request $request)
    {
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
        $symptome->delete();
        return response()->json(['message' => 'Symptôme supprimé avec succès !']);
    }

    // Edit (pour récupérer les données AJAX)
    public function edit(Symptome $symptome)
    {
        return response()->json($symptome);
    }

    public function show($id) {
        $symptome = Symptome::with(['maladies.protocole'])->findOrFail($id);
        return view('application.symptome.show', compact('symptome'));
    }
}
