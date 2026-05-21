<?php

namespace App\Http\Controllers;

use App\Models\Grossesse;
use App\Models\ConsultationPrenatale;
use App\Models\Patient;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MaternityController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can('maternity.view'), 403, 'Accès non autorisé : vous n\'avez pas accès au module maternité.');

        $year = session('exercice_year', date('Y'));
        $grossesses = Grossesse::with('patient')
            ->whereYear('created_at', $year)
            ->where('statut', 'En cours')
            ->get();
        return view('application.maternity.index', compact('grossesses'));
    }

    public function create()
    {
        // On ne liste que les femmes
        $patients = Patient::where('genre', 'F')->get();
        return view('application.maternity.create', compact('patients'));
    }

    public function store(Request $request)
    {
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
        abort_unless(auth()->user()->can('maternity.view'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les suivis de grossesse.');

        $grossesse->load(['patient', 'cpns']);
        return view('application.maternity.show', compact('grossesse'));
    }

    public function storeCpn(Request $request)
    {
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

        ConsultationPrenatale::create($validated);

        return back()->with('success', 'CPN enregistrée.');
    }

    public function close(Request $request, Grossesse $grossesse)
    {
        $validated = $request->validate([
            'statut'   => 'required|in:Terminée,Interrompue',
            'issue'    => 'required|string',
            'date_fin' => 'required|date',
        ]);

        $grossesse->update($validated);

        return redirect()->route('maternity.index')->with('success', 'Le suivi de grossesse a été clôturé avec succès.');
    }
}
