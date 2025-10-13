<?php

namespace App\Http\Controllers;

use App\Models\DemandeEnLigne;
use App\Models\Etablissement;
use App\Models\Patient;
use App\Models\SynchroJournal;
use App\Services\EnLigne\DemandesService;
use App\Services\EnLigne\SynchroService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Serveur local : demandes de rendez-vous venues du serveur en ligne (RG-17) et
 * connexion de l'établissement à ce serveur (EF-14.01, SEC-14).
 */
class DemandeEnLigneController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('rendezvous.en_ligne'), 403, 'Accès non autorisé.');

        $statut = $request->statut ?: 'a_traiter';
        $demandes = DemandeEnLigne::with(['medecin', 'patient', 'rendezVous'])
            ->when($statut === 'a_traiter', fn($q) => $q->whereIn('statut', ['recue', 'conflit']), fn($q) => $q->when($statut !== 'toutes', fn($s) => $s->where('statut', $statut)))
            ->orderBy('creneau')->limit(200)->get();

        // Dossiers qui peuvent correspondre : même téléphone, ou nom et prénom proches (EF-14.02)
        $correspondances = $demandes->whereIn('statut', ['recue'])->mapWithKeys(fn($d) => [$d->id =>
            Patient::where('telephone', $d->patient_telephone)->get()
                ->merge(Patient::doublonsProbables($d->patient_nom, $d->patient_prenom))
                ->unique('id')->take(5),
        ]);

        $etablissement = Etablissement::courant();

        return view('application.rendezvous.en_ligne', [
            'demandes'        => $demandes,
            'statut'          => $statut,
            'correspondances' => $correspondances,
            'etablissement'   => $etablissement,
            'journal'         => SynchroJournal::where('etablissement_id', $etablissement->id)->latest('id')->first(),
        ]);
    }

    public function confirmer(Request $request, DemandeEnLigne $demande, DemandesService $service)
    {
        abort_unless(Auth::user()->can('rendezvous.en_ligne'), 403, 'Accès non autorisé.');
        $validated = $request->validate([
            'patient_id' => 'nullable|exists:patients,id',
            'genre'      => 'nullable|required_without:patient_id|in:M,F',
            'age'        => 'nullable|required_without:patient_id|integer|min:0|max:130',
        ], [
            'genre.required_without' => 'Choisissez le dossier du patient, ou indiquez son sexe et son âge pour en créer un.',
            'age.required_without'   => 'Choisissez le dossier du patient, ou indiquez son sexe et son âge pour en créer un.',
        ]);

        $patient = !empty($validated['patient_id']) ? Patient::findOrFail($validated['patient_id']) : null;
        if (!$patient && Patient::where('telephone', $demande->patient_telephone)->exists()) {
            return back()->with('error', 'Un dossier existe déjà avec le numéro ' . $demande->patient_telephone . ' : choisissez-le plutôt que d\'en créer un autre.');
        }

        try {
            $rdv = $service->confirmer($demande, $patient, $validated);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', 'Rendez-vous confirmé le ' . Carbon::parse($rdv->date_heure)->format('d/m/Y à H\hi') . ' ; le patient sera prévenu à la prochaine synchronisation.');
    }

    public function proposer(Request $request, DemandeEnLigne $demande, DemandesService $service)
    {
        abort_unless(Auth::user()->can('rendezvous.en_ligne'), 403, 'Accès non autorisé.');
        $validated = $request->validate([
            'creneau_propose' => 'required|date|after:now',
            'message'         => 'nullable|string|max:255',
        ]);

        try {
            $service->proposer($demande, Carbon::parse($validated['creneau_propose']), $validated['message'] ?? null);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', 'Autre créneau proposé au patient.');
    }

    public function refuser(Request $request, DemandeEnLigne $demande, DemandesService $service)
    {
        abort_unless(Auth::user()->can('rendezvous.en_ligne'), 403, 'Accès non autorisé.');
        $validated = $request->validate(['message' => 'required|string|max:255']);

        try {
            $service->refuser($demande, $validated['message']);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', 'Demande refusée ; le patient sera prévenu.');
    }

    /** Synchronisation immédiate, sans attendre le passage automatique. */
    public function synchroniser(SynchroService $synchro)
    {
        abort_unless(Auth::user()->can('rendezvous.en_ligne') || Auth::user()->can('parametres.etablissement'), 403, 'Accès non autorisé.');

        $journal = $synchro->synchroniser(Etablissement::courant());

        return back()->with($journal->statut === 'ok' ? 'success' : 'error', $journal->statut === 'ok'
            ? 'Synchronisation réussie : ' . $journal->recus . ' reçu(s), ' . $journal->envoyes . ' envoyé(s).'
            : 'Synchronisation impossible : ' . $journal->message . ' L\'activité de l\'établissement n\'est pas affectée.');
    }

    /** Administration (plateforme) : synchronisation immédiate d'un établissement donné. */
    public function synchroniserEtablissement(Etablissement $etablissement, SynchroService $synchro)
    {
        app()->instance('etablissement.courant', $etablissement);
        try {
            $journal = $synchro->synchroniser($etablissement);
        } finally {
            Etablissement::oublierCourant();
        }

        return back()->with($journal->statut === 'ok' ? 'success' : 'error', $journal->statut === 'ok'
            ? 'Synchronisation réussie : ' . $journal->recus . ' reçu(s), ' . $journal->envoyes . ' envoyé(s).'
            : 'Synchronisation impossible : ' . $journal->message);
    }

    /** Administration (plateforme) : adresse et identifiants du serveur en ligne d'un établissement. */
    public function connexion(Request $request, Etablissement $etablissement)
    {
        $validated = $request->validate([
            'en_ligne_url'    => ['nullable', 'url', 'max:255', function ($attribut, $valeur, $echec) {
                // SEC-13 : HTTPS obligatoire, sauf essai sur la machine elle-même
                $hote = parse_url($valeur, PHP_URL_HOST);
                if (!str_starts_with($valeur, 'https://') && !in_array($hote, ['localhost', '127.0.0.1'], true)) {
                    $echec('L\'adresse du serveur en ligne doit commencer par https://');
                }
            }],
            'en_ligne_cle'    => 'nullable|required_with:en_ligne_url|string|max:40',
            'en_ligne_secret' => 'nullable|string|min:32|max:255',
        ]);

        if (filled($validated['en_ligne_url']) && blank($validated['en_ligne_secret']) && blank($etablissement->en_ligne_secret)) {
            return back()->withErrors(['en_ligne_secret' => 'Saisissez le secret fourni par le serveur en ligne.']);
        }

        $donnees = ['en_ligne_url' => $validated['en_ligne_url'] ?: null, 'en_ligne_cle' => $validated['en_ligne_cle'] ?: null];
        if (filled($validated['en_ligne_secret'])) {
            $donnees['en_ligne_secret'] = $validated['en_ligne_secret'];
        }
        if (blank($donnees['en_ligne_url'])) {
            // Déconnexion : identifiants effacés
            $donnees += ['en_ligne_secret' => null, 'en_ligne_cle' => null];
        }
        $etablissement->fill($donnees)->save();

        return redirect()->route('plateforme.configuration', $etablissement)->with('success', blank($donnees['en_ligne_url'])
            ? 'Établissement déconnecté du serveur en ligne.'
            : 'Connexion au serveur en ligne enregistrée. Utilisez « Synchroniser maintenant » pour la vérifier.');
    }
}
