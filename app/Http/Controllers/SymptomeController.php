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
                    $editBtn = '<button class="btn btn-info btn-sm edit" data-id="'.$row->id.'">Modifier</button> ';
                    $deleteBtn = '<button class="btn btn-danger btn-sm delete" data-id="'.$row->id.'">Supprimer</button>';
                    return $editBtn.$deleteBtn;
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
}
