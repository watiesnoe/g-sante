<?php

namespace App\Http\Controllers;

use App\Models\RendezVous;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class SuiviController extends Controller
{
    /**
     * Liste des suivis post-consultation (rendez-vous liés à une consultation).
     */
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('consultations.suivi'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les suivis.');

        if ($request->ajax()) {
            $suivis = RendezVous::with(['patient', 'medecin', 'consultation'])
                ->whereNotNull('consultation_id')
                ->select('rendezvous.*');

            return DataTables::of($suivis)
                ->addColumn('patient', fn($s) => optional($s->patient)->nom . ' ' . optional($s->patient)->prenom)
                ->addColumn('medecin', fn($s) => optional($s->medecin)->name)
                ->addColumn('consultation', fn($s) => $s->consultation ? "Consultation #{$s->consultation->id}" : '-')
                ->addColumn('date_heure', fn($s) => $s->date_heure ? \Carbon\Carbon::parse($s->date_heure)->format('d-m-Y H:i') : '-')
                ->addColumn('motif', fn($s) => $s->motif ?? '-')
                ->addColumn('resultat', fn($s) => $s->resultat ?? '-')
                ->addColumn('statut', function ($s) {
                    $labels = [
                        'prevu' => 'Prévu',
                        'en_attente' => 'En attente',
                        'realise' => 'Réalisé',
                        'annule' => 'Annulé',
                    ];
                    return $labels[$s->statut] ?? ucfirst($s->statut);
                })
                ->addColumn('actions', function ($s) {
                    $user = Auth::user();
                    $html = '';

                    // Le détail/la modification/la suppression sont gérés depuis la
                    // page Rendez-vous (fonctionnalité déjà complète), pour éviter de
                    // dupliquer cette logique ici.
                    if ($user->can('rendezvous.view')) {
                        $html .= '<a href="' . route('rendezvous.show', $s->uuid) . '" class="btn btn-sm btn-outline-primary" title="Détails"><i class="fa fa-eye"></i></a>';
                    }
                    if ($user->can('rendezvous.edit')) {
                        $html .= '<a href="' . route('rendezvous.edit', $s->uuid) . '" class="btn btn-sm btn-outline-info" title="Modifier"><i class="fa fa-pencil-alt"></i></a>';
                    }

                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $html . '</div>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('application.suiviconsultation.index');
    }

    /**
     * Formulaire de création d'un suivi depuis une consultation.
     */
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

    /**
     * Enregistre le suivi créé depuis une consultation (sous forme de rendez-vous).
     */
    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('consultations.suivi'), 403, 'Accès non autorisé : vous n\'avez pas la permission de créer un suivi.');

        $consultation = Consultation::findOrFail($request->consultation_id);

        $request->validate([
            'motif'      => 'nullable|string|max:255',
            'resultat'   => 'required|string',
            'statut'     => 'required|in:prevu,en_attente,realise,annule',
        ]);

        $suivi = RendezVous::create([
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
            'suivi_id'        => $suivi->id,
        ]);
    }
}
