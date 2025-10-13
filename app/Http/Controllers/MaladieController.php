<?php

namespace App\Http\Controllers;

use App\Models\Maladie;
use App\Models\Symptome;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class MaladieController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request) {
        if($request->ajax()) {
            $maladies = Maladie::with('symptomes')->select('maladies.*');

            return DataTables::of($maladies)
                ->addIndexColumn()
                ->addColumn('symptomes', function($row){
                    return $row->symptomes->pluck('nom')->implode(', ');
                })
                ->addColumn('actions', function($row){
                    return '<button class="btn btn-sm btn-danger delete" data-id="'.$row->id.'">Supprimer</button>';
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

    /**
     * Remove the specified resource from storage.
     */
    public function show($id) {
        $maladie = Maladie::with('symptomes')->findOrFail($id);
        return response()->json($maladie);
    }
}
