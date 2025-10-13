<?php

namespace App\Http\Controllers;

use App\Models\Examen;
use App\Models\ServiceMedical;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ExamenController extends Controller
{
    public function index(Request $request)
    {
        if($request->ajax()) {
            $examens = Examen::with('serviceMedical')->select('examens.*');
            return DataTables::of($examens)
                ->addIndexColumn()
                ->addColumn('service', fn($row) => $row->serviceMedical->nom ?? '-')
                ->addColumn('actions', function($row){
                    return '<a href="'.route("examens.edit",$row->id).'" class="btn btn-sm btn-warning">✏️</a>
                            <button data-url="'.route("examens.destroy",$row->id).'" class="btn btn-sm btn-danger btn-delete">🗑️</button>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
        return view('application.examen.index');
    }

    public function create()
    {
        $services = ServiceMedical::all();
        return view('application.examen.create', compact('services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'service_medical_id' => 'required|exists:service_medicals,id',
            'description' => 'nullable|string',
            'prix' => 'nullable|numeric'
        ]);

        $examen = Examen::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Examen ajouté avec succès ✨',
            'data' => $examen
        ]);
    }

    public function edit(Examen $examen)
    {
        $services = ServiceMedical::all();
        return view('application.examen.create', compact('examen','services'));
    }

    public function update(Request $request, Examen $examen)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'service_medical_id' => 'required|exists:service_medicals,id',
            'description' => 'nullable|string',
            'prix' => 'nullable|numeric'
        ]);

        $examen->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Examen mis à jour avec succès ✨',
            'data' => $examen
        ]);
    }

    public function destroy(Examen $examen)
    {
        $examen->delete();
        return response()->json([
            'success' => true,
            'message' => 'Examen supprimé ✨'
        ]);
    }
}

