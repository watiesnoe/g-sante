<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CaisseSession;
use App\Models\CaisseMouvement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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
    public function mySession()
    {
        $session = CaisseSession::where('user_id', Auth::id())->where('statut', 'ouverte')->first();
        if (!$session) {
            return redirect()->route('caisse.open')->with('info', 'Veuillez ouvrir votre caisse pour commencer.');
        }

        $mouvements = $session->mouvements()->latest()->get();
        
        $totalEntrees = $mouvements->where('type', 'entree')->sum('montant');
        $totalSorties = $mouvements->where('type', 'sortie')->sum('montant');
        
        // Update theorique dynamically
        $session->update([
            'solde_theorique' => $session->solde_initial + $totalEntrees - $totalSorties
        ]);

        return view('application.caisse.my_session', compact('session', 'mouvements', 'totalEntrees', 'totalSorties'));
    }
}
