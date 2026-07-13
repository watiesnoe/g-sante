<?php

namespace App\Http\Controllers;

use App\Models\Suivi;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class SuiviController extends Controller
{
    // Affiche le formulaire de création depuis une consultation


    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('consultations.suivi'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les suivis.');

        if ($request->ajax()) {
            $suivis = Suivi::with(['patient','medecin','consultation'])->select('suivis.*');

            return DataTables::of($suivis)
                ->addColumn('patient', fn($s) => optional($s->patient)->nom . ' ' . optional($s->patient)->prenom)
                ->addColumn('medecin', fn($s) => optional($s->medecin)->name)
                ->addColumn('consultation', fn($s) => $s->consultation ? "Consultation #{$s->consultation->id}" : '-')
                ->addColumn('date_heure', fn($s) => $s->date_heure ? \Carbon\Carbon::parse($s->date_heure)->format('d-m-Y H:i') : '-')
                ->addColumn('motif', fn($s) => $s->motif ?? '-')
                ->addColumn('resultat', fn($s) => $s->resultat ?? '-')
                ->addColumn('statut', function($s) {
                    $labels = [
                        'prevu' => 'Prévu',
                        'realise' => 'Réalisé',
                        'annulé' => 'Annulé',
                        'termine' => 'Terminé'
                    ];
                    return $labels[$s->statut] ?? ucfirst($s->statut);
                })
                ->addColumn('actions', function($s){
                    $user = Auth::user();
                    $html = '';

                    if ($user->can('consultations.suivi')) {
                        $html .= '<button type="button" class="btn btn-sm btn-outline-primary view" data-id="'.$s->id.'" title="Détails"><i class="fa fa-eye"></i></button>';
                        $html .= '<button type="button" class="btn btn-sm btn-outline-info edit" data-id="'.$s->id.'" title="Modifier"><i class="fa fa-pencil-alt"></i></button>';
                        $html .= '<button type="button" class="btn btn-sm btn-outline-danger delete" data-id="'.$s->id.'" title="Supprimer"><i class="fa fa-trash"></i></button>';
                    }
                    
                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $html . '</div>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('application.suiviconsultation.index');
    }
    public function create(Consultation $consultation)
    {
        abort_unless(Auth::user()->can('consultations.suivi'), 403, 'Accès non autorisé : vous n\'avez pas la permission de créer un suivi.');

        $consultation->load(['patient', 'medecin']);

        return view('application.suiviconsultation.create', [
            'consultation' => $consultation,
            'patient' => $consultation->patient,
            'medecin' => $consultation->medecin,
        ]);
    }

    // Stocke le suivi créé depuis une consultation
   public function store(Request $request)
{
    abort_unless(Auth::user()->can('consultations.suivi'), 403, 'Accès non autorisé : vous n\'avez pas la permission de créer un suivi.');

    $consultation = Consultation::findOrFail($request->consultation_id);

    $request->validate([
        'motif'      => 'nullable|string|max:255',
        'resultat'   => 'required|string',
        'statut'     => 'required|in:prevu,realise,annulé',
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
