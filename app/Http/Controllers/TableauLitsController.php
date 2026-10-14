<?php

namespace App\Http\Controllers;

use App\Models\Hospitalisation;
use App\Models\Lit;
use App\Models\OccupationLit;
use App\Models\Salle;
use App\Services\HospitalisationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/** EF-10.02 : état des lits en temps réel ; transferts de lit (EF-10.01). */
class TableauLitsController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->can('hospitalisations.view'), 403, 'Accès non autorisé.');

        // Regroupement service → unité → chambre (EF-10.02, paramètre hospitalisation.niveaux)
        $salles = Salle::with(['lits' => fn($q) => $q->orderBy('numero'), 'uniteSoins', 'service'])
            ->whereNotIn('type', Salle::TYPES_BLOC)->get()
            ->sortBy(fn($s) => ($s->service->nom ?? '') . '|' . ($s->uniteSoins->nom ?? '') . '|' . $s->nom)->values();
        $unites = \App\Models\UniteSoins::with('service')->orderBy('nom')->get();
        $occupations = OccupationLit::with('hospitalisation.patient', 'hospitalisation.consultation.patient')->whereNull('fin')->get()->keyBy('lit_id');
        $litsLibres = Lit::where('statut', 'Libre')->orderBy('numero')->get(['id', 'numero', 'salle_id']);
        $compte = Lit::selectRaw('statut, COUNT(*) as total')->groupBy('statut')->pluck('total', 'statut');

        $multiNiveaux = \App\Models\Etablissement::courant()->parametre('hospitalisation.niveaux') === 'service_unite_chambre_lit';
        $services = \App\Models\ServiceMedical::orderBy('nom')->get(['id', 'nom']);

        return view('application.hospitalisation.lits', compact('salles', 'occupations', 'litsLibres', 'compte', 'unites', 'multiNiveaux', 'services'));
    }

    public function creerUnite(Request $request)
    {
        abort_unless(Auth::user()->can('parametres.lits') || Auth::user()->can('parametres.salles'), 403, 'Accès non autorisé.');
        $request->validate(['nom' => 'required|string|max:100', 'service_medical_id' => 'required|exists:service_medicals,id']);
        \App\Models\UniteSoins::create($request->only('nom', 'service_medical_id'));

        return back()->with('success', 'Unité de soins créée.');
    }

    public function affecterUnite(Request $request, Salle $salle)
    {
        abort_unless(Auth::user()->can('parametres.lits') || Auth::user()->can('parametres.salles'), 403, 'Accès non autorisé.');
        $request->validate(['unite_soins_id' => 'nullable|exists:unites_soins,id']);
        $salle->update(['unite_soins_id' => $request->unite_soins_id ?: null]);

        return back()->with('success', 'Chambre rattachée à l\'unité.');
    }

    /** Réserver, mettre en maintenance ou libérer un lit qui n'a pas de séjour en cours. */
    public function statut(Request $request, Lit $lit)
    {
        abort_unless(Auth::user()->can('hospitalisations.edit') || Auth::user()->can('parametres.lits'), 403, 'Accès non autorisé.');
        $request->validate(['statut' => 'required|in:Libre,Réservé,Maintenance']);

        if (OccupationLit::where('lit_id', $lit->id)->whereNull('fin')->exists()) {
            return back()->with('error', "Le lit {$lit->numero} est occupé par un séjour en cours : faites sortir ou transférer le patient.");
        }

        $lit->update(['statut' => $request->statut]);

        return back()->with('success', "Lit {$lit->numero} : {$request->statut}.");
    }

    public function transferer(Request $request, Hospitalisation $hospitalisation, HospitalisationService $service)
    {
        abort_unless(Auth::user()->can('hospitalisations.edit'), 403, 'Accès non autorisé.');
        $request->validate(['lit_id' => 'required|exists:lits,id']);

        try {
            $service->transferer($hospitalisation, Lit::findOrFail($request->lit_id));
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', 'Patient transféré.');
    }
}
