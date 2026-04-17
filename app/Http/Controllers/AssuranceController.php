<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Assurance;
use Yajra\DataTables\DataTables;

class AssuranceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $assurances = Assurance::query();
            return DataTables::of($assurances)
                ->addIndexColumn()
                ->addColumn('actions', function($row) {
                    return '
                        <a href="'.route('assurances.show', $row->id).'" class="btn btn-sm btn-primary" title="Détails"><i class="fa fa-eye"></i></a>
                        <a href="'.route('assurances.edit', $row->id).'" class="btn btn-sm btn-info" title="Modifier"><i class="fa fa-pencil-alt"></i></a>
                        <button class="btn btn-sm btn-danger delete" data-id="'.$row->id.'" title="Supprimer"><i class="fa fa-trash"></i></button>
                    ';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('application.assurance.index');
    }

    public function create()
    {
        return view('application.assurance.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'taux' => 'required|integer|min:0|max:100',
        ]);
        // dd($request->all());
        Assurance::create($request->all());

        return redirect()->route('assurances.index')->with('success', 'Assurance ajoutée avec succès !');
    }

    public function show(Request $request, $id)
    {
        $assurance = Assurance::findOrFail($id);
        if ($request->ajax()) {
            return response()->json($assurance);
        }
        return view('application.assurance.show', compact('assurance'));
    }

    public function edit($id)
    {
        $assurance = Assurance::findOrFail($id);
        return view('application.assurance.create', compact('assurance'));
    }

    public function update(Request $request, $id)
    {
        $assurance = Assurance::findOrFail($id);

        $request->validate([
            'nom' => 'required|string|max:255',
            'taux' => 'required|integer|min:0|max:100',
        ]);

        $assurance->update($request->all());

        return redirect()->route('assurances.index')->with('success', 'Assurance mise à jour avec succès !');
    }

    public function destroy($id)
    {
        $assurance = Assurance::findOrFail($id);
        $assurance->delete();

        return response()->json(['success' => true, 'message' => 'Assurance supprimée avec succès !']);
    }
}
