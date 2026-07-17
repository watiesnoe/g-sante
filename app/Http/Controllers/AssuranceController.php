<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assurance;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;

class AssuranceController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('parametres.assurances'), 403, 'Accès non autorisé : vous n\'avez pas accès à la gestion des assurances.');

        if ($request->ajax()) {
            $assurances = Assurance::query();
            return DataTables::of($assurances)
                ->addIndexColumn()
                ->addColumn('actions', function($row) {
                    $view   = '<a href="'.route('assurances.show', $row->uuid).'" class="btn btn-sm btn-outline-primary" title="Détails"><i class="fa fa-eye"></i></a>';
                    $edit   = '<a href="'.route('assurances.edit', $row->uuid).'" class="btn btn-sm btn-outline-info" title="Modifier"><i class="fa fa-pencil-alt"></i></a>';
                    $delete = '<button type="button" class="btn btn-sm btn-outline-danger delete" data-id="'.$row->uuid.'" title="Supprimer"><i class="fa fa-trash"></i></button>';
                    
                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $view . $edit . $delete . '</div>';
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
            'telephone' => 'nullable|string|max:50',
            'adresse' => 'nullable|string|max:255',
        ]);
        
        $assurance = Assurance::create($request->all());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Assurance ajoutée avec succès !',
                'data' => $assurance
            ]);
        }

        return redirect()->route('assurances.index')->with('success', 'Assurance ajoutée avec succès !');
    }

    public function show(Request $request, Assurance $assurance)
    {
        abort_unless(Auth::user()->can('parametres.assurances'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les assurances.');

        if ($request->ajax()) {
            return response()->json($assurance);
        }
        return view('application.assurance.show', compact('assurance'));
    }

    public function edit(Assurance $assurance)
    {
        return view('application.assurance.create', compact('assurance'));
    }

    public function update(Request $request, Assurance $assurance)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'taux' => 'required|integer|min:0|max:100',
            'telephone' => 'nullable|string|max:50',
            'adresse' => 'nullable|string|max:255',
        ]);

        $assurance->update($request->all());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Assurance mise à jour avec succès !',
                'data' => $assurance
            ]);
        }

        return redirect()->route('assurances.index')->with('success', 'Assurance mise à jour avec succès !');
    }

    public function destroy(Assurance $assurance)
    {
        $assurance->delete();

        return response()->json(['success' => true, 'message' => 'Assurance supprimée avec succès !']);
    }
}
