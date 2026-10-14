<?php

namespace App\Http\Controllers;

use App\Models\Etablissement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EtablissementController extends Controller
{
    /**
     * Formulaire de configuration de l'établissement
     */
    public function edit()
    {
        abort_unless(Auth::user()->can('parametres.etablissement'), 403, 'Accès non autorisé : vous n\'avez pas la permission de configurer l\'établissement.');

        return view('application.parametre.etablissement', [
            'etablissement' => Etablissement::courant(),
            'types'         => Etablissement::TYPES,
            'modules'       => Etablissement::MODULES,
            'profils'       => Etablissement::PROFILS,
        ]);
    }

    /**
     * Enregistrer la configuration de l'établissement
     */
    public function update(Request $request)
    {
        abort_unless(Auth::user()->can('parametres.etablissement'), 403, 'Accès non autorisé : vous n\'avez pas la permission de configurer l\'établissement.');

        $validated = $request->validate([
            'type'      => ['required', Rule::in(array_keys(Etablissement::TYPES))],
            'nom'       => 'required|string|max:255',
            'slogan'    => 'nullable|string|max:255',
            'adresse'   => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:255',
            'email'     => 'nullable|email|max:255',
            'site_web'  => 'nullable|string|max:255',
            'logo'      => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'modules'   => 'nullable|array',
            'modules.*' => [Rule::in(array_keys(Etablissement::MODULES))],
        ]);

        $etablissement = Etablissement::query()->first() ?? new Etablissement();

        if ($request->hasFile('logo')) {
            $fichier = 'etablissement.' . $request->file('logo')->getClientOriginalExtension();
            $request->file('logo')->move(public_path('image/logo'), $fichier);
            $validated['logo'] = 'image/logo/' . $fichier;
        } else {
            unset($validated['logo']);
        }

        $validated['modules'] = array_values($validated['modules'] ?? []);
        $etablissement->fill($validated)->save();

        return redirect()->route('etablissement.edit')->with('success', 'Configuration de l\'établissement enregistrée.');
    }
}
