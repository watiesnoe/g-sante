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
    $examens = PrescriptionExamen::with('consultation.patient')
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
            // Bouton pour accéder à la page de résultat / réponse
            $reponseBtn = '<a href="'.route('reponse.create', $row->id).'" class="btn btn-sm btn-primary me-1">
                             📝 Réponse
                           </a>';

            // Bouton supprimer
            $deleteBtn = '<button data-url="'.route('examens.destroy', $row->id).'" class="btn btn-sm btn-danger btn-delete">
                             🗑️ Supprimer
                          </button>';

            return $reponseBtn . $deleteBtn;
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
