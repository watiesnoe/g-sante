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
            'parametres'    => Etablissement::PARAMETRES,
            'historique'    => $this->historique(Etablissement::courant()),
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
            'region'    => 'nullable|string|max:255',
            'district'  => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:255',
            'email'     => 'nullable|email|max:255',
            'site_web'  => 'nullable|string|max:255',
            'logo'      => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'modules'   => 'nullable|array',
            'modules.*' => [Rule::in(array_keys(Etablissement::MODULES))],
        ]);

        $etablissement = Etablissement::courant();

        if ($request->hasFile('logo')) {
            $fichier = 'etablissement.' . $request->file('logo')->getClientOriginalExtension();
            $request->file('logo')->move(public_path('image/logo'), $fichier);
            $validated['logo'] = 'image/logo/' . $fichier;
        } else {
            unset($validated['logo']);
        }

        $validated['modules'] = array_values($validated['modules'] ?? []);
        $validated['configure'] = true;

        // Paramètres modifiables (les paramètres « fixes » sont ignorés)
        $validated['parametres'] = collect(Etablissement::PARAMETRES)
            ->reject(fn($definition) => !empty($definition['fixe']))
            ->mapWithKeys(function ($definition, $cle) use ($request, $etablissement) {
                $champ = 'parametres.' . str_replace('.', '_', $cle);

                return [$cle => match ($definition['type'] ?? 'booleen') {
                    'booleen' => $request->boolean($champ),
                    'nombre'  => max(0, (int) $request->input($champ, $etablissement->parametre($cle))),
                    'texte'   => trim((string) $request->input($champ)) ?: $etablissement->parametre($cle),
                    'choix'   => array_key_exists($request->input($champ), $definition['choix']) ? $request->input($champ) : $etablissement->parametre($cle),
                    'liste'   => array_values(array_intersect((array) $request->input($champ, []), array_keys($definition['choix']))) ?: ['especes'],
                }];
            })
            ->all();
        $etablissement->fill($validated)->save();

        return redirect()->route('etablissement.edit')->with('success', 'Configuration de l\'établissement enregistrée.');
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

    /**
     * EF-01.05 : changements de réglages (ancienne valeur, nouvelle valeur, auteur, date),
     * lus dans le journal d'audit.
     */
    private function historique(Etablissement $etablissement): \Illuminate\Support\Collection
    {
        if (!$etablissement->exists) {
            return collect();
        }

        $texte = fn($v) => is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : (is_bool($v) ? ($v ? 'oui' : 'non') : (string) $v);

        return \Spatie\Activitylog\Models\Activity::with('causer')
            ->where('subject_type', Etablissement::class)->where('subject_id', $etablissement->id)->where('event', 'updated')
            ->latest()->limit(30)->get()
            ->flatMap(function ($activite) use ($texte) {
                $avant = $activite->properties['old'] ?? [];
                return collect($activite->properties['attributes'] ?? [])->map(fn($valeur, $champ) => [
                    'date'   => $activite->created_at->format('d/m/Y H:i'),
                    'auteur' => $activite->causer->name ?? 'Système',
                    'champ'  => $champ,
                    'avant'  => \Illuminate\Support\Str::limit($texte($avant[$champ] ?? null), 80),
                    'apres'  => \Illuminate\Support\Str::limit($texte($valeur), 80),
                ])->values();
            });
    }
}
