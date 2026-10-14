<?php

namespace App\Http\Controllers;

use App\Models\FusionPatient;
use App\Models\Patient;
use App\Services\DossierPatientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/** EF-02.03 : fusion de deux dossiers, motivée, journalisée et réversible. */
class FusionPatientController extends Controller
{
    public function store(Request $request, Patient $patient, DossierPatientService $service)
    {
        abort_unless(Auth::user()->can('patients.fusionner'), 403, 'Accès non autorisé.');

        $request->validate(['doublon_id' => 'required|exists:patients,id', 'motif' => 'required|string|max:255']);

        try {
            $service->fusionner($patient, Patient::findOrFail($request->doublon_id), $request->motif);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', 'Dossiers fusionnés. La fusion peut être annulée depuis ce dossier.');
    }

    public function annuler(FusionPatient $fusion, DossierPatientService $service)
    {
        abort_unless(Auth::user()->can('patients.fusionner'), 403, 'Accès non autorisé.');

        try {
            $service->annuler($fusion);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', 'Fusion annulée : les deux dossiers sont de nouveau séparés.');
    }
}
