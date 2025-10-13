<?php

namespace App\Http\Controllers;

use App\Models\Etablissement;
use App\Models\Formule;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Plateforme : formules d'abonnement (noms, modules, limites, prix), définies par le super-admin. */
class FormuleController extends Controller
{
    public function index()
    {
        return view('application.plateforme.formules', [
            'formules' => Formule::withCount(['abonnements as abonnes_count' => fn($q) => $q->where('statut', '!=', 'termine')])->orderBy('ordre')->orderBy('nom')->get(),
            'modules'  => Etablissement::MODULES,
            'proteges' => Formule::MODULES_PROTEGES,
            'edition'  => request('modifier') ? Formule::where('uuid', request('modifier'))->first() : null,
        ]);
    }

    public function store(Request $request)
    {
        Formule::create($this->valider($request));

        return redirect()->route('plateforme.formules')->with('success', 'Formule « ' . $request->nom . ' » créée.');
    }

    public function update(Request $request, Formule $formule)
    {
        $formule->update($this->valider($request, $formule));

        return redirect()->route('plateforme.formules')->with('success', 'Formule « ' . $formule->nom . ' » enregistrée. Les espaces abonnés gardent leurs modules jusqu\'à leur prochain changement de formule.');
    }

    private function valider(Request $request, ?Formule $formule = null): array
    {
        $validated = $request->validate([
            'nom'              => ['required', 'string', 'max:60', Rule::unique('formules', 'nom')->ignore($formule?->id)],
            'description'      => 'nullable|string|max:255',
            'prix'             => 'nullable|numeric|min:0',
            'duree_mois'       => 'required|integer|min:1|max:60',
            'modules'          => 'required|array|min:1',
            'modules.*'        => [Rule::in(array_keys(Etablissement::MODULES))],
            'modules_coupes'   => 'nullable|array',
            'modules_coupes.*' => [Rule::in(array_keys(Etablissement::MODULES)), Rule::notIn(Formule::MODULES_PROTEGES)],
            'max_utilisateurs' => 'nullable|integer|min:1',
            'max_lits'         => 'nullable|integer|min:0',
            'max_sms_mois'     => 'nullable|integer|min:0',
            'jours_grace'      => 'required|integer|min:0|max:90',
            'ordre'            => 'nullable|integer|min:0',
        ], [
            'modules.required'        => 'Cochez au moins un module.',
            'modules_coupes.*.not_in' => 'Les urgences, l\'hospitalisation et la maternité ne peuvent pas être coupées : des patients peuvent être en soins.',
        ]);

        return array_merge($validated, [
            // On ne coupe qu'un module que la formule accorde
            'modules_coupes' => array_values(array_intersect($validated['modules_coupes'] ?? [], $validated['modules'])),
            'actif'          => $request->boolean('actif', true),
            'ordre'          => $validated['ordre'] ?? 0,
        ]);
    }
}
