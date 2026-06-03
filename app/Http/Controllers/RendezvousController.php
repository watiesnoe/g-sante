<?php

namespace App\Http\Controllers;

use App\Models\RendezVous;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
class RendezvousController extends Controller
{


    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('rendezvous.view'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les rendez-vous.');

        if ($request->ajax()) {
            $year = session('exercice_year', date('Y'));
            $rdvs = RendezVous::with(['patient', 'medecin', 'consultation'])
                ->whereYear('date_heure', $year)
                ->whereNotIn('statut', ['annule', 'realise']) // 🔹 Exclure annulé et réalisé
                ->select('rendezvous.*');

            return DataTables::of($rdvs)
                ->addIndexColumn()

                // Patient
                ->addColumn('patient', fn($rdv) => optional($rdv->patient)->nom . ' ' . optional($rdv->patient)->prenom ?? '-')

                // Médecin
                ->addColumn('medecin', fn($rdv) => optional($rdv->medecin)->name ?? '-')

                // Date formatée
                ->addColumn('date_heure', fn($rdv) => $rdv->date_heure ? Carbon::parse($rdv->date_heure)->format('d-m-Y H:i') : '-')

                // Motif
                ->addColumn('motif', fn($rdv) => $rdv->motif ?? '-')

                // Consultation
                ->addColumn('consultation', fn($rdv) => $rdv->consultation ? "Consultation #{$rdv->consultation->id}" : '-')

                // Actions — filtrées selon les permissions de l'utilisateur connecté
                ->addColumn('actions', function($rdv) {
                    $user = Auth::user();
                    $html = '';

                    // 👁 Voir (rendezvous.view)
                    if ($user->can('rendezvous.view')) {
                        $html .= '<a href="'.route('rendezvous.show', $rdv->uuid).'" class="btn btn-sm btn-outline-primary" title="Voir"><i class="fa fa-eye"></i></a>';
                    }

                    // ✏️ Modifier (rendezvous.edit)
                    if ($user->can('rendezvous.edit')) {
                        $html .= '<a href="'.route('rendezvous.edit', $rdv->uuid).'" class="btn btn-sm btn-outline-info" title="Modifier"><i class="fa fa-pencil-alt"></i></a>';
                    }

                    // ✅ Marquer réalisé (rendezvous.confirm)
                    if ($user->can('rendezvous.confirm') && $rdv->statut !== 'realise') {
                        $html .= '<button type="button" class="btn btn-sm btn-outline-success btn-realise" data-url="'.route('rendezvous.marquerRealise', $rdv->uuid).'" title="Marquer comme réalisé"><i class="fa fa-check"></i></button>';
                    }

                    // 📋 Créer un suivi (consultations.suivi)
                    if ($user->can('consultations.suivi') && $rdv->consultation) {
                        $html .= '<a href="'.route('consultations.suivi.create', $rdv->consultation->id).'" class="btn btn-sm btn-outline-success" title="Créer un suivi"><i class="fa fa-file-medical"></i></a>';
                    }

                    // 🗑 Supprimer (rendezvous.delete)
                    if ($user->can('rendezvous.delete')) {
                        $html .= '<button type="button" data-url="'.route('rendezvous.destroy', $rdv->uuid).'" class="btn btn-sm btn-outline-danger btn-delete" title="Supprimer"><i class="fa fa-trash"></i></button>';
                    }

                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $html . '</div>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        $prefillConsultation = null;
        if ($request->has('consultation')) {
            $prefillConsultation = \App\Models\Consultation::with(['patient', 'medecin'])->where('uuid', $request->consultation)->first();
        }

        return view('application.rendezvous.index', compact('prefillConsultation'));
    }

    public function store(Request $request)
    {
        if ($request->id) {
            abort_unless(Auth::user()->can('rendezvous.edit'), 403, 'Accès non autorisé');
        } else {
            abort_unless(Auth::user()->can('rendezvous.create'), 403, 'Accès non autorisé');
        }

        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'medecin_id' => 'required|exists:users,id',
            'consultation_id' => 'nullable|exists:consultations,id',
            'date_heure' => 'required|date',
            'motif' => 'nullable|string|max:255',
        ]);

        $data = [
            'patient_id' => $request->patient_id,
            'medecin_id' => $request->medecin_id,
            'consultation_id' => $request->consultation_id,
            'date_heure' => $request->date_heure,
            'motif' => $request->motif,
        ];

        if (!$request->id) {
            $data['statut'] = 'prevu';
        }

        $rdv = RendezVous::updateOrCreate(
            ['id' => $request->id],
            $data
        );

        return response()->json(['success' => true, 'data' => $rdv]);
    }

    public function disponible(Request $request)
    {
        abort_unless(Auth::user()->can('rendezvous.view'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les rendez-vous réalisés.');

        if ($request->ajax()) {
            $rdvs = RendezVous::with(['patient', 'medecin', 'consultation'])
                ->where('statut', ['realise'])
                ->select('rendezvous.*');

            return DataTables::of($rdvs)
                ->addIndexColumn()

                // Patient
                ->addColumn('patient', fn($rdv) => optional($rdv->patient)->nom . ' ' . optional($rdv->patient)->prenom ?? '-')

                // Médecin
                ->addColumn('medecin', fn($rdv) => optional($rdv->medecin)->name ?? '-')

                // Date formatée
                ->addColumn('date_heure', fn($rdv) => $rdv->date_heure ? Carbon::parse($rdv->date_heure)->format('d-m-Y H:i') : '-')

                // Motif
                ->addColumn('motif', fn($rdv) => $rdv->motif ?? '-')

                // Consultation
                ->addColumn('consultation', fn($rdv) => $rdv->consultation ? "Consultation #{$rdv->consultation->id}" : '-')

                // Actions — filtrées selon les permissions de l'utilisateur connecté
                ->addColumn('actions', function($rdv) {
                    $user = Auth::user();
                    $html = '';

                    // 👁 Voir (rendezvous.view)
                    if ($user->can('rendezvous.view')) {
                        $html .= '<a href="'.route('rendezvous.show', $rdv->uuid).'" class="btn btn-sm btn-outline-primary" title="Voir"><i class="fa fa-eye"></i></a>';
                    }

                    // ✏️ Modifier (rendezvous.edit)
                    if ($user->can('rendezvous.edit')) {
                        $html .= '<a href="'.route('rendezvous.edit', $rdv->uuid).'" class="btn btn-sm btn-outline-info" title="Modifier"><i class="fa fa-pencil-alt"></i></a>';
                    }

                    // 📋 Créer un suivi (consultations.suivi)
                    if ($user->can('consultations.suivi') && $rdv->consultation) {
                        $html .= '<a href="'.route('consultations.suivi.create', $rdv->consultation->id).'" class="btn btn-sm btn-outline-success" title="Créer un suivi"><i class="fa fa-file-medical"></i></a>';
                    }

                    // 🗑 Supprimer (rendezvous.delete)
                    if ($user->can('rendezvous.delete')) {
                        $html .= '<button type="button" data-url="'.route('rendezvous.destroy', $rdv->uuid).'" class="btn btn-sm btn-outline-danger btn-delete" title="Supprimer"><i class="fa fa-trash"></i></button>';
                    }

                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $html . '</div>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('application.rendezvous.rdvdisponible');
    }
    public function annuler(Request $request)
    {
        abort_unless(Auth::user()->can('rendezvous.view'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les rendez-vous annulés.');

        if ($request->ajax()) {
            $rdvs = RendezVous::with(['patient', 'medecin', 'consultation'])
                ->where('statut', ['annule'])
                ->select('rendezvous.*');

            return DataTables::of($rdvs)
                ->addIndexColumn()

                // Patient
                ->addColumn('patient', fn($rdv) => optional($rdv->patient)->nom . ' ' . optional($rdv->patient)->prenom ?? '-')

                // Médecin
                ->addColumn('medecin', fn($rdv) => optional($rdv->medecin)->name ?? '-')

                // Date formatée
                ->addColumn('date_heure', fn($rdv) => $rdv->date_heure ? Carbon::parse($rdv->date_heure)->format('d-m-Y H:i') : '-')

                // Motif
                ->addColumn('motif', fn($rdv) => $rdv->motif ?? '-')

                // Consultation
                ->addColumn('consultation', fn($rdv) => $rdv->consultation ? "Consultation #{$rdv->consultation->id}" : '-')

                // Actions — filtrées selon les permissions de l'utilisateur connecté
                ->addColumn('actions', function($rdv) {
                    $user = Auth::user();
                    $html = '';

                    // 👁 Voir (rendezvous.view)
                    if ($user->can('rendezvous.view')) {
                        $html .= '<a href="'.route('rendezvous.show', $rdv->uuid).'" class="btn btn-sm btn-outline-primary" title="Voir"><i class="fa fa-eye"></i></a>';
                    }

                    // ✏️ Modifier (rendezvous.edit)
                    if ($user->can('rendezvous.edit')) {
                        $html .= '<a href="'.route('rendezvous.edit', $rdv->uuid).'" class="btn btn-sm btn-outline-info" title="Modifier"><i class="fa fa-pencil-alt"></i></a>';
                    }

                    // 🗑 Supprimer (rendezvous.delete)
                    if ($user->can('rendezvous.delete')) {
                        $html .= '<button type="button" data-url="'.route('rendezvous.destroy', $rdv->uuid).'" class="btn btn-sm btn-outline-danger btn-delete" title="Supprimer"><i class="fa fa-trash"></i></button>';
                    }

                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $html . '</div>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('application.rendezvous.annule');
    }
    public function marquerRealise(RendezVous $rendezvous)
    {
        abort_unless(Auth::user()->can('rendezvous.confirm'), 403, 'Accès non autorisé : vous n\'avez pas la permission de confirmer un rendez-vous.');

        // Vérifie que le rendez-vous n'est pas déjà réalisé
        if ($rendezvous->statut !== 'realise') {
            $rendezvous->statut = 'realise';
            $rendezvous->save();
            return response()->json(['success' => true, 'message' => 'Rendez-vous marqué comme réalisé.']);
        }

        return response()->json(['success' => false, 'message' => 'Rendez-vous déjà réalisé.']);
    }


    // Optionnel : afficher un rendez-vous
    public function show(RendezVous $rendezvous)
    {
        abort_unless(Auth::user()->can('rendezvous.view'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les rendez-vous.');

        $rendezvous->load(['patient', 'medecin', 'consultation']);
        return view('application.rendezvous.show', compact('rendezvous'));
    }
}
