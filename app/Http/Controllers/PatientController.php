<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $patients = Patient::select(['id', 'nom', 'prenom', 'genre', 'telephone', 'created_at']);

            return datatables()->of($patients)
                ->addIndexColumn()
                ->editColumn('created_at', fn($p) => $p->created_at ? Carbon::parse($p->created_at)->format('d-m-Y') : '-')
                ->addColumn('actions', function($patient) {
                    $view = '<a href="'.route('patients.show', $patient).'" class="btn-sm" title="Voir"><i class="fa fa-eye text-primary"></i></a> ';
                    $edit = '<a href="'.route('patients.edit', $patient).'" class="btn-sm" title="Modifier"><i class="fa fa-pencil-alt text-info"></i></a> ';
                    $print = '<a href="'.route('patients.medicales', $patient).'" target="_blank" class="btn-sm" title="Imprimer"><i class="fa fa-print text-warning"></i></a> ';
                    $delete = '<form action="'.route('patients.destroy', $patient).'" method="POST" class="d-inline" onsubmit="return confirm(\'Supprimer ce patient ?\');">'.csrf_field().method_field("DELETE").'<button type="submit" class="btn-sm border-0 bg-transparent" title="Supprimer"><i class="fa fa-trash text-danger"></i></button></form>';
                    return $view.$edit.$print.$delete;

                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('application.patient.index');
    }

    public function create()
    {
        return view('application.patient.create');
    }

    public function store(Request $request)
    {
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
        $patients = Patient::where('nom', 'like', '%'.$request->q.'%')
            ->orWhere('telephone', 'like', '%'.$request->q.'%')
            ->limit(10)
            ->get(['id','nom','prenom','telephone','assurance_id','numero_assurance','fin_validite_assurance']);

        return response()->json($patients);
    }

    /**
     * Afficher le dossier complet d’un patient
     */
    public function show(Patient $patient)
    {
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
        return view('application.patient.create', compact('patient'));
    }

    public function update(Request $request, Patient $patient)
    {
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
        $patient->delete();

        return redirect()->route('patients.index')
            ->with('success', 'Patient supprimé.');
    }

    public function print(Patient $patient)
    {
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
