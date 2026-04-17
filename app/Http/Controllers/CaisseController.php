<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Paiement;
use App\Models\OrdonnancePaiement;
use App\Models\PaiementCommande;
use Carbon\Carbon;

class CaisseController extends Controller
{
    public function index(Request $request)
    {
        $transactions = collect();
        $debutMois = Carbon::now()->startOfMonth();
        $finMois = Carbon::now()->endOfMonth();

        // 1. Entrées Tickets (Consultations etc.)
        $tickets = Ticket::whereNotIn('statut', ['annulé'])->get();
        foreach ($tickets as $ticket) {
            if ($ticket->total > 0) {
                $transactions->push([
                    'date' => $ticket->created_at,
                    'type' => 'Entrée',
                    'provenance' => 'Ticket (Caisse)',
                    'description' => $ticket->description ?: 'Prestation Caisse',
                    'montant' => $ticket->total,
                    'badge' => 'success'
                ]);
            }
        }

        // 2. Entrées Paiements (Hosp, Examens)
        $paiements = Paiement::all();
        foreach ($paiements as $p) {
            if ($p->montant_recu > 0) {
                $desc = 'Paiement Service';
                if ($p->hospitalisation_id) $desc = 'Hospitalisation #' . $p->hospitalisation_id;
                if ($p->prescriptions_examens_id) $desc = 'Examen Labo #' . $p->prescriptions_examens_id;

                $transactions->push([
                    'date' => $p->date_paiement ? Carbon::parse($p->date_paiement) : $p->created_at,
                    'type' => 'Entrée',
                    'provenance' => 'Hôpital / Services',
                    'description' => $desc,
                    'montant' => $p->montant_recu,
                    'badge' => 'success'
                ]);
            }
        }

        // 3. Entrées Pharmacie
        if (class_exists(OrdonnancePaiement::class)) {
            $ordoPaiements = OrdonnancePaiement::with('medicament')->get();
            foreach ($ordoPaiements as $op) {
                if ($op->prix_total > 0) {
                    $medName = $op->medicament ? $op->medicament->nom : 'Medicament ID:'.$op->medicament_id;
                    $transactions->push([
                        'date' => $op->created_at,
                        'type' => 'Entrée',
                        'provenance' => 'Pharmacie',
                        'description' => 'Vente: ' . $medName . ' (Qté: '.$op->quantite.')',
                        'montant' => $op->prix_total,
                        'badge' => 'success'
                    ]);
                }
            }
        }

        // 4. Sorties Transactions Fournisseurs
        $pmtCommandes = PaiementCommande::all();
        foreach ($pmtCommandes as $pc) {
            if ($pc->montant > 0) {
                $transactions->push([
                    'date' => $pc->date_paiement ? Carbon::parse($pc->date_paiement) : $pc->created_at,
                    'type' => 'Sortie',
                    'provenance' => 'Achat / Commande',
                    'description' => 'Paiement Fournisseur (Ref: ' . $pc->reference . ')',
                    'montant' => $pc->montant,
                    'badge' => 'danger'
                ]);
            }
        }

        // Tri décroissant par date
        $transactions = $transactions->sortByDesc('date');

        // Totaux Globaux
        $totalEntre = $transactions->where('type', 'Entrée')->sum('montant');
        $totalSortie = $transactions->where('type', 'Sortie')->sum('montant');
        $solde = $totalEntre - $totalSortie;

        // Totaux du Jour
        $today = Carbon::today();
        $jTransactions = $transactions->filter(function($item) use ($today) {
            return Carbon::parse($item['date'])->startOfDay()->equalTo($today);
        });
        
        $jEntre = $jTransactions->where('type', 'Entrée')->sum('montant');
        $jSortie = $jTransactions->where('type', 'Sortie')->sum('montant');
        $jSolde = $jEntre - $jSortie;

        return view('application.caisse.index', compact('transactions', 'totalEntre', 'totalSortie', 'solde', 'jEntre', 'jSortie', 'jSolde'));
    }
}
