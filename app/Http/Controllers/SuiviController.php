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
                ->addColumn('date_heure', fn($s) => $s->date_heure ? \Carbon\Carbon::parse($s->date_heure)->format('d-m-Y H:i') : '-')
                ->addColumn('motif', fn($s) => $s->motif ?? '-')
                ->addColumn('resultat', fn($s) => $s->resultat ?? '-')
                ->addColumn('statut', fn($s) => ucfirst($s->statut))
                ->addColumn('actions', function($s){
                    $view   = '<button type="button" class="btn btn-sm btn-outline-primary view" data-id="'.$s->id.'" title="Détails"><i class="fa fa-eye"></i></button>';
                    $edit   = '<button type="button" class="btn btn-sm btn-outline-info edit" data-id="'.$s->id.'" title="Modifier"><i class="fa fa-pencil-alt"></i></button>';
                    $delete = '<button type="button" class="btn btn-sm btn-outline-danger delete" data-id="'.$s->id.'" title="Supprimer"><i class="fa fa-trash"></i></button>';
                    
                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $view . $edit . $delete . '</div>';
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
        'motif'      => 'nullable|string|max:255',
        'resultat'   => 'required|string',
        'statut'     => 'required|in:prévu,réalisé,annulé',
    ]);

    $suivi = Suivi::create([
        'consultation_id' => $consultation->id,
        'patient_id'      => $consultation->patient_id,
        'medecin_id'      => $consultation->medecin_id,
        'date_heure'      => now(),
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
