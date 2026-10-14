<?php

namespace App\Http\Controllers;

use App\Models\PrescriptionExamen;
use App\Services\LaboratoireService;
use App\Services\WorkflowService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/** Poste de travail du laboratoire et de l'imagerie (EF-09.02 à EF-09.04). */
class LaboratoireController extends Controller
{
    public function index(Request $request, WorkflowService $workflow)
    {
        abort_unless(Auth::user()->can('examens.view'), 403, 'Accès non autorisé.');

        $statut = $request->statut ?: 'en_cours';
        $examens = PrescriptionExamen::with(['consultation.patient', 'consultation.medecin', 'resultat', 'validateur'])
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            // Le prescripteur retrouve ses propres demandes (ancien écran « Analyses supplémentaires »)
            ->when($request->boolean('mes'), fn($q) => $q->whereHas('consultation', fn($c) => $c->where('medecin_id', Auth::id())))
            ->when($statut === 'en_cours', fn($q) => $q->whereIn('statut', ['demande', 'preleve', 'planifie', 'realise', 'saisi']),
                fn($q) => $q->where('statut', $statut))
            ->latest()->limit(300)->get();

        $suivants = $examens->mapWithKeys(fn($e) => [$e->id => array_diff($workflow->suivants('examen', $e->statut), ['saisi', 'valide'])]);

        return view('application.laboratoire.index', compact('examens', 'statut', 'suivants'));
    }

    public function etape(Request $request, PrescriptionExamen $examen, LaboratoireService $service)
    {
        abort_unless(Auth::user()->can('examens.results'), 403, 'Accès non autorisé.');
        $request->validate(['vers' => 'required|in:preleve,planifie,realise,annule', 'motif' => 'nullable|string|max:255', 'planifie_le' => 'nullable|date']);

        try {
            $service->passerA($examen, $request->vers, $request->only('motif', 'planifie_le'));
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', $examen->examen . ' : ' . PrescriptionExamen::STATUTS[$examen->statut] . '.');
    }

    public function valider(PrescriptionExamen $examen, LaboratoireService $service)
    {
        try {
            $service->valider($examen);
        } catch (AuthorizationException $e) {
            return back()->with('error', $e->getMessage());
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', 'Résultat validé : ' . $examen->examen . '.');
    }
}
