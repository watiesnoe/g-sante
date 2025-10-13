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
    /**
     * Le propriétaire n'édite que l'identité de son établissement. Le type, les
     * modules, les règles de fonctionnement et le serveur en ligne sont gérés
     * par l'administration de la plateforme (PlateformeController::configuration).
     */
    public function edit()
    {
        abort_unless(Auth::user()->can('parametres.etablissement'), 403, 'Accès non autorisé : vous n\'avez pas la permission de configurer l\'établissement.');

        return view('application.parametre.etablissement', [
            'etablissement' => Etablissement::courant(),
            'types'         => Etablissement::TYPES,
            'modules'       => Etablissement::MODULES,
        ]);
    }

    public function update(Request $request)
    {
        abort_unless(Auth::user()->can('parametres.etablissement'), 403, 'Accès non autorisé : vous n\'avez pas la permission de configurer l\'établissement.');

        $validated = $request->validate([
            'nom'       => 'required|string|max:255',
            'slogan'    => 'nullable|string|max:255',
            'adresse'   => 'nullable|string|max:255',
            'region'    => 'nullable|string|max:255',
            'district'  => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:255',
            'email'     => 'nullable|email|max:255',
            'site_web'  => 'nullable|string|max:255',
            'logo'      => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        $etablissement = Etablissement::courant();
        if ($request->hasFile('logo')) {
            $fichier = 'etablissement-' . $etablissement->id . '.' . $request->file('logo')->getClientOriginalExtension();
            $request->file('logo')->move(public_path('image/logo'), $fichier);
            $validated['logo'] = 'image/logo/' . $fichier;
        } else {
            unset($validated['logo']);
        }
        $etablissement->fill($validated + ['configure' => true])->save();

        return redirect()->route('etablissement.edit')->with('success', 'Informations de l\'établissement enregistrées.');
    }

    /**
     * Changer d'établissement de travail (parmi ceux affectés à l'utilisateur)
     */
    public function changer(Request $request)
    {
        $id = (int) $request->input('etablissement_id');
        abort_unless(Auth::user()->etablissementsAccessibles()->contains('id', $id), 403, 'Vous n\'êtes pas affecté à cet établissement.');

        $request->session()->put(Etablissement::SESSION, $id);
        Etablissement::oublierCourant();

        return redirect()->route('dashboard')->with('success', 'Vous travaillez maintenant dans : ' . Etablissement::courant()->nom);
    }

    /**
     * Liste des établissements (super-administration)
     */
    public function index()
    {
        abort_unless(Auth::user()->can('etablissements.gerer'), 403, 'Accès non autorisé.');

        $etablissements = Etablissement::query()->withCount('utilisateurs')->orderBy('nom')->get();

        return view('application.parametre.etablissements', compact('etablissements'));
    }

    public function create()
    {
        abort_unless(Auth::user()->can('etablissements.gerer'), 403, 'Accès non autorisé.');

        return view('application.parametre.etablissement_creer', [
            'types'   => Etablissement::TYPES,
            'modeles' => Etablissement::query()->orderBy('nom')->get(),
        ]);
    }

    /**
     * Créer un établissement avec la configuration de son profil (EF-01.01, REC-01)
     */
    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('etablissements.gerer'), 403, 'Accès non autorisé.');

        $validated = $request->validate([
            'type'      => ['required', Rule::in(array_keys(Etablissement::TYPES))],
            'nom'       => 'required|string|max:255',
            'code'      => 'nullable|string|max:20|alpha_dash|unique:etablissements,code',
            'adresse'   => 'nullable|string|max:255',
            'region'    => 'nullable|string|max:255',
            'district'  => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:255',
            'email'     => 'nullable|email|max:255',
            'modele_id' => 'nullable|exists:etablissements,id',
        ]);

        $modele = isset($validated['modele_id']) ? Etablissement::find($validated['modele_id']) : null;
        unset($validated['modele_id']);

        $etablissement = app(\App\Services\EtablissementService::class)->creer($validated, $modele);

        return redirect()->route('etablissements.index')->with('success', "Établissement « {$etablissement->nom} » créé avec le profil " . Etablissement::TYPES[$etablissement->type] . '.');
    }

}
