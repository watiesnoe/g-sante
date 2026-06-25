<?php

namespace App\Http\Controllers;

use App\Models\PrescriptionExamen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrescriptionExamenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('examens.view'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les examens.');

        if ($request->ajax()) {
            $year = session('exercice_year', date('Y'));
            $user = Auth::user();
            $examens = PrescriptionExamen::with('consultation.patient')
                ->whereYear('created_at', $year)
                ->where('statut', '!=', 'realise') // ✅ exclure les réalisés
                ->when(!$user->hasRole(['super_admin', 'superadmin', 'admin']), function ($query) use ($user) {
                    $query->whereHas('consultation', function ($q) use ($user) {
                        $q->where('medecin_id', $user->id);
                    });
                })
                ->latest();

    return datatables()->of($examens)
        ->addColumn('patient', function($row){
            return $row->consultation?->patient?->nom .' '. $row->consultation?->patient?->prenom;
        })
        ->addColumn('examen', function($row){
            return $row->examen;
        })
        ->addColumn('actions', function($row){
            $user = Auth::user();
            $html = '';

            if ($user->can('examens.results')) {
                $html .= '<a href="'.route('reponse.create', $row->id).'" class="btn btn-sm btn-outline-primary" title="Réponse"><i class="fa fa-edit"></i> Réponse</a>';
            }
            if ($user->can('examens.delete')) {
                $html .= '<button type="button" data-url="'.route('examens.destroy', $row->id).'" class="btn btn-sm btn-outline-danger btn-delete" title="Supprimer"><i class="fa fa-trash"></i></button>';
            }

            return '<div class="d-flex align-items-center justify-content-center gap-1">' . $html . '</div>';
        })
        ->rawColumns(['actions'])
        ->make(true);
}

        return view('application.examen.listeprescription');
    }


    // Créer
    public function create()
    {
        abort_unless(Auth::user()->can('examens.create'), 403, 'Accès non autorisé : vous n\'avez pas la permission de prescrire un examen.');

        return view('application.examens.create');
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('examens.create'), 403, 'Accès non autorisé : vous n\'avez pas la permission de prescrire un examen.');

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
        abort_unless(Auth::user()->can('examens.delete'), 403, 'Accès non autorisé : vous n\'avez pas la permission de supprimer un examen.');

        $examen->delete();
        return response()->json(['success' => true]);
    }
}
