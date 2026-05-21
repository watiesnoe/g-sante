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
            $patients = Patient::select(['id', 'uuid', 'nom', 'prenom', 'genre', 'telephone', 'created_at'])
                ->whereYear('created_at', $year);

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
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'genre' => 'required|in:M,F',
            'telephone' => 'required|string|max:20|unique:patients,telephone',
            'ethnie' => 'required|string|max:255',
            'age' => 'required|integer|min:0',
            'assurance_id' => 'nullable|exists:assurances,id',
            'numero_assurance' => 'nullable|string|max:255',
            'fin_validite_assurance' => 'nullable|date',
        ]);

        $patient = Patient::create($validated);

        return response()->json([
            'success' => true,
            'patient' => $patient
        ]);
    }

    public function search(Request $request)
    {
        abort_unless(Auth::user()->can('patients.search'), 403, 'Accès non autorisé : vous n\'avez pas la permission de rechercher un patient.');

        $patients = Patient::where('nom', 'like', '%'.$request->q.'%')
            ->orWhere('telephone', 'like', '%'.$request->q.'%')
            ->limit(10)
            ->get(['id', 'uuid','nom','prenom','telephone','assurance_id','numero_assurance','fin_validite_assurance']);

        return response()->json($patients);
    }

    /**
     * Afficher le dossier complet d’un patient
     */
    public function show(Patient $patient)
    {
        abort_unless(Auth::user()->can('patients.dossier'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les dossiers patients.');

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

        return view('application.patient.show', compact('patient'));
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
            'telephone' => 'required|string|max:20|unique:patients,telephone,'.$patient->id,
            'ethnie' => 'required|string|max:255',
            'age' => 'required|integer|min:0',
        ]);

        $patient->update($validated);

        return redirect()->route('patients.show', $patient)
            ->with('success', 'Patient mis à jour.');
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
