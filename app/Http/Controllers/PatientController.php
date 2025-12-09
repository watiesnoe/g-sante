<?php

namespace App\Http\Controllers;

use App\Models\Patient;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $patients = Patient::select(['id', 'nom', 'prenom', 'genre', 'telephone', 'created_at']);

            return datatables()->of($patients)
                ->addIndexColumn()
                ->addColumn('actions', function($patient) {
                    $btn = '
                            <div class="dropdown">
                              <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="dropdownMenuButton'.$patient->id.'" data-bs-toggle="dropdown" aria-expanded="false">
                                Actions
                              </button>
                              <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton'.$patient->id.'">
                                <li><a class="dropdown-item" href="'.route('patients.show', $patient).'">Voir</a></li>
                                <li><a class="dropdown-item" href="'.route('patients.edit', $patient).'">Modifier</a></li>
                                <li><a class="dropdown-item" href="'.route('patients.medicales', $patient).'" target="_blank">Imprimer</a></li>
                                <li>
                                  <form action="'.route('patients.destroy', $patient).'" method="POST" onsubmit="return confirm(\'Supprimer ce patient ?\');">
                                    '.csrf_field().method_field('DELETE').'
                                    <button type="submit" class="dropdown-item text-danger">Supprimer</button>
                                  </form>
                                </li>
                              </ul>
                            </div>
                            ';

                    return $btn;

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
            ->get(['id','nom','prenom','telephone']);

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
