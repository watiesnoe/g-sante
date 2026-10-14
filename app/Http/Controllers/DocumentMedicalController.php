<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\DocumentMedical;
use App\Models\ModeleDocument;
use App\Models\Patient;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Certificats médicaux et lettres d'orientation à partir de modèles (EF-04.06),
 * et gestion des modèles par l'établissement.
 */
class DocumentMedicalController extends Controller
{
    public function create(Request $request)
    {
        abort_unless(Auth::user()->can('consultations.print'), 403, 'Accès non autorisé.');

        $patient = Patient::where('uuid', $request->patient)->firstOrFail();
        $consultation = $request->consultation ? Consultation::where('uuid', $request->consultation)->first() : null;
        $modeles = $this->modeles();
        $modele = $modeles->firstWhere('id', (int) $request->modele) ?? $modeles->first();

        return view('application.document.create', [
            'patient'      => $patient,
            'consultation' => $consultation,
            'modeles'      => $modeles,
            'modele'       => $modele,
            'contenu'      => $modele?->rendre($patient, $consultation, Auth::user()) ?? '',
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('consultations.print'), 403, 'Accès non autorisé.');

        $validated = $request->validate([
            'patient_id'         => 'required|exists:patients,id',
            'consultation_id'    => 'nullable|exists:consultations,id',
            'modele_document_id' => 'nullable|exists:modeles_documents,id',
            'type'               => ['required', Rule::in(array_keys(ModeleDocument::TYPES))],
            'titre'              => 'required|string|max:255',
            'destinataire'       => 'nullable|string|max:255',
            'contenu'            => 'required|string',
        ]);

        $document = DocumentMedical::create($validated + ['user_id' => Auth::id()]);

        return redirect()->route('documents.pdf', $document);
    }

    public function pdf(DocumentMedical $document)
    {
        abort_unless(Auth::user()->can('consultations.print'), 403, 'Accès non autorisé.');

        $document->load(['patient', 'user']);

        return Pdf::loadView('application.document.pdf', compact('document'))
            ->stream(\Illuminate\Support\Str::slug($document->titre) . '_' . $document->patient->numero_dossier . '.pdf');
    }

    // ── Modèles (Paramètres) ────────────────────────────────────────────────

    public function modelesIndex()
    {
        abort_unless(Auth::user()->can('parametres.etablissement'), 403, 'Accès non autorisé.');

        return view('application.document.modeles', ['modeles' => $this->modeles(false)]);
    }

    public function modeleEnregistrer(Request $request)
    {
        abort_unless(Auth::user()->can('parametres.etablissement'), 403, 'Accès non autorisé.');

        $validated = $request->validate([
            'id'      => 'nullable|exists:modeles_documents,id',
            'type'    => ['required', Rule::in(array_keys(ModeleDocument::TYPES))],
            'titre'   => 'required|string|max:255',
            'contenu' => 'required|string',
            'actif'   => 'nullable|boolean',
        ]);
        $validated['actif'] = $request->boolean('actif', true);

        $modele = isset($validated['id']) ? ModeleDocument::findOrFail($validated['id']) : new ModeleDocument();
        $modele->fill($validated)->save();

        return back()->with('success', 'Modèle enregistré.');
    }

    /** Modèles de l'établissement ; les modèles de départ sont créés au premier usage. */
    private function modeles(bool $actifsSeulement = true)
    {
        if (!ModeleDocument::exists()) {
            foreach (ModeleDocument::modelesParDefaut() as $defaut) {
                ModeleDocument::create($defaut);
            }
        }

        return ModeleDocument::when($actifsSeulement, fn($q) => $q->where('actif', true))->orderBy('type')->orderBy('titre')->get();
    }
}
