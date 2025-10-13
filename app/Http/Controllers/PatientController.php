<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('patients.view'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les dossiers patients.');

        if ($request->ajax()) {
            $year = session('exercice_year', date('Y'));
            $patients = Patient::forUserStructure(Auth::user())
                ->select(['patients.id', 'patients.uuid', 'patients.numero_dossier', 'patients.nom', 'patients.prenom', 'patients.genre', 'patients.telephone', 'patients.created_at'])
                ->whereYear('patients.created_at', $year);

            return datatables()->of($patients)
                ->addIndexColumn()
                ->editColumn('created_at', fn($p) => $p->created_at ? Carbon::parse($p->created_at)->format('d-m-Y') : '-')
                ->addColumn('actions', function($patient) {
                    $user = Auth::user();
                    $html = '';

                    if ($user->can('patients.view') || $user->can('patients.dossier')) {
                        $html .= '<a href="'.route('patients.show', $patient).'" class="btn btn-sm btn-outline-primary" title="Voir"><i class="fa fa-eye"></i></a>';
                    }
                    if ($user->can('patients.edit')) {
                        $html .= '<a href="'.route('patients.edit', $patient).'" class="btn btn-sm btn-outline-info" title="Modifier"><i class="fa fa-pencil-alt"></i></a>';
                    }
                    if ($user->can('patients.dossier')) {
                        $html .= '<a href="'.route('patients.medicales', $patient).'" target="_blank" class="btn btn-sm btn-outline-warning" title="Imprimer"><i class="fa fa-print"></i></a>';
                    }
                    if ($user->can('patients.delete')) {
                        $html .= '<form action="'.route('patients.destroy', $patient).'" method="POST" class="d-inline" onsubmit="return confirm(\'Supprimer ce patient ?\');">'.csrf_field().method_field('DELETE').'<button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="fa fa-trash"></i></button></form>';
                    }
                    
                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $html . '</div>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('application.patient.index');
    }

    public function create()
    {
        abort_unless(Auth::user()->can('patients.create'), 403, 'Accès non autorisé : vous n\'avez pas la permission de créer un patient.');

        return view('application.patient.create');
    }

 public function store(Request $request)
    {
        abort_unless(Auth::user()->can('patients.create'), 403, 'Accès non autorisé : vous n\'avez pas la permission de créer un patient.');

        $validated = $request->validate([
            'nom'              => 'required|string|max:255',
            'prenom'           => 'required|string|max:255',
            'genre'            => 'required|in:M,F',
            'telephone'        => 'required|string|max:20|unique:patients,telephone,NULL,id,etablissement_id,' . \App\Models\Etablissement::courantId(),
            'ethnie'           => 'required|string|max:255',
            'age'              => 'required|integer|min:0',
            'assurance_id'     => 'nullable|exists:assurances,id',
            'numero_assurance' => 'nullable|string|max:255',
            'groupe_sanguin'   => 'nullable|string|max:5',
            'adresse'          => 'nullable|string',
            'allergies'        => 'nullable|string|max:1000',
            'date_naissance'   => 'nullable|date|before_or_equal:today',
            'provenance'       => [\Illuminate\Validation\Rule::requiredIf(fn() => \App\Models\Etablissement::courant()->parametre('patient.provenance')), 'nullable', \Illuminate\Validation\Rule::in(array_keys(Patient::PROVENANCES))],
            'cscom_origine'    => 'nullable|required_if:provenance,cscom|string|max:255',
        ], ['cscom_origine.required_if' => 'Indiquez le CSCom qui a référé le patient.']);

        // EF-02.02 : signaler les doublons probables avant de créer le dossier
        if (!$request->boolean('confirmer_doublon')) {
            $doublons = Patient::doublonsProbables($validated['nom'], $validated['prenom']);
            if ($doublons->isNotEmpty()) {
                return response()->json([
                    'success'  => false,
                    'doublons' => $doublons,
                    'message'  => 'Un dossier existe peut-être déjà pour ce patient.',
                ], 409);
            }
        }

        $patient = Patient::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Le patient a été enregistré avec succès.',
            'patient' => $patient
        ]);
    }
    public function search(Request $request)
    {
        abort_unless(Auth::user()->can('patients.search'), 403, 'Accès non autorisé : vous n\'avez pas la permission de rechercher un patient.');

        $query = Patient::forUserStructure(Auth::user())->where(function($q) use ($request) {
            $q->where('nom', 'like', '%'.$request->q.'%')
              ->orWhere('prenom', 'like', '%'.$request->q.'%')
              ->orWhere('telephone', 'like', '%'.$request->q.'%')
              ->orWhere('numero_dossier', 'like', '%'.$request->q.'%');
        });

        if ($request->has('gender')) {
            $genders = is_array($request->gender) ? $request->gender : explode(',', $request->gender);
            $query->whereIn('genre', $genders);
        }

        if ($request->has('min_age')) {
            $query->where('age', '>=', $request->min_age);
        }

        $patients = $query->limit(20)
            ->get(['patients.id', 'patients.uuid', 'patients.numero_dossier', 'patients.nom','patients.prenom','patients.telephone','patients.age','patients.genre','patients.assurance_id','patients.numero_assurance','patients.fin_validite_assurance']);

        return response()->json($patients);
    }

    /**
     * Afficher le dossier complet d’un patient
     */
    public function show(Patient $patient)
    {
        abort_unless(Auth::user()->can('patients.dossier'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les dossiers patients.');

        $user = Auth::user();
        if (!$user->hasRole(['super_admin', 'superadmin', 'admin'])) {
            $isAuthorized = Patient::forUserStructure($user)->where('patients.id', $patient->id)->exists();
            abort_unless($isAuthorized, 403, 'Accès non autorisé : ce patient ne fait pas partie de votre structure.');
        }

        $patient->load([
            'consultations.ordonnances',
            'consultations.examens',
            'consultations.rendezVous',
            'consultations.certificat',
            'consultations.hospitalisation',
            'consultations.symptomes',
            'consultations.maladies',
//            'consultations.paiements',
            'hospitalisations.paiements',
            'grossesses.cpns',
            'transferts.sourceMedecin',
            'transferts.destMedecin',
            'transferts.sourceService',
            'transferts.destService',
        ]);

        $synthese = app(\App\Services\DossierPatientService::class)->synthese($patient);
        $fusions = \App\Models\FusionPatient::with(['fusionne', 'user'])->where('patient_conserve_id', $patient->id)->latest()->get();

        return view('application.patient.show', compact('patient', 'synthese', 'fusions'));
    }

    public function edit(Patient $patient)
    {
        abort_unless(Auth::user()->can('patients.edit'), 403, 'Accès non autorisé : vous n\'avez pas la permission de modifier un patient.');

        return view('application.patient.create', compact('patient'));
    }

public function update(Request $request, Patient $patient)
{
    abort_unless(Auth::user()->can('patients.edit'), 403, 'Accès non autorisé : vous n\'avez pas la permission de modifier un patient.');

    $validated = $request->validate([
        'nom' => 'required|string|max:255',
        'prenom' => 'required|string|max:255',
        'genre' => 'required|in:M,F',
        'telephone' => 'required|string|max:20|unique:patients,telephone,'.$patient->id.',id,etablissement_id,' . \App\Models\Etablissement::courantId(),
        'ethnie' => 'required|string|max:255',
        'age' => 'required|integer|min:0',
        'assurance_id' => 'nullable|exists:assurances,id',
        'numero_assurance' => 'nullable|string|max:255',
        'groupe_sanguin' => 'nullable|string|max:5',
        'adresse' => 'nullable|string',
        'allergies' => 'nullable|string|max:1000',
        'date_naissance' => 'nullable|date|before_or_equal:today',
        'provenance' => ['nullable', \Illuminate\Validation\Rule::in(array_keys(Patient::PROVENANCES))],
        'cscom_origine' => 'nullable|required_if:provenance,cscom|string|max:255',
    ]);

    // Mise à jour en base de données
    $patient->update($validated);

    // CORRECTION FONDAMENTALE : On retourne du JSON pour que SweetAlert2 côté JS puisse s'exécuter
    return response()->json([
        'success' => true,
        'message' => 'Le patient a été mis à jour avec succès.',
        'patient' => $patient
    ]);
}

    public function destroy(Patient $patient)
    {
        abort_unless(Auth::user()->can('patients.delete'), 403, 'Accès non autorisé : vous n\'avez pas la permission de supprimer un patient.');

        $patient->delete();

        return redirect()->route('patients.index')
            ->with('success', 'Patient supprimé.');
    }

    public function print(Patient $patient)
    {
        abort_unless(Auth::user()->can('patients.dossier'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir ou imprimer le dossier.');

        $patient->load([
            'consultations.ordonnances.medicaments',
            'consultations.examens',
            'consultations.rendezVous',
            'consultations.certificat',
            'consultations.hospitalisation',
            'consultations.symptomes',
            'consultations.maladies',
            'hospitalisations.paiements',
        ]);

        $pdf = Pdf::loadView('application.patient.pdf', compact('patient'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("dossier_{$patient->nom}_{$patient->prenom}.pdf");
    }
}
