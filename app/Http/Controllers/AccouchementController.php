<?php

namespace App\Http\Controllers;

use App\Models\Accouchement;
use App\Models\Patient;
use App\Services\MaterniteService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/** Salle de naissance : registre des accouchements et des nouveau-nés (EF-10.04). */
class AccouchementController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('maternity.view'), 403, 'Accès non autorisé.');

        $du = $request->du ? Carbon::parse($request->du) : today()->startOfMonth();
        $au = $request->au ? Carbon::parse($request->au) : today();

        $accouchements = Accouchement::with(['patient', 'nouveauNes', 'accoucheur'])
            ->whereBetween('date_heure', [$du->copy()->startOfDay(), $au->copy()->endOfDay()])->latest('date_heure')->get();

        return view('application.maternity.accouchements', compact('accouchements', 'du', 'au'));
    }

    public function create(Request $request)
    {
        abort_unless(Auth::user()->can('maternity.accouchement'), 403, 'Accès non autorisé.');
        $mere = $request->patient ? Patient::where('uuid', $request->patient)->firstOrFail() : null;

        return view('application.maternity.accouchement', compact('mere'));
    }

    public function store(Request $request, MaterniteService $service)
    {
        abort_unless(Auth::user()->can('maternity.accouchement'), 403, 'Accès non autorisé.');

        $validated = $request->validate([
            'patient_id'                 => 'required|exists:patients,id',
            'date_heure'                 => 'required|date|before_or_equal:now',
            'mode'                       => ['required', Rule::in(array_keys(Accouchement::MODES))],
            'terme_sa'                   => 'nullable|integer|min:20|max:45',
            'presentation'               => 'nullable|string|max:30',
            'etat_mere'                  => ['required', Rule::in(array_keys(Accouchement::ETATS_MERE))],
            'complications'              => 'nullable|string',
            'delivrance_complete'        => 'nullable|boolean',
            'perte_sang_ml'              => 'nullable|integer|min:0|max:10000',
            'observations'               => 'nullable|string',
            'enfants'                    => 'required|array|min:1|max:6',
            'enfants.*.sexe'             => 'required|in:M,F,I',
            'enfants.*.etat'             => ['required', Rule::in(array_keys(\App\Models\NouveauNe::ETATS))],
            'enfants.*.prenom'           => 'nullable|string|max:100',
            'enfants.*.poids_g'          => 'nullable|integer|min:200|max:7000',
            'enfants.*.taille_cm'        => 'nullable|numeric|min:20|max:70',
            'enfants.*.perimetre_cranien_cm' => 'nullable|numeric|min:15|max:50',
            'enfants.*.apgar_1'          => 'nullable|integer|min:0|max:10',
            'enfants.*.apgar_5'          => 'nullable|integer|min:0|max:10',
            'enfants.*.reanimation'      => 'nullable|boolean',
        ]);

        $mere = Patient::findOrFail($validated['patient_id']);
        abort_unless($mere->genre === 'F', 422, 'La patiente doit être de sexe féminin.');

        $enfants = $validated['enfants'];
        unset($validated['enfants'], $validated['patient_id']);
        $validated['delivrance_complete'] = $request->boolean('delivrance_complete', true);

        $service->enregistrerAccouchement($mere, $validated, $enfants);

        return redirect()->route('accouchements.index')->with('success', 'Accouchement enregistré ; dossier ouvert pour chaque enfant né vivant.');
    }
}
