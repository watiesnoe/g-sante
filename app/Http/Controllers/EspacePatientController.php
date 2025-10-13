<?php

namespace App\Http\Controllers;

use App\Http\Middleware\PatientConnecte;
use App\Models\EnLigne\Compte;
use App\Models\EnLigne\Creneau;
use App\Models\EnLigne\Demande;
use App\Models\EnLigne\Medecin;
use App\Models\EnLigne\Site;
use App\Services\EnLigne\ConnexionPatientService;
use App\Services\EnLigne\PortailService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Espace patient du serveur en ligne (EF-14.02 à EF-14.08). Le patient ne voit et
 * ne modifie que ses propres demandes : chaque requête est filtrée par son compte (SEC-12).
 */
class EspacePatientController extends Controller
{
    // ── Connexion par code SMS ────────────────────────────────────────

    public function connexion(Request $request)
    {
        if ($request->session()->has(PatientConnecte::SESSION)) {
            return redirect()->route('patient.accueil');
        }

        return view('espace_patient.connexion', ['telephone' => $request->session()->get('espace_patient.telephone')]);
    }

    public function envoyerCode(Request $request, ConnexionPatientService $connexion)
    {
        $request->validate(['telephone' => 'required|string|max:20'], ['telephone.required' => 'Indiquez votre numéro de téléphone.']);
        $code = $connexion->demanderCode($request->telephone, $request->ip());
        $request->session()->put('espace_patient.telephone', Compte::normaliser($request->telephone));

        $retour = redirect()->route('patient.verification')->with('info', 'Un code vous a été envoyé par SMS.');
        // Démonstration sans opérateur SMS : le code est affiché, jamais en production
        if (config('sms.pilote') === 'journal' && !app()->isProduction()) {
            $retour->with('demo_code', $code);
        }

        return $retour;
    }

    public function verification(Request $request)
    {
        if (!$request->session()->has('espace_patient.telephone')) {
            return redirect()->route('patient.connexion');
        }

        return view('espace_patient.verification', ['telephone' => $request->session()->get('espace_patient.telephone')]);
    }

    public function verifier(Request $request, ConnexionPatientService $connexion)
    {
        $request->validate(['code' => 'required|digits:6'], ['code.required' => 'Saisissez le code reçu.', 'code.digits' => 'Le code comporte 6 chiffres.']);
        $telephone = $request->session()->get('espace_patient.telephone');
        if (!$telephone) {
            return redirect()->route('patient.connexion');
        }

        $compte = $connexion->verifier($telephone, $request->code);
        // Nouvelle session à la connexion : un identifiant de session volé avant ne sert à rien
        $request->session()->regenerate();
        $request->session()->forget('espace_patient.telephone');
        $request->session()->put(PatientConnecte::SESSION, $compte->id);

        return redirect()->intended(route('patient.accueil'));
    }

    public function deconnexion(Request $request)
    {
        $request->session()->forget(PatientConnecte::SESSION);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('patient.connexion');
    }

    public function profil(Request $request)
    {
        return view('espace_patient.profil', ['compte' => $this->compte($request)]);
    }

    public function enregistrerProfil(Request $request)
    {
        $validated = $request->validate([
            'nom'    => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
        ]);
        $this->compte($request)->update(array_map('trim', $validated));

        return redirect()->route('patient.accueil');
    }

    // ── Rendez-vous ───────────────────────────────────────────────────

    public function accueil(Request $request)
    {
        $demandes = Demande::with('site')->where('patient_compte_id', $this->compte($request)->id)
            ->orderByRaw("statut IN ('en_attente','confirmee','proposee') DESC")->orderByDesc('creneau')->limit(50)->get();

        return view('espace_patient.accueil', ['compte' => $this->compte($request), 'demandes' => $demandes]);
    }

    /** Choix de l'établissement, puis du médecin et du créneau ; « remplace » pour un déplacement. */
    public function nouveau(Request $request, ?Site $site = null)
    {
        $remplace = $request->filled('remplace') ? $this->demande($request, $request->remplace) : null;
        if ($remplace && !$remplace->modifiable()) {
            return redirect()->route('patient.accueil')->with('erreur', 'Ce rendez-vous ne peut plus être déplacé en ligne : le délai est dépassé. Appelez l\'établissement.');
        }
        $site ??= $remplace?->site;

        if (!$site) {
            $sites = Site::where('actif', true)->whereHas('creneaux', fn($q) => $q->where('debut', '>', now()))->orderBy('nom')->get();

            return view('espace_patient.etablissements', ['sites' => $sites]);
        }
        abort_unless($site->actif, 404);

        $medecins = Medecin::where('site_id', $site->id)->orderBy('nom')->get();
        $medecin = $medecins->firstWhere('uuid', $request->medecin ?? $remplace?->medecin_uuid);
        $creneaux = $medecin
            ? Creneau::where('site_id', $site->id)->where('medecin_uuid', $medecin->uuid)->where('debut', '>', now())->orderBy('debut')->limit(200)->get()
                ->groupBy(fn($c) => $c->debut->format('Y-m-d'))
            : collect();

        return view('espace_patient.nouveau', compact('site', 'medecins', 'medecin', 'creneaux', 'remplace'));
    }

    public function demander(Request $request, Site $site, PortailService $portail)
    {
        abort_unless($site->actif, 404);
        $validated = $request->validate([
            'medecin_uuid' => 'required|uuid',
            'creneau'      => 'required|date',
            'motif'        => 'nullable|string|max:200',
            'remplace'     => 'nullable|uuid',
        ], ['creneau.required' => 'Choisissez un horaire.']);

        $compte = $this->compte($request);
        $donnees = [
            'medecin_uuid' => $validated['medecin_uuid'], 'creneau' => $validated['creneau'], 'motif' => $validated['motif'] ?? null,
            'patient_nom' => $compte->nom, 'patient_prenom' => $compte->prenom, 'patient_telephone' => $compte->telephone,
            'patient_compte_id' => $compte->id,
        ];

        try {
            if (!empty($validated['remplace'])) {
                $ancienne = $this->demande($request, $validated['remplace']);
                abort_unless($ancienne->site_id === $site->id, 404);
                $portail->deplacer($ancienne, $donnees);
                $message = 'Demande de changement envoyée. L\'établissement doit la confirmer ; vous serez prévenu par SMS.';
            } else {
                $portail->demander($site, $donnees);
                $message = 'Demande envoyée. L\'établissement doit la confirmer ; vous serez prévenu par SMS.';
            }
        } catch (ValidationException $e) {
            return back()->withInput()->with('erreur', collect($e->errors())->flatten()->first());
        }

        return redirect()->route('patient.accueil')->with('info', $message);
    }

    public function annuler(Request $request, string $demande, PortailService $portail)
    {
        try {
            $portail->annuler($this->demande($request, $demande));
        } catch (ValidationException $e) {
            return back()->with('erreur', collect($e->errors())->flatten()->first());
        }

        return back()->with('info', 'Rendez-vous annulé.');
    }

    public function accepter(Request $request, string $demande, PortailService $portail)
    {
        try {
            $portail->accepterProposition($this->demande($request, $demande));
        } catch (ValidationException $e) {
            return back()->with('erreur', collect($e->errors())->flatten()->first());
        }

        return back()->with('info', 'Créneau accepté. L\'établissement va le confirmer.');
    }

    // ── PWA (EF-14.07) ────────────────────────────────────────────────

    public function manifeste()
    {
        return response()->json([
            'name'             => 'G-SANTÉ — Espace patient',
            'short_name'       => 'G-SANTÉ',
            'description'      => 'Vos rendez-vous dans les établissements de santé',
            'lang'             => 'fr',
            'start_url'        => route('patient.accueil', [], false),
            'scope'            => '/patient/',
            'display'          => 'standalone',
            'background_color' => '#ffffff',
            'theme_color'      => '#0b5d8f',
            'icons'            => [
                ['src' => '/patient-icone-192.png', 'sizes' => '192x192', 'type' => 'image/png'],
                ['src' => '/patient-icone-512.png', 'sizes' => '512x512', 'type' => 'image/png'],
                ['src' => '/patient-icone-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
        ], 200, ['Content-Type' => 'application/manifest+json']);
    }

    public function serviceWorker()
    {
        return response()->view('espace_patient.sw', [], 200, ['Content-Type' => 'application/javascript', 'Service-Worker-Allowed' => '/patient/']);
    }

    public function horsLigne()
    {
        return view('espace_patient.hors_ligne');
    }

    private function compte(Request $request): Compte
    {
        return $request->attributes->get('compte');
    }

    /** SEC-12 : une demande d'un autre patient est introuvable, quelle que soit l'adresse saisie. */
    private function demande(Request $request, string $uuid): Demande
    {
        return Demande::with('site')->where('uuid', $uuid)->where('patient_compte_id', $this->compte($request)->id)->firstOrFail();
    }
}
