<?php

namespace App\Http\Controllers;

use App\Models\CaisseMouvement;
use App\Models\CaisseSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class CaisseController extends Controller
{
    /**
     * Admin overview: lists all caisse sessions
     */
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('caisse.view'), 403, 'Accès non autorisé.');

        // View all sessions for admin, or redirect normal users to their active session
        if (!Auth::user()->hasRole(['admin', 'super_admin'])) {
            return redirect()->route('caisse.my_session');
        }

        $year = session('exercice_year', date('Y'));
        $sessions = CaisseSession::with('user')
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
        return view('application.caisse.open');
    }

    /**
     * Store opening of a caisse session
     */
    public function storeOpen(Request $request)
    {
        $request->validate([
            'solde_initial' => 'required|numeric|min:0'
        ]);

        $session = CaisseSession::where('user_id', Auth::id())->where('statut', 'ouverte')->first();
        if ($session) {
            return redirect()->route('caisse.my_session')->with('info', 'Vous avez déjà une caisse ouverte.');
        }

        CaisseSession::create([
            'user_id' => Auth::id(),
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

        // Recalculate theorique just in case
        $totalEntrees = $session->mouvements()->where('type', 'entree')->sum('montant');
        $totalSorties = $session->mouvements()->where('type', 'sortie')->sum('montant');
        $session->solde_theorique = $session->solde_initial + $totalEntrees - $totalSorties;
        $session->save();

        return view('application.caisse.close', compact('session', 'totalEntrees', 'totalSorties'));
    }

    /**
     * Store closing of a caisse session
     */
    public function storeClose(Request $request)
    {
        $request->validate([
            'solde_reel' => 'required|numeric|min:0'
        ]);

        $session = CaisseSession::where('user_id', Auth::id())->where('statut', 'ouverte')->first();
        if (!$session) {
            return redirect()->route('caisse.open')->with('error', 'Vous n\'avez pas de caisse ouverte.');
        }

        // Final theorique calculation
        $totalEntrees = $session->mouvements()->where('type', 'entree')->sum('montant');
        $totalSorties = $session->mouvements()->where('type', 'sortie')->sum('montant');
        $session->solde_theorique = $session->solde_initial + $totalEntrees - $totalSorties;

        $session->solde_reel = $request->solde_reel;
        $session->ecart = $request->solde_reel - $session->solde_theorique;
        $session->statut = 'fermee';
        $session->closed_at = now();
        $session->save();

        return redirect()->route('caisse.index')->with('success', 'Votre caisse a été clôturée avec succès.');
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
            ->editColumn('type', function ($mouvement) {
                return $mouvement->type === 'entree' 
                    ? '<span class="badge bg-success">Entrée</span>' 
                    : '<span class="badge bg-danger">Sortie</span>';
            })
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
                ->editColumn('type', function ($mouvement) {
                    return $mouvement->type === 'entree' 
                        ? '<span class="badge bg-success">Entrée</span>' 
                        : '<span class="badge bg-danger">Sortie</span>';
                })
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
}
