<?php

namespace App\Http\Controllers;

use App\Models\Lit;
use App\Models\Salle;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class LitController extends Controller
{
    // Liste des lits
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $lits = Lit::with('salle')->select('lits.*');

            return DataTables::of($lits)
                ->addIndexColumn()
                ->addColumn('salle', function ($row) {
                    return $row->salle->nom ?? '-';
                })
                ->addColumn('actions', function ($row) {
                    $editUrl = route('lits.edit', $row->id);
                    $deleteUrl = route('lits.destroy', $row->id);
                    return '
                        <a href="'.$editUrl.'" class="btn btn-sm btn-warning">✏️</a>
                        <button data-url="'.$deleteUrl.'" class="btn btn-sm btn-danger btn-delete">🗑️</button>
                    ';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('application.lit.index');
    }
    // Formulaire de création
    public function create()
    {
        $salles = Salle::all();
        return view('application.lit.create', ['salles' => $salles]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'numero' => 'required|string|unique:lits,numero',
            'salle_id' => 'required|exists:salles,id',
            'statut' => 'required|in:Libre,Occupé,Maintenance',
        ]);

        $lit = Lit::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Lit ajouté avec succès ✨',
            'data' => $lit
        ], 201);
    }

    public function edit(Lit $lit)
    {
        $salles = Salle::all();
        return view('application.lit.create', ['lit' => $lit, 'salles' => $salles]);
    }

    public function update(Request $request,Lit $lit)
    {
        $request->validate([
            'numero' => 'required|string|unique:lits,numero,' . $lit->id,
            'salle_id' => 'required|exists:salles,id',
            'statut' => 'required|in:Libre,Occupé,Maintenance',
        ]);

        $lit->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Lit mis à jour avec succès ✨',
            'data' => $lit
        ], 200);
    }
}
