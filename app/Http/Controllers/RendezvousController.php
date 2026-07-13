<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\RendezVous;
use App\Models\User;
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
            $rdvs = RendezVous::with(['patient.grossesses', 'medecin', 'consultation'])
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
                ->addColumn('actions', function ($rdv) {
                    $user = Auth::user();
                    $html = '';

                    // 👁 Voir (rendezvous.view)
                    if ($user->can('rendezvous.view')) {
                        $html .= '<a href="' . route('rendezvous.show', $rdv->uuid) . '" class="btn btn-sm btn-outline-primary" title="Voir"><i class="fa fa-eye"></i></a>';
                    }

                    // 🤰 Maternité (maternity.view) s'il y a une grossesse active en cours
                    $activeGrossesse = $rdv->patient ? $rdv->patient->grossesses->where('statut', 'En cours')->first() : null;
                    if ($user->can('maternity.view') && $activeGrossesse) {
                        $html .= '<a href="' . route('maternity.show', [$activeGrossesse->uuid, 'rdv_uuid' => $rdv->uuid]) . '" class="btn btn-sm btn-outline-danger" title="Maternité" style="color: #e91e63; border-color: #e91e63;"><i class="fa fa-female"></i></a>';
                    }

                    // ✏️ Modifier (rendezvous.edit)
                    if ($user->can('rendezvous.edit')) {
                        $html .= '<a href="' . route('rendezvous.edit', $rdv->uuid) . '" class="btn btn-sm btn-outline-info" title="Modifier"><i class="fa fa-pencil-alt"></i></a>';
                    }

                    // ✅ Marquer réalisé (rendezvous.confirm)
                    if ($user->can('rendezvous.confirm') && $rdv->statut !== 'realise') {
                        $html .= '<button type="button" class="btn btn-sm btn-outline-success btn-realise" data-url="' . route('rendezvous.marquerRealise', $rdv->uuid) . '" title="Marquer comme réalisé"><i class="fa fa-check"></i></button>';
                    }

                    // 📋 Créer un suivi (consultations.suivi)
                    if ($user->can('consultations.suivi') && $rdv->consultation) {
                        $html .= '<a href="' . route('consultations.suivi.create', $rdv->consultation) . '" class="btn btn-sm btn-outline-success" title="Créer un suivi"><i class="fa fa-file-medical"></i></a>';
                    }

                    // 🗑 Supprimer (rendezvous.delete)
                    if ($user->can('rendezvous.delete')) {
                        $html .= '<button type="button" data-url="' . route('rendezvous.destroy', $rdv->uuid) . '" class="btn btn-sm btn-outline-danger btn-delete" title="Supprimer"><i class="fa fa-trash"></i></button>';
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
    public function create(Request $request, $patient = null)
    {
        abort_unless(Auth::user()->can('rendezvous.create'), 403, 'Accès non autorisé');

        // Résoudre le patient depuis le segment de route (UUID) ou le query param
        if ($patient) {
            $patient = Patient::where('uuid', $patient)->firstOrFail();
        } elseif ($request->has('patient_id')) {
            $patient = Patient::where('uuid', $request->patient_id)->firstOrFail();
        }

        $medecins = User::whereHas('roles', function ($q) {
            $q->where('name', 'medecin');
        })->orderBy('name')->get();

        // Médecin pré-sélectionné : depuis query param ou utilisateur connecté s'il est médecin
        $preselectedMedecinId = $request->input('medecin_id', Auth::id());

        return view('application.rendezvous.create', compact('patient', 'medecins', 'preselectedMedecinId'));
    }

    public function store(Request $request)
    {
        if ($request->id) {
            abort_unless(Auth::user()->can('rendezvous.edit'), 403, 'Accès non autorisé');
        } else {
            abort_unless(Auth::user()->can('rendezvous.create'), 403, 'Accès non autorisé');
        }

        // ── Résolution UUID → ID numérique ──────────────────────────────
        // Support des deux modes : UUID (depuis modale) ou ID numérique (depuis index)
        if ($request->filled('patient_uuid')) {
            $patient = Patient::where('uuid', $request->patient_uuid)->firstOrFail();
            $patientId = $patient->id;
        } else {
            $patientId = $request->patient_id;
        }

        if ($request->filled('medecin_uuid')) {
            $medecin = User::where('uuid', $request->medecin_uuid)->firstOrFail();
            $medecinId = $medecin->id;
        } else {
            $medecinId = $request->medecin_id;
        }
        // ────────────────────────────────────────────────────────────────

        $request->validate([
            'consultation_id' => 'nullable|exists:consultations,id',
            'date_heure'      => 'required|date',
            'motif'           => 'nullable|string|max:255',
        ]);

        // Valider que patient et médecin ont bien été résolus
        abort_if(empty($patientId), 422, 'Patient introuvable.');
        abort_if(empty($medecinId), 422, 'Médecin introuvable.');

        $data = [
            'patient_id'      => $patientId,
            'medecin_id'      => $medecinId,
            'consultation_id' => $request->consultation_id,
            'date_heure'      => $request->date_heure,
            'motif'           => $request->motif,
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
            $rdvs = RendezVous::with(['patient.grossesses', 'medecin', 'consultation'])
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
                ->addColumn('actions', function ($rdv) {
                    $user = Auth::user();
                    $html = '';

                    // 👁 Voir (rendezvous.view)
                    if ($user->can('rendezvous.view')) {
                        $html .= '<a href="' . route('rendezvous.show', $rdv->uuid) . '" class="btn btn-sm btn-outline-primary" title="Voir"><i class="fa fa-eye"></i></a>';
                    }

                    // 🤰 Maternité (maternity.view) s'il y a une grossesse active en cours
                    $activeGrossesse = $rdv->patient ? $rdv->patient->grossesses->where('statut', 'En cours')->first() : null;
                    if ($user->can('maternity.view') && $activeGrossesse) {
                        $html .= '<a href="' . route('maternity.show', [$activeGrossesse->uuid, 'rdv_uuid' => $rdv->uuid]) . '" class="btn btn-sm btn-outline-danger" title="Maternité" style="color: #e91e63; border-color: #e91e63;"><i class="fa fa-female"></i></a>';
                    }

                    // ✏️ Modifier (rendezvous.edit)
                    if ($user->can('rendezvous.edit')) {
                        $html .= '<a href="' . route('rendezvous.edit', $rdv->uuid) . '" class="btn btn-sm btn-outline-info" title="Modifier"><i class="fa fa-pencil-alt"></i></a>';
                    }

                    // 📋 Créer un suivi (consultations.suivi)
                    if ($user->can('consultations.suivi') && $rdv->consultation) {
                        $html .= '<a href="' . route('consultations.suivi.create', $rdv->consultation) . '" class="btn btn-sm btn-outline-success" title="Créer un suivi"><i class="fa fa-file-medical"></i></a>';
                    }

                    // 🗑 Supprimer (rendezvous.delete)
                    if ($user->can('rendezvous.delete')) {
                        $html .= '<button type="button" data-url="' . route('rendezvous.destroy', $rdv->uuid) . '" class="btn btn-sm btn-outline-danger btn-delete" title="Supprimer"><i class="fa fa-trash"></i></button>';
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
            $rdvs = RendezVous::with(['patient.grossesses', 'medecin', 'consultation'])
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
                ->addColumn('actions', function ($rdv) {
                    $user = Auth::user();
                    $html = '';

                    // 👁 Voir (rendezvous.view)
                    if ($user->can('rendezvous.view')) {
                        $html .= '<a href="' . route('rendezvous.show', $rdv->uuid) . '" class="btn btn-sm btn-outline-primary" title="Voir"><i class="fa fa-eye"></i></a>';
                    }

                    // 🤰 Maternité (maternity.view) s'il y a une grossesse active en cours
                    $activeGrossesse = $rdv->patient ? $rdv->patient->grossesses->where('statut', 'En cours')->first() : null;
                    if ($user->can('maternity.view') && $activeGrossesse) {
                        $html .= '<a href="' . route('maternity.show', [$activeGrossesse->uuid, 'rdv_uuid' => $rdv->uuid]) . '" class="btn btn-sm btn-outline-danger" title="Maternité" style="color: #e91e63; border-color: #e91e63;"><i class="fa fa-female"></i></a>';
                    }

                    // ✏️ Modifier (rendezvous.edit)
                    if ($user->can('rendezvous.edit')) {
                        $html .= '<a href="' . route('rendezvous.edit', $rdv->uuid) . '" class="btn btn-sm btn-outline-info" title="Modifier"><i class="fa fa-pencil-alt"></i></a>';
                    }

                    // 🗑 Supprimer (rendezvous.delete)
                    if ($user->can('rendezvous.delete')) {
                        $html .= '<button type="button" data-url="' . route('rendezvous.destroy', $rdv->uuid) . '" class="btn btn-sm btn-outline-danger btn-delete" title="Supprimer"><i class="fa fa-trash"></i></button>';
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

    public function edit(RendezVous $rendezvous)
    {
        abort_unless(Auth::user()->can('rendezvous.edit'), 403, 'Accès non autorisé.');

        $rendezvous->load(['patient', 'medecin', 'consultation']);
        $medecins = \App\Models\User::whereHas('roles', function ($q) {
            $q->where('name', 'medecin');
        })->orderBy('name')->get();

        return view('application.rendezvous.edit', compact('rendezvous', 'medecins'));
    }

    public function update(Request $request, RendezVous $rendezvous)
    {
        abort_unless(Auth::user()->can('rendezvous.edit'), 403, 'Accès non autorisé.');

        // 1. AJOUT de 'medecin_id' et 'statut' dans la validation
        $request->validate([
            'date_heure' => 'required|date',
            'motif'      => 'nullable|string|max:255',
            'medecin_id' => 'required|exists:users,id', // s'adapte à votre table des médecins/utilisateurs
            'statut'     => 'required|in:prevu,en_attente,realise,annule',
        ]);

        // 2. Prise en compte de toutes les modifications
        $rendezvous->update([
            'date_heure' => $request->date_heure,
            'motif'      => $request->motif,
            'medecin_id' => $request->medecin_id,
            'statut'     => $request->statut,
        ]);

        // 3. CORRECTION : Redirection web classique plutôt qu'un retour JSON
        return redirect()->route('rendezvous.index')
            ->with('success', 'Le rendez-vous a été modifié avec succès.');
    }

    public function destroy(RendezVous $rendezvous)
    {
        abort_unless(Auth::user()->can('rendezvous.delete'), 403, 'Accès non autorisé.');

        $rendezvous->statut = 'annule';
        $rendezvous->save();

        return response()->json(['success' => true, 'message' => 'Rendez-vous annulé avec succès.']);
    }
}
