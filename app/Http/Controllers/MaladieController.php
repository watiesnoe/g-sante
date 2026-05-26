<?php

namespace App\Http\Controllers;

use App\Models\Maladie;
use App\Models\Symptome;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;

class MaladieController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request) {
        abort_unless(Auth::user()->can('parametres.maladies'), 403, 'Accès non autorisé : vous n\'avez pas accès à la gestion des maladies.');

        if($request->ajax()) {
            $maladies = Maladie::with('symptomes')->select('maladies.*');

            return DataTables::of($maladies)
                ->addIndexColumn()
                ->addColumn('symptomes', function($row){
                    return $row->symptomes->pluck('nom')->implode(', ');
                })
                ->addColumn('actions', function($row){
                    $viewBtn   = '<button type="button" class="btn btn-sm btn-outline-primary view" data-id="'.$row->id.'" title="Détails"><i class="fa fa-eye"></i></button>';
                    $editBtn   = '<button type="button" class="btn btn-sm btn-outline-info edit" data-id="'.$row->id.'" title="Modifier"><i class="fa fa-pencil-alt"></i></button>';
                    $deleteBtn = '<button type="button" class="btn btn-sm btn-outline-danger delete" data-id="'.$row->id.'" title="Supprimer"><i class="fa fa-trash"></i></button>';
                    
                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $viewBtn . $editBtn . $deleteBtn . '</div>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        $symptomes = Symptome::all();
        return view('application.maladie.index', compact('symptomes'));
    }

    public function store(Request $request) {
        $request->validate([
            'nom' => 'required|unique:maladies',
            'symptomes' => 'array'
        ]);

        $maladie = Maladie::create([
            'nom' => $request->nom,
            'description' => $request->description
        ]);

        $maladie->symptomes()->sync($request->symptomes ?? []);

        return response()->json([
            'success' => true,
            'message' => 'Maladie créée avec succès !'
        ]);
    }

    public function destroy($id) {
        $maladie = Maladie::findOrFail($id);
        $maladie->symptomes()->detach();
        $maladie->delete();

        return response()->json(['success' => true, 'message' => 'Maladie supprimée !']);
    }

    public function update(Request $request, $id) {
        $request->validate([
            'nom' => 'required|unique:maladies,nom,'.$id,
            'symptomes' => 'array'
        ]);

        $maladie = Maladie::findOrFail($id);
        $maladie->update([
            'nom' => $request->nom,
            'description' => $request->description
        ]);

        $maladie->symptomes()->sync($request->symptomes ?? []);

        return response()->json([
            'success' => true,
            'message' => 'Maladie mise à jour avec succès !'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function show($id) {
        $maladie = Maladie::with('symptomes')->findOrFail($id);
        return response()->json($maladie);
    }
}
