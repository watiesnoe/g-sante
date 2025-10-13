<?php

namespace App\Http\Controllers;

use App\Models\Suivi;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class SuiviController extends Controller
{
    // Affiche le formulaire de création depuis une consultation


    public function index(Request $request)
    {
        if ($request->ajax()) {
            $suivis = Suivi::with(['patient','medecin','consultation'])->select('suivis.*');

            return DataTables::of($suivis)
                ->addColumn('patient', fn($s) => optional($s->patient)->nom . ' ' . optional($s->patient)->prenom)
                ->addColumn('medecin', fn($s) => optional($s->medecin)->name)
                ->addColumn('consultation', fn($s) => $s->consultation ? "Consultation #{$s->consultation->id}" : '-')
                ->addColumn('date_heure', fn($s) => $s->date_heure ? \Carbon\Carbon::parse($s->date_heure)->format('d/m/Y H:i') : '-')
                ->addColumn('motif', fn($s) => $s->motif ?? '-')
                ->addColumn('resultat', fn($s) => $s->resultat ?? '-')
                ->addColumn('statut', fn($s) => ucfirst($s->statut))
                ->addColumn('actions', function($s){
                    return '
                <div class="dropdown">
                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">👁️ Voir</a></li>
                        <li><a class="dropdown-item" href="#">✏️ Modifier</a></li>
                        <li>
                            <button data-url="'.route('suivis.destroy',$s->id).'" class="dropdown-item text-danger btn-delete">🗑️ Supprimer</button>
                        </li>
                    </ul>
                </div>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('application.suiviconsultation.index');
    }
    public function create($consultation_id)
    {
        $consultation = Consultation::with(['patient', 'medecin'])->findOrFail($consultation_id);

        return view('application.suiviconsultation.create', [
            'consultation' => $consultation,
            'patient' => $consultation->patient,
            'medecin' => $consultation->medecin,
        ]);
    }

    // Stocke le suivi créé depuis une consultation
   public function store(Request $request)
{
    $consultation = Consultation::findOrFail($request->consultation_id);

    $request->validate([
        'date_heure' => 'required|date',
        'motif'      => 'nullable|string|max:255',
        'resultat'   => 'required|string',
        'statut'     => 'required|in:prévu,réalisé,annulé',
    ]);

    $suivi = Suivi::create([
        'consultation_id' => $consultation->id,
        'patient_id'      => $consultation->patient_id,
        'medecin_id'      => $consultation->medecin_id,
        'date_heure'      => $request->date_heure,
        'motif'           => $request->motif,
        'resultat'        => $request->resultat,
        'statut'          => $request->statut,
    ]);

    return response()->json([
        'success'         => true,
        'message'         => 'Suivi ajouté avec succès ✅',
        'consultation_id' => $consultation->id,
        'suivi_id'        => $suivi->id, // optionnel si tu veux l’utiliser après
    ]);
}

}
