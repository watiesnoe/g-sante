<?php

namespace App\Http\Controllers;

use App\Models\Grossesse;
use App\Models\ConsultationPrenatale;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MaternityController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->can('maternity.view'), 403, 'Accès non autorisé : vous n\'avez pas accès au module maternité.');

        $year = session('exercice_year', date('Y'));
        $grossesses = Grossesse::with('patient')
            ->whereYear('created_at', $year)
            ->where('statut', 'En cours')
            ->get();
        return view('application.maternity.index', compact('grossesses'));
    }

    public function create()
    {
        abort_unless(Auth::user()->can('maternity.create'), 403, 'Accès non autorisé : vous n\'avez pas la permission de créer un suivi de grossesse.');

        return view('application.maternity.create');
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('maternity.create'), 403, 'Accès non autorisé : vous n\'avez pas la permission de créer un suivi de grossesse.');

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'ddr'        => 'required|date',
            'parite'     => 'nullable|integer',
            'gestite'    => 'nullable|integer',
            'antecedents_particuliers' => 'nullable|string',
        ]);

        // Calcul DPA (Date Prévue Accouchement = DDR + 9 mois + 7 jours)
        $ddr = Carbon::parse($validated['ddr']);
        $validated['dpa'] = $ddr->addMonths(9)->addDays(7);

        Grossesse::create($validated);

        return redirect()->route('maternity.index')->with('success', 'Suivi de grossesse initialisé.');
    }

    public function show(Grossesse $grossesse)
    {
        abort_unless(Auth::user()->can('maternity.view'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les suivis de grossesse.');

        // Si le paramètre rdv_uuid est passé, marquer ce rendez-vous comme réalisé
        if (request()->filled('rdv_uuid')) {
            $rdv = \App\Models\RendezVous::where('uuid', request('rdv_uuid'))->first();
            if ($rdv && $rdv->statut !== 'realise') {
                $rdv->statut = 'realise';
                $rdv->save();
            }
        }

        $grossesse->load(['patient', 'cpns']);

        // Récupérer le médecin de la dernière consultation médicale du patient
        $dernierMedecinId = \App\Models\Consultation::where('patient_id', $grossesse->patient_id)
            ->whereNotNull('medecin_id')
            ->latest()
            ->value('medecin_id');

        // Fallback : utilisateur connecté
        $dernierMedecinId = $dernierMedecinId ?? Auth::id();

        return view('application.maternity.show', compact('grossesse', 'dernierMedecinId'));
    }

    public function storeCpn(Request $request)
    {
        abort_unless(Auth::user()->can('maternity.create'), 403, 'Accès non autorisé : vous n\'avez pas la permission de saisir une CPN.');

        $validated = $request->validate([
            'grossesse_id' => 'required|exists:grossesses,id',
            'numero_cpn'   => 'required|integer',
            'date_cpn'     => 'required|date',
            'poids'        => 'nullable|numeric',
            'tension'      => 'nullable|string',
            'hauteur_uterine' => 'nullable|integer',
            'bcf'          => 'nullable|string',
            'observations' => 'nullable|string',
            'traitement_recu' => 'nullable|string',
            'prochain_rdv' => 'nullable|date',
        ]);

        $cpn = ConsultationPrenatale::create($validated);

        // Si une date de prochain rdv est renseignée, enregistrer dans les rendez-vous
        if ($request->filled('prochain_rdv')) {
            $grossesse = Grossesse::findOrFail($validated['grossesse_id']);
            
            // Récupérer le médecin de la dernière consultation médicale du patient
            $medecinId = \App\Models\Consultation::where('patient_id', $grossesse->patient_id)
                ->whereNotNull('medecin_id')
                ->latest()
                ->value('medecin_id');

            // Fallback : utilisateur connecté
            $medecinId = $medecinId ?? Auth::id();

            \App\Models\RendezVous::create([
                'patient_id' => $grossesse->patient_id,
                'medecin_id' => $medecinId,
                'date_heure' => $request->prochain_rdv . ' 08:00:00', // Heure par défaut à 08:00
                'motif'      => 'Consultation Prénatale (CPN ' . ($validated['numero_cpn'] + 1) . ')',
                'statut'     => 'prevu',
            ]);
        }

        return back()->with('success', 'CPN enregistrée.');
    }

    public function close(Request $request, Grossesse $grossesse)
    {
        abort_unless(Auth::user()->can('maternity.close'), 403, 'Accès non autorisé : vous n\'avez pas la permission de clôturer un suivi de grossesse.');

        $validated = $request->validate([
            'statut'   => 'required|in:Terminée,Interrompue',
            'issue'    => 'required|string',
            'date_fin' => 'required|date',
        ]);

        $grossesse->update($validated);

        return redirect()->route('maternity.index')->with('success', 'Le suivi de grossesse a été clôturé avec succès.');
    }
}
