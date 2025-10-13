<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use App\Models\Etablissement;
use App\Models\Formule;
use App\Models\PaiementAbonnement;
use App\Models\Role;
use App\Services\AbonnementService;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Services\EtablissementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Espace plateforme (super-admin) : création des espaces d'établissement et de
 * leurs propriétaires, modules accordés, suspension. Le propriétaire administre
 * ensuite son seul établissement (configuration, personnel).
 *
 * Toutes les routes exigent le droit etablissements.gerer (voir routes/web.php).
 */
class PlateformeController extends Controller
{
    /** Tableau de bord : espaces, abonnements, échéances, recettes. */
    public function tableau()
    {
        $etablissements = Etablissement::query()->with(['abonnement.formule'])->orderBy('nom')->get();
        $abonnements = $etablissements->pluck('abonnement')->filter(fn($a) => $a && $a->statut !== 'termine');

        $recettes = PaiementAbonnement::where('paye_le', '>=', now()->subMonths(5)->startOfMonth())
            ->get(['montant', 'paye_le'])->groupBy(fn($p) => $p->paye_le->format('Y-m'))->map->sum('montant');
        $mois = collect(range(5, 0))->mapWithKeys(fn($i) => [now()->subMonths($i)->format('Y-m') => (float) ($recettes[now()->subMonths($i)->format('Y-m')] ?? 0)]);

        return view('application.plateforme.tableau', [
            'etablissements' => $etablissements,
            'abonnements'    => $abonnements,
            'parFormule'     => $abonnements->groupBy(fn($a) => $a->formule->nom)->map->count()->sortDesc(),
            'echeances'      => $abonnements->filter(fn($a) => $a->statut === 'actif' && $a->joursRestants() <= 30)->sortBy('fin'),
            'retards'        => $abonnements->whereIn('statut', ['grace', 'restreint'])->sortBy('fin'),
            'mois'           => $mois,
            'proprietaires'  => User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->count(),
            'jamaisConnectes' => User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->where('doit_changer_mot_de_passe', true)->count(),
            'paiementsRecents' => PaiementAbonnement::with('abonnement.etablissement')->latest('id')->limit(8)->get(),
        ]);
    }

    /** Gestion des accès : tous les propriétaires d'établissement. */
    public function proprietaires()
    {
        return view('application.plateforme.proprietaires', [
            'proprietaires' => User::whereHas('roles', fn($q) => $q->where('name', 'admin'))
                ->with(['etablissements:id,nom,actif'])->orderBy('nom')->get(),
        ]);
    }

    /** Gestion des accès : administrateurs de la plateforme (super-admins). */
    public function administrateurs()
    {
        return view('application.plateforme.administrateurs', [
            'administrateurs' => User::whereHas('roles', fn($q) => $q->whereIn('name', ['super_admin', 'superadmin']))->orderBy('nom')->get(),
        ]);
    }

    /** Nouvel administrateur de la plateforme ; mot de passe provisoire affiché une seule fois. */
    public function ajouterAdministrateur(Request $request)
    {
        $validated = $request->validate([
            'prenom'    => 'required|string|max:100',
            'nom'       => 'required|string|max:100',
            'email'     => 'required|email|max:255|unique:users,email',
            'telephone' => 'nullable|string|max:30',
        ], ['email.unique' => 'Un compte existe déjà avec cet e-mail.']);

        $motDePasse = $this->motDePasseProvisoire();
        DB::transaction(function () use ($validated, $motDePasse) {
            $user = new User([
                'name' => trim($validated['prenom'] . ' ' . $validated['nom']), 'prenom' => $validated['prenom'], 'nom' => $validated['nom'],
                'email' => mb_strtolower($validated['email']), 'telephone' => $validated['telephone'] ?? null,
                'password' => $motDePasse, 'statut' => 'actif',
            ]);
            $user->forceFill(['uuid' => (string) Str::uuid(), 'doit_changer_mot_de_passe' => true])->save();
            $user->assignRole(Role::where('name', 'super_admin')->where('guard_name', 'web')->firstOrFail());
            // Un administrateur de la plateforme accède à tous les établissements
            $user->etablissements()->sync(Etablissement::pluck('id'));
        });

        return redirect()->route('plateforme.administrateurs')->with('identifiants', [
            'etablissement' => 'Administration de la plateforme', 'email' => mb_strtolower($validated['email']), 'mot_de_passe' => $motDePasse,
        ]);
    }

    /** Activer ou désactiver un compte propriétaire ou administrateur. */
    public function basculerCompte(User $user)
    {
        abort_unless($user->hasRole(['admin', 'super_admin', 'superadmin']), 404);
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }
        if ($user->estSuperAdmin() && $user->statut === 'actif'
            && User::whereHas('roles', fn($q) => $q->where('name', 'super_admin'))->where('statut', 'actif')->count() <= 1) {
            return back()->with('error', 'Il doit rester au moins un administrateur de la plateforme actif.');
        }
        $user->update(['statut' => $user->statut === 'actif' ? 'inactif' : 'actif']);

        return back()->with('success', $user->name . ' : compte ' . ($user->statut === 'actif' ? 'réactivé' : 'désactivé') . '.');
    }

    /** Nouveau mot de passe provisoire pour un administrateur de la plateforme. */
    public function reinitialiserAdministrateur(User $user)
    {
        abort_unless($user->estSuperAdmin(), 404);
        $motDePasse = $this->motDePasseProvisoire();
        $user->forceFill(['password' => $motDePasse, 'doit_changer_mot_de_passe' => true, 'statut' => 'actif', 'echecs_connexion' => 0, 'verrouille_jusqua' => null])->save();

        return redirect()->route('plateforme.administrateurs')->with('identifiants', [
            'etablissement' => 'Administration de la plateforme', 'email' => $user->email, 'mot_de_passe' => $motDePasse,
        ]);
    }

    public function index()
    {
        $etablissements = Etablissement::query()->with('abonnement.formule')->orderBy('nom')->get();
        $proprietaires = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))
            ->with('etablissements:id')->get(['id', 'uuid', 'name', 'nom', 'prenom', 'email', 'statut', 'doit_changer_mot_de_passe']);
        $comptes = DB::table('etablissement_user')->select('etablissement_id', DB::raw('COUNT(*) as total'))->groupBy('etablissement_id')->pluck('total', 'etablissement_id');
        $patients = DB::table('patients')->select('etablissement_id', DB::raw('COUNT(*) as total'))->groupBy('etablissement_id')->pluck('total', 'etablissement_id');

        return view('application.plateforme.index', [
            'etablissements' => $etablissements,
            'proprietaires'  => $etablissements->mapWithKeys(fn($e) => [$e->id => $proprietaires->filter(fn($u) => $u->etablissements->contains('id', $e->id))->values()]),
            'comptes'        => $comptes,
            'patients'       => $patients,
            'types'          => Etablissement::TYPES,
            'modules'        => Etablissement::MODULES,
            'formules'       => Formule::where('actif', true)->orderBy('ordre')->orderBy('nom')->get(),
            'recettesMois'   => PaiementAbonnement::where('paye_le', '>=', now()->startOfMonth())->sum('montant'),
            'paiementsRecents' => PaiementAbonnement::with('abonnement.etablissement')->latest('id')->limit(8)->get(),
        ]);
    }

    public function create()
    {
        return view('application.plateforme.creer', [
            'types'   => Etablissement::TYPES,
            'profils' => Etablissement::PROFILS,
            'modules' => Etablissement::MODULES,
            'modeles' => Etablissement::query()->orderBy('nom')->get(['id', 'nom']),
            'formules' => Formule::where('actif', true)->orderBy('ordre')->orderBy('nom')->get(),
        ]);
    }

    /** Crée l'espace et son propriétaire ; le mot de passe provisoire n'est affiché qu'une fois. */
    public function store(Request $request, EtablissementService $service)
    {
        $validated = $request->validate([
            'type'       => ['required', Rule::in(array_keys(Etablissement::TYPES))],
            'nom'        => 'required|string|max:255',
            'code'       => 'nullable|string|max:20|alpha_dash|unique:etablissements,code',
            'region'     => 'nullable|string|max:255',
            'district'   => 'nullable|string|max:255',
            'adresse'    => 'nullable|string|max:255',
            'telephone'  => 'nullable|string|max:255',
            'email'      => 'nullable|email|max:255',
            'modele_id'  => 'nullable|exists:etablissements,id',
            'modules'    => 'nullable|array',
            'modules.*'  => [Rule::in(array_keys(Etablissement::MODULES))],
            'proprietaire.prenom'    => 'required|string|max:100',
            'proprietaire.nom'       => 'required|string|max:100',
            'proprietaire.email'     => 'required|email|max:255|unique:users,email',
            'proprietaire.telephone' => 'nullable|string|max:30',
            'formule_id' => 'nullable|exists:formules,id',
            'debut'      => 'nullable|date',
            'essai_jours' => 'nullable|integer|min:1|max:90',
        ], [
            'proprietaire.email.unique' => 'Un compte existe déjà avec cet e-mail : choisissez une autre adresse pour le propriétaire.',
        ]);

        [$etablissement, $motDePasse] = DB::transaction(function () use ($validated, $service) {
            $modele = !empty($validated['modele_id']) ? Etablissement::find($validated['modele_id']) : null;
            $etablissement = $service->creer(collect($validated)->only(['type', 'nom', 'code', 'region', 'district', 'adresse', 'telephone', 'email'])->all(), $modele);
            if (!empty($validated['formule_id'])) {
                // Avec une formule, les modules sont ceux de la formule
                app(AbonnementService::class)->souscrire($etablissement, Formule::findOrFail($validated['formule_id']),
                    Carbon::parse($validated['debut'] ?? today()), $validated['essai_jours'] ?? null);
            } elseif (isset($validated['modules'])) {
                $etablissement->update(['modules' => array_values($validated['modules'])]);
            }
            $motDePasse = $this->creerProprietaire($etablissement, $validated['proprietaire']);

            return [$etablissement, $motDePasse];
        });

        return redirect()->route('plateforme.index')->with('identifiants', [
            'etablissement' => $etablissement->nom,
            'email'         => mb_strtolower($validated['proprietaire']['email']),
            'mot_de_passe'  => $motDePasse,
        ]);
    }

    /** Ajoute un propriétaire à un espace existant (par exemple créé avant la plateforme). */
    public function ajouterProprietaire(Request $request, Etablissement $etablissement)
    {
        $validated = $request->validate([
            'prenom'    => 'required|string|max:100',
            'nom'       => 'required|string|max:100',
            'email'     => 'required|email|max:255|unique:users,email',
            'telephone' => 'nullable|string|max:30',
        ], ['email.unique' => 'Un compte existe déjà avec cet e-mail.']);

        $motDePasse = DB::transaction(fn() => $this->creerProprietaire($etablissement, $validated));

        return redirect()->route('plateforme.index')->with('identifiants', [
            'etablissement' => $etablissement->nom, 'email' => mb_strtolower($validated['email']), 'mot_de_passe' => $motDePasse,
        ]);
    }

    /** Nouveau mot de passe provisoire pour un propriétaire qui a perdu le sien. */
    public function reinitialiser(Etablissement $etablissement, User $user)
    {
        abort_unless($user->hasRole('admin') && $user->etablissements()->whereKey($etablissement->id)->exists(), 404);

        $motDePasse = $this->motDePasseProvisoire();
        $user->forceFill([
            'password' => $motDePasse, 'doit_changer_mot_de_passe' => true, 'statut' => 'actif',
            'echecs_connexion' => 0, 'verrouille_jusqua' => null,
        ])->save();

        // Retour à la page d'origine (liste des établissements ou des propriétaires)
        return back()->with('identifiants', [
            'etablissement' => $etablissement->nom, 'email' => $user->email, 'mot_de_passe' => $motDePasse,
        ]);
    }

    /** Configuration d'un établissement : type, modules, règles de fonctionnement, serveur en ligne. */
    public function configuration(Etablissement $etablissement, EtablissementService $service)
    {
        return view('application.plateforme.configuration', [
            'etablissement' => $etablissement->load('abonnement.formule'),
            'types'         => Etablissement::TYPES,
            'modules'       => Etablissement::MODULES,
            'profils'       => Etablissement::PROFILS,
            'parametres'    => Etablissement::PARAMETRES,
            'historique'    => $service->historique($etablissement),
            'synchros'      => \App\Models\SynchroJournal::where('etablissement_id', $etablissement->id)->latest('id')->limit(5)->get(),
        ]);
    }

    public function enregistrerConfiguration(Request $request, Etablissement $etablissement, EtablissementService $service)
    {
        $validated = $request->validate([
            'type'      => ['required', Rule::in(array_keys(Etablissement::TYPES))],
            'modules'   => 'nullable|array',
            'modules.*' => [Rule::in(array_keys(Etablissement::MODULES))],
        ]);
        $etablissement->fill([
            'type'       => $validated['type'],
            'modules'    => array_values($validated['modules'] ?? []),
            'parametres' => $service->reglagesDepuis($request, $etablissement),
        ])->save();

        return redirect()->route('plateforme.configuration', $etablissement)->with('success', 'Configuration de « ' . $etablissement->nom . ' » enregistrée.');
    }

    /** Modules et type accordés à l'établissement. */
    public function modules(Request $request, Etablissement $etablissement)
    {
        $validated = $request->validate([
            'type'      => ['required', Rule::in(array_keys(Etablissement::TYPES))],
            'modules'   => 'nullable|array',
            'modules.*' => [Rule::in(array_keys(Etablissement::MODULES))],
        ]);
        $etablissement->update(['type' => $validated['type'], 'modules' => array_values($validated['modules'] ?? [])]);

        return redirect()->route('plateforme.index')->with('success', 'Modules de « ' . $etablissement->nom . ' » enregistrés.');
    }

    /** Abonner l'espace à une formule (nouvel abonnement) ou changer de formule en gardant les dates. */
    public function abonner(Request $request, Etablissement $etablissement, AbonnementService $service)
    {
        $validated = $request->validate([
            'formule_id'  => 'required|exists:formules,id',
            'action'      => 'required|in:nouveau,changer',
            'debut'       => 'nullable|required_if:action,nouveau|date',
            'essai_jours' => 'nullable|integer|min:1|max:90',
        ]);
        $formule = Formule::findOrFail($validated['formule_id']);
        $actuel = $etablissement->abonnement;

        if ($validated['action'] === 'changer' && $actuel && $actuel->statut !== 'termine') {
            $service->changerFormule($actuel, $formule);
            $message = 'Formule de « ' . $etablissement->nom . ' » changée pour « ' . $formule->nom . ' » ; ses modules sont mis à jour.';
        } else {
            $abonnement = $service->souscrire($etablissement, $formule, Carbon::parse($validated['debut'] ?? today()), $validated['essai_jours'] ?? null);
            $message = 'Abonnement « ' . $formule->nom . ' » de « ' . $etablissement->nom . ' » jusqu\'au ' . $abonnement->fin->format('d/m/Y') . '.';
        }

        return redirect()->route('plateforme.index')->with('success', $message);
    }

    /** Paiement reçu (espèces, virement, paiement mobile…) : prolonge l'abonnement et rétablit les modules. */
    public function payer(Request $request, Etablissement $etablissement, AbonnementService $service)
    {
        $validated = $request->validate([
            'montant'   => 'required|numeric|min:1',
            'mode'      => ['required', Rule::in(array_keys(PaiementAbonnement::MODES))],
            'reference' => 'nullable|string|max:100',
            'paye_le'   => 'required|date|before_or_equal:today',
        ]);
        $abonnement = $etablissement->abonnement;
        abort_unless($abonnement, 404, 'Aucun abonnement pour cet espace.');

        try {
            $paiement = $service->enregistrerPaiement($abonnement, (float) $validated['montant'], $validated['mode'], $validated['reference'] ?? null, Carbon::parse($validated['paye_le']));
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return redirect()->route('plateforme.index')->with('success', 'Paiement ' . $paiement->numero . ' enregistré : « ' . $etablissement->nom . ' » est abonné jusqu\'au ' . $paiement->periode_fin->format('d/m/Y') . '.')
            ->with('recu', $paiement->uuid);
    }

    public function recu(PaiementAbonnement $paiement)
    {
        return view('application.plateforme.recu', ['paiement' => $paiement->load(['abonnement.etablissement', 'abonnement.formule', 'user'])]);
    }

    /** Suspendre : plus personne de l'établissement ne peut travailler ; les données sont conservées. */
    public function suspendre(Request $request, Etablissement $etablissement)
    {
        $request->validate(['motif' => 'required|string|max:255']);
        if (Etablissement::where('actif', true)->count() <= 1) {
            return back()->with('error', 'Impossible de suspendre le dernier établissement actif.');
        }
        $etablissement->update(['actif' => false, 'motif_suspension' => $request->motif]);

        return redirect()->route('plateforme.index')->with('success', 'Espace « ' . $etablissement->nom . ' » suspendu. Ses données sont conservées.');
    }

    public function reactiver(Etablissement $etablissement)
    {
        $etablissement->update(['actif' => true, 'motif_suspension' => null]);

        return redirect()->route('plateforme.index')->with('success', 'Espace « ' . $etablissement->nom . ' » réactivé.');
    }

    /** Ouvre l'espace d'un établissement, pour l'assister ou le vérifier. */
    public function entrer(Request $request, Etablissement $etablissement)
    {
        abort_unless($etablissement->actif, 403, 'Réactivez d\'abord cet espace.');
        $request->session()->put(Etablissement::SESSION, $etablissement->id);
        Etablissement::oublierCourant();

        return redirect()->route('dashboard')->with('success', 'Vous travaillez maintenant dans : ' . $etablissement->nom);
    }

    private function creerProprietaire(Etablissement $etablissement, array $donnees): string
    {
        $role = Role::where('name', 'admin')->where('guard_name', 'web')->firstOrFail();
        $motDePasse = $this->motDePasseProvisoire();

        $user = new User([
            'name'      => trim($donnees['prenom'] . ' ' . $donnees['nom']),
            'prenom'    => $donnees['prenom'],
            'nom'       => $donnees['nom'],
            'email'     => mb_strtolower($donnees['email']),
            'telephone' => $donnees['telephone'] ?? null,
            'password'  => $motDePasse,
            'statut'    => 'actif',
        ]);
        $user->forceFill(['uuid' => (string) Str::uuid(), 'doit_changer_mot_de_passe' => true])->save();
        $user->assignRole($role);
        // Le propriétaire n'a accès qu'à son établissement
        $user->etablissements()->sync([$etablissement->id]);

        return $motDePasse;
    }

    private function motDePasseProvisoire(): string
    {
        return Str::password(12, letters: true, numbers: true, symbols: false);
    }
}
