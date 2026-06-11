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
                return '<a href="#" class="btn btn-sm btn-alt-secondary" title="Voir"><i class="fa fa-eye"></i></a>';
            })
            ->rawColumns(['type', 'action'])
            ->make(true);
    }

    // 4. SI REQUÊTE NORMALE : On charge la vue standard
    return view('application.caisse.my_session', compact('session', 'totalEntrees', 'totalSorties'));
    }
}
