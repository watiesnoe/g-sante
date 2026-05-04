<?php

namespace App\Http\Controllers;

use App\Models\PrescriptionExamen;
use Illuminate\Http\Request;

class PrescriptionExamenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $year = session('exercice_year', date('Y'));
            $examens = PrescriptionExamen::with('consultation.patient')
                ->whereYear('created_at', $year)
                ->where('statut', '!=', 'realise') // ✅ exclure les réalisés
                ->latest();

    return datatables()->of($examens)
        ->addColumn('patient', function($row){
            return $row->consultation?->patient?->nom .' '. $row->consultation?->patient?->prenom;
        })
        ->addColumn('examen', function($row){
            return $row->examen;
        })
        ->addColumn('actions', function($row){
            $reponse = '<a href="'.route('reponse.create', $row->id).'" class="btn btn-sm btn-outline-primary" title="Réponse"><i class="fa fa-edit"></i> Réponse</a>';
            $delete  = '<button type="button" data-url="'.route('examens.destroy', $row->id).'" class="btn btn-sm btn-outline-danger btn-delete" title="Supprimer"><i class="fa fa-trash"></i></button>';

            return '<div class="d-flex align-items-center justify-content-center gap-1">' . $reponse . $delete . '</div>';
        })
        ->rawColumns(['actions'])
        ->make(true);
}

        return view('application.examen.listeprescription');
    }


    // Créer
    public function create()
    {
        return view('application.examens.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'consultation_id' => 'required|exists:consultations,id',
            'examen' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        PrescriptionExamen::create($request->all());

        return redirect()->route('examens.index')->with('success', 'Examen prescrit avec succès.');
    }

    // Supprimer
    public function destroy(PrescriptionExamen $examen)
    {
        $examen->delete();
        return response()->json(['success' => true]);
    }
}
