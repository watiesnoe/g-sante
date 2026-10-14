<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\PrescriptionExamen;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrescriptionExamenController extends Controller
{
    /**
     * Liste des examens prescrits, regroupés par consultation (un patient peut
     * avoir plusieurs examens prescrits lors d'une même consultation : ils
     * doivent apparaître ensemble, pas comme des lignes indépendantes).
     */
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('examens.view'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les examens.');

        if ($request->ajax()) {
            $year = session('exercice_year', date('Y'));
            $user = Auth::user();

            $consultations = Consultation::with(['patient', 'medecin', 'examens'])
                ->whereHas('examens', function ($q) {
                    $q->whereNotIn('statut', ['saisi', 'valide', 'annule']); // ✅ au moins un examen sans résultat
                })
                ->whereYear('date_consultation', $year)
                ->when(!$user->hasRole(['super_admin', 'superadmin', 'admin']), function ($query) use ($user) {
                    $query->where('medecin_id', $user->id);
                })
                ->latest('date_consultation');

            return datatables()->of($consultations)
                ->addColumn('patient', fn($c) => optional($c->patient)->nom . ' ' . optional($c->patient)->prenom)
                ->addColumn('date', fn($c) => $c->date_consultation ? \Carbon\Carbon::parse($c->date_consultation)->format('d-m-Y H:i') : '-')
                ->addColumn('examens', function ($c) {
                    $user = Auth::user();
                    $items = $c->examens->whereNotIn('statut', ['saisi', 'valide', 'annule'])->map(function ($e) use ($user) {
                        $actions = '';
                        // Modification possible uniquement tant qu'aucun résultat n'a été saisi
                        // (le statut passe à "saisi" dès qu'un résultat est enregistré, voir
                        // ResultatExamenController::store()) — au-delà, l'examen fait partie du
                        // dossier médical du patient et ne doit plus être altéré.
                        if ($user->can('examens.edit')) {
                            $actions .= '<button type="button" data-url="' . route('prescriptions.update', $e->uuid) . '" data-examen="' . e($e->examen) . '" data-notes="' . e($e->notes) . '" class="btn btn-sm btn-outline-info me-1 btn-edit-examen" title="Modifier la prescription"><i class="fa fa-pencil-alt"></i></button>';
                        }
                        if ($user->can('examens.results')) {
                            $actions .= '<a href="' . route('reponse.create', $e->id) . '" class="btn btn-sm btn-outline-primary me-1" title="Saisir le résultat"><i class="fa fa-vials"></i> Résultat</a>';
                        }
                        if ($user->can('examens.delete')) {
                            $actions .= '<button type="button" data-url="' . route('prescriptions.destroy', $e->uuid) . '" class="btn btn-sm btn-outline-danger btn-delete-examen" title="Supprimer"><i class="fa fa-trash"></i></button>';
                        }

                        return '<div class="d-flex justify-content-between align-items-center border-bottom py-1">
                                    <span>' . e($e->examen) . ($e->notes ? ' <small class="text-muted">(' . e($e->notes) . ')</small>' : '') . '</span>
                                    <span class="ms-2 text-nowrap">' . $actions . '</span>
                                </div>';
                    });

                    return $items->implode('');
                })
                ->addColumn('actions', function ($c) {
                    $html = '<a href="' . route('prescriptions.pdf', $c->uuid) . '" target="_blank" class="btn btn-sm btn-outline-secondary" title="Imprimer tous les examens de cette consultation"><i class="fa fa-print"></i> Imprimer</a>';

                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $html . '</div>';
                })
                ->rawColumns(['examens', 'actions'])
                ->make(true);
        }

        return view('application.examen.listeprescription');
    }

    /**
     * Génère un bon d'examens unique regroupant tous les examens prescrits
     * lors de cette consultation (et pas seulement ceux encore en attente :
     * le document imprimé doit refléter tout ce qui a été prescrit).
     */
    public function pdf(Consultation $consultation)
    {
        abort_unless(Auth::user()->can('examens.view'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les examens.');

        $consultation->load(['patient', 'medecin', 'examens']);
        abort_if($consultation->examens->isEmpty(), 404, 'Aucun examen prescrit pour cette consultation.');

        $pdf = Pdf::loadView('application.examen.pdf', [
            'consultation' => $consultation,
            'patient'      => $consultation->patient,
            'medecin'      => $consultation->medecin,
            'examens'      => $consultation->examens,
        ]);

        return $pdf->download('examens_' . $consultation->uuid . '.pdf');
    }


    // Créer
    public function create()
    {
        abort_unless(Auth::user()->can('examens.create'), 403, 'Accès non autorisé : vous n\'avez pas la permission de prescrire un examen.');

        return view('application.examens.create');
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('examens.create'), 403, 'Accès non autorisé : vous n\'avez pas la permission de prescrire un examen.');

        $validated = $request->validate([
            'consultation_id' => 'required|exists:consultations,id',
            'examen' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        PrescriptionExamen::create($validated);

        return redirect()->route('examens.index')->with('success', 'Examen prescrit avec succès.');
    }

    // Modifier (uniquement tant qu'aucun résultat n'a été saisi)
    public function edit(PrescriptionExamen $examen)
    {
        abort_unless(Auth::user()->can('examens.edit'), 403, 'Accès non autorisé : vous n\'avez pas la permission de modifier un examen.');
        abort_if(in_array($examen->statut, ['realise', 'saisi', 'valide', 'annule'], true), 403, 'Le résultat de cet examen a déjà été saisi : la prescription ne peut plus être modifiée.');

        return response()->json([
            'id'     => $examen->id,
            'examen' => $examen->examen,
            'notes'  => $examen->notes,
        ]);
    }

    public function update(Request $request, PrescriptionExamen $examen)
    {
        abort_unless(Auth::user()->can('examens.edit'), 403, 'Accès non autorisé : vous n\'avez pas la permission de modifier un examen.');
        abort_if(in_array($examen->statut, ['realise', 'saisi', 'valide', 'annule'], true), 403, 'Le résultat de cet examen a déjà été saisi : la prescription ne peut plus être modifiée.');

        $validated = $request->validate([
            'examen' => 'required|string|max:255',
            'notes'  => 'nullable|string',
        ]);

        $examen->update($validated);

        return response()->json(['success' => true, 'message' => 'Examen modifié avec succès.']);
    }

    // Supprimer
    public function destroy(PrescriptionExamen $examen)
    {
        abort_unless(Auth::user()->can('examens.delete'), 403, 'Accès non autorisé : vous n\'avez pas la permission de supprimer un examen.');

        $examen->delete();
        return response()->json(['success' => true]);
    }
}
