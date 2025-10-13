<?php

namespace App\Http\Controllers;

use App\Models\Etablissement;
use App\Models\Passage;
use App\Models\Patient;
use App\Services\PassageService;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

/**
 * Accueil : arrivées du jour, file d'attente et parcours du patient (EF-03.01 à EF-03.03).
 */
class PassageController extends Controller
{
    public function index(Request $request, WorkflowService $workflow)
    {
        abort_unless(Auth::user()->can('accueil.view'), 403, 'Accès non autorisé : vous n\'avez pas accès à l\'accueil.');

        $triage = (bool) Etablissement::courant()->parametre('circuit.triage_actif');
        $paiementApres = Etablissement::courant()->parametre('paiement.moment') === 'apres';

        if ($request->ajax()) {
            $passages = Passage::with(['patient', 'service'])
                ->whereDate('arrive_at', $request->date ?: today())
                ->when($request->statut === 'ouverts', fn($q) => $q->ouverts())
                ->when($request->service_id, fn($q) => $q->where('service_medical_id', $request->service_id))
                // EF-03.03 : file ordonnée par priorité quand le triage est actif, sinon par arrivée
                ->when($triage, fn($q) => $q->orderByRaw('priorite IS NULL')->orderBy('priorite'))
                ->orderBy('arrive_at')
                ->select('passages.*');

            return DataTables::of($passages)
                ->addColumn('patient', fn($p) => e(mb_strtoupper($p->patient->nom ?? '') . ' ' . ($p->patient->prenom ?? '')) . '<div class="small text-muted">' . e($p->patient->numero_dossier ?? '') . '</div>')
                ->addColumn('service', fn($p) => e($p->service->nom ?? '-'))
                ->editColumn('arrive_at', fn($p) => $p->arrive_at->format('H:i'))
                ->addColumn('attente', fn($p) => $p->estOuvert() && !$p->pris_en_charge_at ? $p->arrive_at->diffForHumans(null, true) : '-')
                ->editColumn('priorite', fn($p) => $p->priorite ? '<span class="badge bg-' . ([1 => 'danger', 2 => 'warning', 3 => 'info'][$p->priorite] ?? 'secondary') . '">P' . $p->priorite . '</span>' : '-')
                ->editColumn('statut', fn($p) => '<span class="badge bg-' . (Passage::COULEURS[$p->statut] ?? 'secondary') . '">' . (Passage::STATUTS[$p->statut] ?? $p->statut) . '</span>')
                ->addColumn('actions', function ($p) use ($workflow, $paiementApres) {
                    $raccourcis = '';
                    // Cabinet (paiement après) : on consulte d'abord, on encaisse à la sortie
                    if ($p->estOuvert() && Auth::user()->can('consultations.create')) {
                        $raccourcis .= '<a href="' . route('consultations.create', ['patient_id' => $p->patient_id]) . '" class="btn btn-sm btn-primary"><i class="fa fa-stethoscope"></i> Consulter</a>';
                    }
                    if ($paiementApres && !$p->ticket_id && in_array($p->statut, ['en_consultation', 'termine'], true) && Auth::user()->can('tickets.create')) {
                        $raccourcis .= '<a href="' . route('tickets.create', ['patient' => $p->patient->uuid]) . '" class="btn btn-sm btn-success"><i class="fa fa-cash-register"></i> Encaisser</a>';
                    }
                    if (!$p->estOuvert() || !Auth::user()->can('accueil.orienter')) {
                        return '<div class="d-flex flex-wrap gap-1 justify-content-center">' . $raccourcis . '</div>';
                    }
                    $boutons = collect($workflow->suivants('passage', $p->statut))->map(fn($vers) => '<button type="button" class="btn btn-sm btn-outline-' . (Passage::COULEURS[$vers] ?? 'secondary') . ' btn-statut" data-url="' . route('passages.statut', $p) . '" data-vers="' . $vers . '">' . (Passage::STATUTS[$vers] ?? $vers) . '</button>');

                    return '<div class="d-flex flex-wrap gap-1 justify-content-center">' . $raccourcis . $boutons->implode('') . '</div>';
                })
                ->rawColumns(['patient', 'priorite', 'statut', 'actions'])
                ->make(true);
        }

        $services = \App\Models\ServiceMedical::orderBy('nom')->get(['id', 'nom']);
        $statsJour = Passage::whereDate('arrive_at', today())->selectRaw('statut, COUNT(*) as total')->groupBy('statut')->pluck('total', 'statut');

        return view('application.accueil.index', compact('services', 'triage', 'statsJour'));
    }

    /** Enregistrer l'arrivée d'un patient (EF-03.01). */
    public function store(Request $request, PassageService $service)
    {
        abort_unless(Auth::user()->can('accueil.create'), 403, 'Accès non autorisé.');

        $validated = $request->validate([
            'patient_id'         => 'required|exists:patients,id',
            'service_medical_id' => 'nullable|exists:service_medicals,id',
            'motif'              => 'nullable|string|max:255',
        ]);

        $patient = Patient::findOrFail($validated['patient_id']);
        $passage = $service->ouvrir($patient, $validated);

        return response()->json(['success' => true, 'message' => 'Arrivée enregistrée : passage ' . $passage->numero . '.', 'numero' => $passage->numero]);
    }

    /** Faire avancer un passage selon le workflow actif (EF-03.02). */
    public function changerStatut(Request $request, Passage $passage, PassageService $service)
    {
        abort_unless(Auth::user()->can('accueil.orienter'), 403, 'Accès non autorisé.');

        $validated = $request->validate([
            'vers'     => 'required|string|in:' . implode(',', array_keys(Passage::STATUTS)),
            'priorite' => 'nullable|integer|min:1|max:5',
        ]);

        if ($validated['vers'] === 'trie' && empty($validated['priorite'])) {
            return response()->json(['success' => false, 'message' => 'Indiquez le niveau de priorité du triage.'], 422);
        }

        $service->passerA($passage, $validated['vers'], $validated);

        return response()->json(['success' => true, 'message' => 'Passage ' . $passage->numero . ' : ' . Passage::STATUTS[$validated['vers']] . '.']);
    }
}
