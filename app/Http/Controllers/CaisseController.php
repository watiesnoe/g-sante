<?php

namespace App\Http\Controllers;

use App\Models\CaisseMouvement;
use App\Models\CaisseSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\CaisseService;
use Yajra\DataTables\DataTables;

class CaisseController extends Controller
{
    /**
     * Admin overview: lists all caisse sessions
     */
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('caisse.view'), 403, 'Accès non autorisé.');

        // Vue d'ensemble pour les admins et les responsables qui valident les clôtures
        if (!Auth::user()->hasRole(['admin', 'super_admin']) && !Auth::user()->can('caisse.valider_cloture')) {
            return redirect()->route('caisse.my_session');
        }

        $year = session('exercice_year', date('Y'));
        $sessions = CaisseSession::with(['user', 'valideePar'])
            ->whereYear('created_at', $year)
            ->latest()
            ->get();
        return view('application.caisse.index', compact('sessions'));
    }

    /**
     * Show form to open a caisse session
     */
    public function open()
    {
        $session = CaisseSession::where('user_id', Auth::id())->where('statut', 'ouverte')->first();
        if ($session) {
            return redirect()->route('caisse.my_session')->with('info', 'Vous avez déjà une caisse ouverte.');
        }
        $guichets = \App\Models\Etablissement::courant()->parametre('caisse.multi_guichets')
            ? \App\Models\Guichet::where('actif', true)->orderBy('nom')->get()
            : collect();
        $guichetsOccupes = CaisseSession::where('statut', 'ouverte')->whereNotNull('guichet_id')->pluck('guichet_id')->all();

        return view('application.caisse.open', compact('guichets', 'guichetsOccupes'));
    }

    /**
     * Store opening of a caisse session
     */
    public function storeOpen(Request $request)
    {
        $multiGuichets = \App\Models\Etablissement::courant()->parametre('caisse.multi_guichets') && \App\Models\Guichet::where('actif', true)->exists();
        $request->validate([
            'solde_initial' => 'required|numeric|min:0',
            'guichet_id'    => [$multiGuichets ? 'required' : 'nullable', \Illuminate\Validation\Rule::exists('guichets', 'id')->where('etablissement_id', \App\Models\Etablissement::courantId())->where('actif', true)],
        ], ['guichet_id.required' => 'Choisissez le guichet sur lequel vous ouvrez la caisse.']);

        // EF-06.06 : un guichet n'a qu'une session ouverte à la fois
        if ($request->guichet_id && CaisseSession::where('guichet_id', $request->guichet_id)->where('statut', 'ouverte')->exists()) {
            return back()->withErrors(['guichet_id' => 'Ce guichet a déjà une caisse ouverte.'])->withInput();
        }

        $session = CaisseSession::where('user_id', Auth::id())->where('statut', 'ouverte')->first();
        if ($session) {
            return redirect()->route('caisse.my_session')->with('info', 'Vous avez déjà une caisse ouverte.');
        }

        CaisseSession::create([
            'user_id' => Auth::id(),
            'guichet_id' => $request->guichet_id,
            'solde_initial' => $request->solde_initial,
            'solde_theorique' => $request->solde_initial,
            'statut' => 'ouverte',
            'opened_at' => now(),
        ]);

        return redirect()->route('caisse.my_session')->with('success', 'Votre caisse a été ouverte avec succès.');
    }

    /**
     * Show form to close the caisse session
     */
    public function close()
    {
        $session = CaisseSession::where('user_id', Auth::id())->where('statut', 'ouverte')->first();
        if (!$session) {
            return redirect()->route('caisse.open')->with('error', 'Vous n\'avez pas de caisse ouverte à clôturer.');
        }

        $totalEntrees = $session->mouvements()->where('type', 'entree')->sum('montant');
        $totalSorties = $session->mouvements()->where('type', 'sortie')->sum('montant');
        $session->solde_theorique = app(CaisseService::class)->soldeTheorique($session);
        $totauxParMode = $session->totauxParMode();

        return view('application.caisse.close', compact('session', 'totalEntrees', 'totalSorties', 'totauxParMode'));
    }

    /**
     * Store closing of a caisse session
     */
    public function storeClose(Request $request)
    {
        $request->merge(['solde_reel' => str_replace([' ', "\u{00a0}"], '', (string) $request->solde_reel)]);
        $request->validate([
            'solde_reel'          => 'required|numeric|min:0',
            'justification_ecart' => 'nullable|string|max:1000',
        ]);

        if (!CaisseSession::hasOpenSession()) {
            return redirect()->route('caisse.open')->with('error', 'Vous n\'avez pas de caisse ouverte.');
        }

        $session = app(CaisseService::class)->cloturer((float) $request->solde_reel, $request->justification_ecart);

        $message = $session->statut === 'fermee'
            ? 'Votre caisse est clôturée. Elle doit maintenant être validée par un responsable.'
            : 'Votre caisse a été clôturée avec succès.';

        return redirect()->route('caisse.show', $session->uuid)->with('success', $message);
    }

    /**
     * Validation d'une clôture par un responsable (EF-06.05)
     */
    public function valider(CaisseSession $session)
    {
        try {
            app(CaisseService::class)->valider($session);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Clôture de caisse validée.');
    }

    /**
     * Dashboard for the currently logged in user's open session
     */
    public function mySession(Request $request)
    {
       $session = CaisseSession::where('user_id', Auth::id())->where('statut', 'ouverte')->first();
    if (!$session) {
        return redirect()->route('caisse.open')->with('info', 'Veuillez ouvrir votre caisse pour commencer.');
    }

    // 2. Calculs rapides pour les cartes du haut
    $mouvementsQuery = $session->mouvements();
    $totalEntrees = (clone $mouvementsQuery)->where('type', 'entree')->sum('montant');
    $totalSorties = (clone $mouvementsQuery)->where('type', 'sortie')->sum('montant');

    // Mise à jour dynamique du solde théorique
    $session->update([
        'solde_theorique' => $session->solde_initial + $totalEntrees - $totalSorties
    ]);

    // 3. SI LA REQUÊTE EST AJAX : On renvoie les données pour DataTables
    if ($request->ajax()) {
        $mouvements = $mouvementsQuery->latest();

        return DataTables::of($mouvements)
            ->editColumn('created_at', function ($mouvement) {
                return Carbon::parse($mouvement->created_at)->format('d/m/Y H:i');
            })
            ->editColumn('type', fn($mouvement) => $this->badgeMouvement($mouvement))
            ->editColumn('montant', function ($mouvement) {
                return number_format($mouvement->montant, 0, ',', ' ') . ' XOF';
            })
            ->addColumn('action', function ($mouvement) {
                return '<button type="button" class="btn btn-sm btn-alt-secondary btn-show-mouvement" data-id="' . $mouvement->uuid . '" title="Voir"><i class="fa fa-eye text-primary"></i></button>';
            })
            ->rawColumns(['type', 'action'])
            ->make(true);
    }

    // 4. SI REQUÊTE NORMALE : On charge la vue standard
    return view('application.caisse.my_session', compact('session', 'totalEntrees', 'totalSorties'));
    }

    /**
     * Show details of a caisse session (Admin view or cashier's own view)
     */
    public function show(CaisseSession $session, Request $request)
    {
        // Permission check: Admin/Super Admin or session owner
        if (!Auth::user()->hasRole(['admin', 'super_admin'])) {
            abort_if($session->user_id !== Auth::id(), 403, 'Accès non autorisé.');
        }

        $totalEntrees = $session->mouvements()->where('type', 'entree')->sum('montant');
        $totalSorties = $session->mouvements()->where('type', 'sortie')->sum('montant');

        if ($request->ajax()) {
            $mouvements = $session->mouvements()->latest();

            return DataTables::of($mouvements)
                ->editColumn('created_at', function ($mouvement) {
                    return Carbon::parse($mouvement->created_at)->format('d/m/Y H:i');
                })
                ->editColumn('type', fn($mouvement) => $this->badgeMouvement($mouvement))
                ->editColumn('montant', function ($mouvement) {
                    return number_format($mouvement->montant, 0, ',', ' ') . ' XOF';
                })
                ->addColumn('action', function ($mouvement) {
                    return '<button type="button" class="btn btn-sm btn-alt-secondary btn-show-mouvement" data-id="' . $mouvement->uuid . '" title="Voir"><i class="fa fa-eye text-primary"></i></button>';
                })
                ->rawColumns(['type', 'action'])
                ->make(true);
        }

        return view('application.caisse.show', compact('session', 'totalEntrees', 'totalSorties'));
    }

    /**
     * Show details of a caisse movement (AJAX endpoint)
     */
    public function showMouvement($uuid)
    {
        $mouvement = CaisseMouvement::where('uuid', $uuid)->with(['user', 'session'])->firstOrFail();

        // Check permission/ownership
        if (!Auth::user()->hasRole(['admin', 'super_admin'])) {
            abort_if($mouvement->user_id !== Auth::id(), 403, 'Accès non autorisé.');
        }

        $refData = null;
        $refType = null;

        if ($mouvement->reference) {
            $refType = class_basename($mouvement->reference);
            $ref = $mouvement->reference;

            if ($ref instanceof \App\Models\Ticket) {
                $ref->load(['patient', 'items.prestation.serviceMedical']);
                $refData = [
                    'id' => $ref->id,
                    'uuid' => $ref->uuid,
                    'total' => $ref->total,
                    'part_patient' => $ref->part_patient,
                    'part_assurance' => $ref->part_assurance,
                    'taux_couverture' => $ref->taux_couverture,
                    'statut' => $ref->statut,
                    'patient' => $ref->patient ? ($ref->patient->nom . ' ' . $ref->patient->prenom) : 'Inconnu',
                    'items' => $ref->items->map(function ($item) {
                        return [
                            'prestation' => $item->prestation->nom ?? 'N/A',
                            'service' => $item->prestation->serviceMedical->nom ?? 'N/A',
                            'prix' => $item->prix_unitaire,
                            'quantite' => $item->quantite,
                            'remise' => $item->remise,
                            'total' => $item->sous_total,
                        ];
                    }),
                    'view_url' => route('tickets.show', $ref->uuid)
                ];
            } elseif ($ref instanceof \App\Models\Hospitalisation) {
                $ref->load(['patient', 'salle', 'lit']);
                $refData = [
                    'id' => $ref->id,
                    'uuid' => $ref->uuid,
                    'date_entree' => $ref->date_entree ? Carbon::parse($ref->date_entree)->format('d/m/Y') : '-',
                    'date_sortie' => $ref->date_sortie ? Carbon::parse($ref->date_sortie)->format('d/m/Y') : '-',
                    'salle' => $ref->salle->nom ?? 'N/A',
                    'lit' => $ref->lit->numero ?? 'N/A',
                    'motif' => $ref->motif,
                    'etat' => $ref->etat,
                    'patient' => $ref->patient ? ($ref->patient->nom . ' ' . $ref->patient->prenom) : ($ref->consultation->patient ? ($ref->consultation->patient->nom . ' ' . $ref->consultation->patient->prenom) : 'Inconnu'),
                    'view_url' => route('hospitalisations.show', $ref->uuid)
                ];
            } elseif ($ref instanceof \App\Models\Paiement) {
                $ref->load(['hospitalisation.patient', 'hospitalisation.salle', 'hospitalisation.lit']);
                $hosp = $ref->hospitalisation;
                $refData = [
                    'id' => $ref->id,
                    'hospitalisation_id' => $hosp ? $hosp->id : null,
                    'uuid' => $hosp ? $hosp->uuid : null,
                    'montant_total' => $ref->montant_total,
                    'montant_recu' => $ref->montant_recu,
                    'montant_restant' => $ref->montant_restant,
                    'statut' => $ref->statut,
                    'date_sortie' => $ref->date_sortie ? Carbon::parse($ref->date_sortie)->format('d/m/Y') : ($hosp && $hosp->date_sortie ? Carbon::parse($hosp->date_sortie)->format('d/m/Y') : '-'),
                    'patient' => $hosp && $hosp->patient ? ($hosp->patient->nom . ' ' . $hosp->patient->prenom) : ($hosp && $hosp->consultation && $hosp->consultation->patient ? ($hosp->consultation->patient->nom . ' ' . $hosp->consultation->patient->prenom) : 'Inconnu'),
                    'view_url' => $hosp ? route('hospitalisations.show', $hosp->uuid) : '#'
                ];
            } elseif ($ref instanceof \App\Models\Ordonnance) {
                $ref->load(['consultation.patient', 'medicaments']);
                $refData = [
                    'id' => $ref->id,
                    'uuid' => $ref->uuid,
                    'patient' => $ref->consultation && $ref->consultation->patient ? ($ref->consultation->patient->nom . ' ' . $ref->consultation->patient->prenom) : 'Inconnu',
                    'statut' => $ref->statutordo,
                    'medicaments' => $ref->medicaments->map(function ($med) {
                        return [
                            'nom' => $med->nom,
                            'quantite' => $med->pivot->quantite,
                            'posologie' => $med->pivot->posologie,
                            'duree' => $med->pivot->duree_jours,
                            'prix' => $med->prix_vente,
                        ];
                    }),
                    'view_url' => route('ordonnances.show', $ref->uuid)
                ];
            } elseif ($ref instanceof \App\Models\PaiementCommande) {
                $ref->load(['commande.fournisseur']);
                $cmd = $ref->commande;
                $refData = [
                    'id' => $ref->id,
                    'commande_id' => $cmd ? $cmd->id : null,
                    'commande_ref' => $cmd ? $cmd->reference : null,
                    'fournisseur' => $cmd && $cmd->fournisseur ? $cmd->fournisseur->nom : 'N/A',
                    'montant' => $ref->montant,
                    'mode' => $ref->mode,
                    'date_paiement' => Carbon::parse($ref->date_paiement)->format('d/m/Y'),
                    'observations' => $ref->observations,
                    'view_url' => $cmd ? route('commandes.show', $cmd->uuid) : '#'
                ];
            }
        }

        return response()->json([
            'uuid' => $mouvement->uuid,
            'type' => $mouvement->type,
            'montant' => $mouvement->montant,
            'motif' => $mouvement->motif,
            'date' => Carbon::parse($mouvement->created_at)->format('d/m/Y H:i'),
            'user' => $mouvement->user->name,
            'reference_type' => $refType,
            'reference_data' => $refData
        ]);
    }

    /** Badge du type de mouvement, avec la mention d'annulation s'il y a lieu. */
    private function badgeMouvement($mouvement): string
    {
        $badge = $mouvement->type === 'entree'
            ? '<span class="badge bg-success">Entrée</span>'
            : '<span class="badge bg-danger">Sortie</span>';

        if ($mouvement->statut === 'annule') {
            $badge .= ' <span class="badge bg-dark" title="' . e($mouvement->motif_annulation) . '">Annulé</span>';
        } elseif ($mouvement->mouvement_annule_id) {
            $badge .= ' <span class="badge bg-warning text-dark">Annulation</span>';
        }

        return $badge;
    }

    /**
     * EF-06.06 : consolidation journalière de toutes les caisses, par guichet et par mode.
     */
    public function consolidation(Request $request)
    {
        abort_unless(Auth::user()->can('caisse.valider_cloture') || Auth::user()->hasRole(['admin', 'super_admin']), 403, 'Accès non autorisé.');

        $jour = $request->date ? Carbon::parse($request->date) : today();
        $service = app(CaisseService::class);
        $sessions = CaisseSession::with(['user', 'guichet', 'valideePar'])->whereDate('opened_at', $jour)->orderBy('opened_at')->get()
            ->map(fn($s) => ['session' => $s, 'theorique' => $service->soldeTheorique($s), 'modes' => $s->totauxParMode()]);

        $totauxModes = [];
        foreach ($sessions as $ligne) {
            foreach ($ligne['modes'] as $mode => $t) {
                $totauxModes[$mode]['entree'] = ($totauxModes[$mode]['entree'] ?? 0) + $t['entree'];
                $totauxModes[$mode]['sortie'] = ($totauxModes[$mode]['sortie'] ?? 0) + $t['sortie'];
            }
        }

        return view('application.caisse.consolidation', compact('jour', 'sessions', 'totauxModes'));
    }
}
