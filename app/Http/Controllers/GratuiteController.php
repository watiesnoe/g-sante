<?php

namespace App\Http\Controllers;

use App\Models\Medicament;
use App\Models\Prestation;
use App\Models\RegleGratuite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** EF-05.03 : règles de gratuité saisies par l'établissement d'après les textes officiels. */
class GratuiteController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->can('gratuites.gerer'), 403, 'Accès non autorisé.');

        return view('application.gratuite.index', [
            'regles'      => RegleGratuite::with(['prestations', 'medicaments'])->latest('date_effet')->get(),
            'prestations' => Prestation::orderBy('nom')->get(['id', 'nom']),
            'medicaments' => Medicament::orderBy('nom')->get(['id', 'nom']),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('gratuites.gerer'), 403, 'Accès non autorisé.');

        $validated = $request->validate([
            'nom'                => 'required|string|max:255',
            'age_max_mois'       => 'nullable|integer|min:0|max:1500',
            'genre'              => 'nullable|in:M,F',
            'grossesse_en_cours' => 'nullable|boolean',
            'portee_actes'       => 'required|in:aucun,tous,liste',
            'couvre_medicaments' => 'nullable|boolean',
            'prestations'        => 'nullable|array',
            'prestations.*'      => 'exists:prestations,id',
            'medicaments'        => 'nullable|array',
            'medicaments.*'      => 'exists:medicaments,id',
            'date_effet'         => 'required|date',
            'date_fin'           => 'nullable|date|after_or_equal:date_effet',
        ]);

        $regle = RegleGratuite::create(collect($validated)->except(['prestations', 'medicaments'])->all() + [
            'grossesse_en_cours' => $request->boolean('grossesse_en_cours'),
            'couvre_medicaments' => $request->boolean('couvre_medicaments'),
            'actif'              => true,
        ]);
        $regle->prestations()->sync($validated['portee_actes'] === 'liste' ? ($validated['prestations'] ?? []) : []);
        $regle->medicaments()->sync($regle->couvre_medicaments ? ($validated['medicaments'] ?? []) : []);

        return back()->with('success', 'Règle de gratuité « ' . $regle->nom . ' » enregistrée.');
    }

    public function basculer(RegleGratuite $regle)
    {
        abort_unless(Auth::user()->can('gratuites.gerer'), 403, 'Accès non autorisé.');
        $regle->update(['actif' => !$regle->actif]);

        return back()->with('success', 'Règle ' . ($regle->actif ? 'activée' : 'désactivée') . '.');
    }
}
