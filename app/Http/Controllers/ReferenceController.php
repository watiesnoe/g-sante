<?php

namespace App\Http\Controllers;

use App\Models\Etablissement;
use App\Models\Patient;
use App\Models\Reference;
use App\Services\NumerotationService;
use App\Services\WorkflowService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Références et évacuations (EF-11.01 à EF-11.03) :
 * émise → reçue → prise en charge → contre-référence envoyée.
 */
class ReferenceController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('references.view'), 403, 'Accès non autorisé.');

        $references = Reference::with(['patient', 'user'])
            ->when($request->sens, fn($q) => $q->where('sens', $request->sens))
            ->latest('date_reference')->limit(300)->get();

        return view('application.reference.index', [
            'references' => $references,
            'etablissement' => Etablissement::courant(),
        ]);
    }

    public function store(Request $request, NumerotationService $numerotation)
    {
        abort_unless(Auth::user()->can('references.gerer'), 403, 'Accès non autorisé.');

        $validated = $request->validate([
            'sens'                  => 'required|in:entrante,sortante',
            'evacuation'            => 'nullable|boolean',
            'patient_id'            => 'required|exists:patients,id',
            'structure_origine'     => 'required_if:sens,entrante|nullable|string|max:255',
            'structure_destination' => 'required_if:sens,sortante|nullable|string|max:255',
            'motif'                 => 'required|string',
            'soins_realises'        => 'nullable|string',
            'transport'             => ['nullable', Rule::in(array_keys(Reference::TRANSPORTS))],
            'date_reference'        => 'required|date',
        ]);

        $nomEtablissement = Etablissement::courant()->nom;
        $reference = Reference::create(array_merge($validated, [
            'numero'                => $numerotation->suivant('reference'),
            'evacuation'            => $request->boolean('evacuation'),
            'structure_origine'     => $validated['sens'] === 'entrante' ? $validated['structure_origine'] : $nomEtablissement,
            'structure_destination' => $validated['sens'] === 'sortante' ? $validated['structure_destination'] : $nomEtablissement,
            // Une référence entrante enregistrée à l'arrivée du patient est déjà reçue
            'statut'                => $validated['sens'] === 'entrante' ? 'recue' : 'emise',
            'recue_at'              => $validated['sens'] === 'entrante' ? now() : null,
            'user_id'               => Auth::id(),
        ]));

        // Le passage du jour d'un patient référé vers l'extérieur se termine par « référé »
        if ($reference->sens === 'sortante') {
            $passage = \App\Models\Passage::where('patient_id', $reference->patient_id)->ouverts()->latest('id')->first();
            if ($passage) {
                $reference->update(['passage_id' => $passage->id]);
                app(\App\Services\PassageService::class)->avancerSiPossible($passage, 'refere');
            }
        }

        return redirect()->route('references.index')->with('success', ($reference->evacuation ? 'Évacuation' : 'Référence') . ' ' . $reference->numero . ' enregistrée.');
    }

    public function etape(Request $request, Reference $reference, WorkflowService $workflow)
    {
        abort_unless(Auth::user()->can('references.gerer'), 403, 'Accès non autorisé.');
        $request->validate([
            'vers'             => 'required|in:recue,prise_en_charge,contre_reference_envoyee,annulee',
            'contre_reference' => 'required_if:vers,contre_reference_envoyee|nullable|string',
        ], ['contre_reference.required_if' => 'Rédigez la contre-référence.']);

        try {
            $workflow->exiger('reference', $reference->statut, $request->vers, Reference::STATUTS);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        $reference->update(array_filter([
            'statut'              => $request->vers,
            'recue_at'            => $request->vers === 'recue' ? now() : null,
            'contre_reference'    => $request->contre_reference,
            'contre_reference_at' => $request->vers === 'contre_reference_envoyee' ? now() : null,
        ], fn($v) => $v !== null));

        return back()->with('success', 'Référence ' . $reference->numero . ' : ' . Reference::STATUTS[$request->vers] . '.');
    }

    /** EF-11.03 : devenir du patient référé, quand l'information revient. */
    public function devenir(Request $request, Reference $reference)
    {
        abort_unless(Auth::user()->can('references.gerer'), 403, 'Accès non autorisé.');
        $request->validate(['devenir' => 'required|string|max:2000']);
        $reference->update(['devenir' => $request->devenir]);

        return back()->with('success', 'Devenir du patient enregistré.');
    }

    /** EF-11.02 : fiche de référence ou de contre-référence. */
    public function fiche(Reference $reference, string $type)
    {
        abort_unless(Auth::user()->can('references.view'), 403, 'Accès non autorisé.');
        abort_unless(in_array($type, ['reference', 'contre-reference'], true), 404);

        $reference->load(['patient', 'user']);

        return Pdf::loadView('application.reference.fiche', compact('reference', 'type'))
            ->stream(($type === 'reference' ? 'fiche_reference_' : 'contre_reference_') . $reference->numero . '.pdf');
    }
}
