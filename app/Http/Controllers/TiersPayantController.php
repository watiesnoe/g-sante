<?php

namespace App\Http\Controllers;

use App\Models\Assurance;
use App\Models\Bordereau;
use App\Models\Convention;
use App\Models\Prestation;
use App\Services\TiersPayantService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/** Tiers payants : conventions, bordereaux, recouvrements et créances (EF-07.01 à EF-07.05). */
class TiersPayantController extends Controller
{
    public function conventions()
    {
        abort_unless(Auth::user()->can('tiers_payants.conventions'), 403, 'Accès non autorisé.');

        return view('application.tiers_payant.conventions', [
            'conventions' => Convention::with(['assurance', 'prestations'])->orderBy('assurance_id')->orderByDesc('date_effet')->get(),
            'assurances'  => Assurance::orderBy('nom')->get(),
            'prestations' => Prestation::orderBy('nom')->get(['id', 'nom']),
        ]);
    }

    public function enregistrerConvention(Request $request)
    {
        abort_unless(Auth::user()->can('tiers_payants.conventions'), 403, 'Accès non autorisé.');

        $validated = $request->validate([
            'assurance_id'        => 'required|exists:assurances,id',
            'taux'                => 'required|numeric|min:0|max:100',
            'plafond_par_facture' => 'nullable|numeric|min:0',
            'couvre_medicaments'  => 'nullable|boolean',
            'couverture_actes'    => 'required|in:toutes,liste',
            'prestations'         => 'nullable|array',
            'prestations.*'       => 'exists:prestations,id',
            'date_effet'          => 'required|date',
            'date_fin'            => 'nullable|date|after_or_equal:date_effet',
        ]);
        $validated['couvre_medicaments'] = $request->boolean('couvre_medicaments');

        // Une nouvelle convention ne modifie pas les factures passées : on ferme la précédente (RG-07)
        Convention::where('assurance_id', $validated['assurance_id'])->whereNull('date_fin')
            ->whereDate('date_effet', '<', $validated['date_effet'])
            ->update(['date_fin' => Carbon::parse($validated['date_effet'])->subDay()]);

        $convention = Convention::create(collect($validated)->except('prestations')->all());
        $convention->prestations()->sync($validated['couverture_actes'] === 'liste' ? ($validated['prestations'] ?? []) : []);

        return back()->with('success', 'Convention enregistrée à partir du ' . Carbon::parse($validated['date_effet'])->format('d/m/Y') . '.');
    }

    public function bordereaux(TiersPayantService $service)
    {
        abort_unless(Auth::user()->can('tiers_payants.bordereaux'), 403, 'Accès non autorisé.');

        return view('application.tiers_payant.bordereaux', [
            'bordereaux' => Bordereau::with('assurance')->latest()->get(),
            'assurances' => Assurance::orderBy('nom')->get(),
            'creances'   => $service->creances(),
        ]);
    }

    public function genererBordereau(Request $request, TiersPayantService $service)
    {
        abort_unless(Auth::user()->can('tiers_payants.bordereaux'), 403, 'Accès non autorisé.');

        $validated = $request->validate([
            'assurance_id' => 'required|exists:assurances,id',
            'du'           => 'required|date',
            'au'           => 'required|date|after_or_equal:du',
        ]);

        try {
            $bordereau = $service->genererBordereau(Assurance::findOrFail($validated['assurance_id']), Carbon::parse($validated['du']), Carbon::parse($validated['au']));
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return redirect()->route('bordereaux.show', $bordereau)->with('success', 'Bordereau ' . $bordereau->numero . ' émis.');
    }

    public function show(Bordereau $bordereau)
    {
        abort_unless(Auth::user()->can('tiers_payants.bordereaux'), 403, 'Accès non autorisé.');

        return view('application.tiers_payant.bordereau', ['bordereau' => $bordereau->load(['assurance', 'lignes.patient', 'recouvrements', 'user'])]);
    }

    public function pdf(Bordereau $bordereau)
    {
        abort_unless(Auth::user()->can('tiers_payants.bordereaux'), 403, 'Accès non autorisé.');

        return Pdf::loadView('application.tiers_payant.bordereau_pdf', ['bordereau' => $bordereau->load(['assurance', 'lignes.patient'])])
            ->setPaper('a4', 'landscape')->download('bordereau_' . $bordereau->numero . '.pdf');
    }

    public function recouvrer(Request $request, Bordereau $bordereau, TiersPayantService $service)
    {
        abort_unless(Auth::user()->can('tiers_payants.bordereaux'), 403, 'Accès non autorisé.');

        $validated = $request->validate([
            'montant'           => 'required|numeric|min:1',
            'date_recouvrement' => 'required|date',
            'mode'              => 'required|in:virement,cheque,especes,mobile',
            'reference'         => 'nullable|string|max:100',
        ]);

        try {
            $service->recouvrer($bordereau, (float) $validated['montant'], $validated['date_recouvrement'], $validated['mode'], $validated['reference'] ?? null);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', 'Recouvrement enregistré.');
    }
}
