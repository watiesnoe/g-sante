<?php

namespace App\Http\Controllers;

use App\Models\Hospitalisation;
use App\Models\Intervention;
use App\Models\Medicament;
use App\Models\Patient;
use App\Models\Prestation;
use App\Models\Salle;
use App\Models\User;
use App\Services\BlocService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/** Bloc opératoire : programme, déroulé, compte rendu et consommables (EF-10.05). */
class BlocController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('bloc.view'), 403, 'Accès non autorisé.');
        $jour = $request->date ? Carbon::parse($request->date) : today();

        return view('application.bloc.index', [
            'jour'          => $jour,
            'interventions' => Intervention::with(['patient', 'salle', 'chirurgien'])->whereDate('debut_prevu', $jour)->orderBy('debut_prevu')->get(),
            'salles'        => Salle::whereIn('type', Salle::TYPES_BLOC)->orderBy('nom')->get(),
            'chirurgiens'   => User::deLEtablissement()->where('statut', 'actif')->orderBy('name')->get(['id', 'name']),
            'prestations'   => Prestation::orderBy('nom')->get(['id', 'nom']),
        ]);
    }

    public function store(Request $request, BlocService $bloc)
    {
        abort_unless(Auth::user()->can('bloc.programmer'), 403, 'Accès non autorisé.');
        $validated = $request->validate([
            'patient_id'       => 'required|exists:patients,id',
            'salle_id'         => 'required|exists:salles,id',
            'prestation_id'    => 'nullable|exists:prestations,id',
            'libelle'          => 'required|string|max:255',
            'urgence'          => 'required|in:programmee,urgente',
            'debut_prevu'      => 'required|date',
            'duree_prevue_min' => 'required|integer|min:10|max:1440',
            'chirurgien_id'    => 'required|exists:users,id',
            'anesthesiste_id'  => 'nullable|exists:users,id|different:chirurgien_id',
            'type_anesthesie'  => 'nullable|string|max:30',
        ]);

        // Rattachement au séjour en cours du patient, s'il y en a un
        $validated['hospitalisation_id'] = Hospitalisation::where('patient_id', $validated['patient_id'])->where('etat', 'en cours')->value('id');

        try {
            $intervention = $bloc->programmer($validated);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('bloc.show', $intervention)->with('success', 'Intervention programmée.');
    }

    public function show(Intervention $intervention)
    {
        abort_unless(Auth::user()->can('bloc.view'), 403, 'Accès non autorisé.');

        return view('application.bloc.show', [
            'intervention' => $intervention->load(['patient', 'salle', 'chirurgien', 'anesthesiste', 'prestation', 'consommables.medicament', 'equipe']),
            'medicaments'  => Medicament::where('stock', '>', 0)->orderBy('nom')->get(['id', 'nom', 'stock']),
        ]);
    }

    public function action(Request $request, Intervention $intervention, string $action, BlocService $bloc)
    {
        abort_unless(Auth::user()->can('bloc.programmer'), 403, 'Accès non autorisé.');

        try {
            match ($action) {
                'demarrer'  => $bloc->demarrer($intervention),
                'terminer'  => $bloc->terminer($intervention, (string) $request->compte_rendu),
                'annuler'   => $bloc->annuler($intervention, (string) $request->motif),
                'consommer' => $bloc->consommer($intervention, Medicament::findOrFail($request->medicament_id), max(1, (int) $request->quantite)),
                default     => abort(404),
            };
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        } catch (\App\Exceptions\StockInsuffisantException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Intervention : ' . Intervention::STATUTS[$intervention->fresh()->statut] . '.');
    }
}
