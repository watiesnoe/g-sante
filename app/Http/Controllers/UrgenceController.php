<?php

namespace App\Http\Controllers;

use App\Models\Etablissement;
use App\Models\Passage;
use App\Models\Patient;
use App\Services\PassageService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Urgences (EF-10.03) : registre avec heures d'arrivée, de prise en charge et de
 * sortie, niveau de triage, décision et orientation. RG-06 : la prise en charge
 * n'est jamais conditionnée à un paiement ; la facture est régularisée ensuite.
 */
class UrgenceController extends Controller
{
    /** Décision → statut final du passage. */
    private const STATUT_PAR_DECISION = [
        'retour_domicile' => 'termine',
        'hospitalisation' => 'hospitalise',
        'reference'       => 'refere',
        'deces'           => 'sorti',
        'observation'     => 'en_consultation',
    ];

    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('urgences.view'), 403, 'Accès non autorisé.');

        $du = $request->du ? Carbon::parse($request->du) : today();
        $au = $request->au ? Carbon::parse($request->au) : today();

        $passages = Passage::with(['patient', 'ticket'])->where('urgence', true)
            ->whereBetween('arrive_at', [$du->copy()->startOfDay(), $au->copy()->endOfDay()])
            ->orderByRaw('termine_at IS NOT NULL')->orderByRaw('priorite IS NULL')->orderBy('priorite')->orderBy('arrive_at')->get();

        if ($request->format === 'csv') {
            return $this->csv($passages, $du, $au);
        }

        return view('application.urgence.index', [
            'passages' => $passages, 'du' => $du, 'au' => $au,
            'triage'   => (bool) Etablissement::courant()->parametre('circuit.triage_actif'),
        ]);
    }

    /** Arrivée aux urgences : passage ouvert sans ticket, trié immédiatement si le triage est actif. */
    public function store(Request $request, PassageService $passages)
    {
        abort_unless(Auth::user()->can('urgences.gerer'), 403, 'Accès non autorisé.');
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'motif'      => 'required|string|max:255',
            'priorite'   => 'nullable|integer|min:1|max:5',
        ]);

        $passage = $passages->ouvrir(Patient::findOrFail($validated['patient_id']), ['motif' => $validated['motif']]);
        $passage->update(['urgence' => true]);

        if (!empty($validated['priorite']) && Etablissement::courant()->parametre('circuit.triage_actif')) {
            $passages->passerA($passage, 'trie', ['priorite' => $validated['priorite']]);
        }

        return back()->with('success', 'Patient enregistré aux urgences : ' . $passage->numero . '.');
    }

    public function priseEnCharge(Passage $passage, PassageService $passages)
    {
        abort_unless(Auth::user()->can('urgences.gerer'), 403, 'Accès non autorisé.');
        try {
            $passages->passerA($passage, 'en_consultation');
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', 'Prise en charge de ' . $passage->numero . ' à ' . now()->format('H:i') . '.');
    }

    public function decision(Request $request, Passage $passage, PassageService $passages)
    {
        abort_unless(Auth::user()->can('urgences.gerer'), 403, 'Accès non autorisé.');
        $validated = $request->validate([
            'decision'    => 'required|in:' . implode(',', array_keys(Passage::DECISIONS)),
            'orientation' => 'nullable|string|max:255',
        ]);

        $passage->update($validated);
        $passages->avancerSiPossible($passage->fresh(), self::STATUT_PAR_DECISION[$validated['decision']]);

        return back()->with('success', 'Décision enregistrée pour ' . $passage->numero . '.');
    }

    private function csv($passages, Carbon $du, Carbon $au)
    {
        $nom = 'registre_urgences_' . $du->format('Ymd') . '_' . $au->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($passages) {
            $f = fopen('php://output', 'w');
            fwrite($f, "\xEF\xBB\xBF");
            fputcsv($f, ['N° passage', 'Patient', 'N° dossier', 'Arrivée', 'Prise en charge', 'Sortie', 'Délai (min)', 'Priorité', 'Motif', 'Décision', 'Orientation'], ';');
            foreach ($passages as $p) {
                fputcsv($f, [
                    $p->numero, trim(($p->patient->nom ?? '') . ' ' . ($p->patient->prenom ?? '')), $p->patient->numero_dossier ?? '',
                    $p->arrive_at?->format('d/m/Y H:i'), $p->pris_en_charge_at?->format('H:i'), $p->termine_at?->format('d/m/Y H:i'),
                    $p->pris_en_charge_at ? $p->arrive_at->diffInMinutes($p->pris_en_charge_at) : '', $p->priorite ? 'P' . $p->priorite : '',
                    $p->motif, Passage::DECISIONS[$p->decision] ?? '', $p->orientation,
                ], ';');
            }
            fclose($f);
        }, $nom, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
