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
                    $viewBtn = '<span class="  btn-sm view " data-id="'.$row->id.'" title="Détails"><i class="fa fa-eye text-primary"></i></span> ';
                    $editBtn = '<span class="  btn-sm edit" data-id="'.$row->id.'" title="Modifier"><i class="fa fa-pencil-alt text-info"></i></span> ';
                    $deleteBtn = '<span class="  btn-sm delete" data-id="'.$row->id.'" title="Supprimer"><i class="fa fa-trash text-danger"></i></span>';
                    return $viewBtn.$editBtn.$deleteBtn;
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
        $symptome = Symptome::findOrFail($id);
        return response()->json($symptome);
    }
}
