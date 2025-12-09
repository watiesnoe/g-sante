<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\PaiementCommande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaiementCommandeController extends Controller
{
    public function dashboard()
    {
        // Récupérer toutes les commandes avec leurs relations
        $commandes = Commande::with(['fournisseur', 'paiements'])
            ->latest()
            ->get();

        // Calculer les statistiques manuellement
        $totalPaid = PaiementCommande::sum('montant');
        $totalRemaining = $commandes->sum('reste_a_payer');

        // Statistiques par statut de paiement
        $fullyPaidOrders = $commandes->where('StatutPaiement', 'total')->count();
        $partiallyPaidOrders = $commandes->where('StatutPaiement', 'partielle')->count();
        $unpaidOrders = $commandes->where('StatutPaiement', 'en_cours')->count();

        $stats = [
            'total_paid' => $totalPaid,
            'total_remaining' => $totalRemaining,
            'fully_paid_orders' => $fullyPaidOrders,
            'partially_paid_orders' => $partiallyPaidOrders,
            'unpaid_orders' => $unpaidOrders,
            'total_orders' => $commandes->count()
        ];

        // Paiements récents
        $recentPayments = PaiementCommande::with(['commande', 'commande.fournisseur'])
            ->latest()
            ->take(5)
            ->get();

        // Données pour le graphique (30 derniers jours)
        $chartData = $this->getChartData();

        return view('application.paiementcommande.index', compact(
            'stats',
            'recentPayments',
            'commandes',
            'chartData'
        ));
    }

    private function getChartData()
    {
        $labels = [];
        $data = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d/m');

            $total = PaiementCommande::whereDate('date_paiement', $date)
                ->sum('montant');

            $data[] = $total;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    public function create(Request $request)
    {
        $commandeId = $request->get('commande_id');
        $commande = $commandeId ? Commande::with('fournisseur')->find($commandeId) : null;

        // Récupérer toutes les commandes avec fournisseur pour la liste déroulante
        $commandes = Commande::with('fournisseur')
            ->whereIn('statut', ['en_cours', 'valide']) // Seulement les commandes actives
            ->orderBy('created_at', 'desc')
            ->get();

        return view('application.paiementcommande.create', compact('commande', 'commandes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'commande_id' => 'required|exists:commandes,id',
            'montant' => 'required|numeric|min:0.01',
            'mode' => 'required|string|in:espèce,virement,chèque,carte,autre',
            'date_paiement' => 'required|date',
            'observations' => 'nullable|string|max:500'
        ]);

        // Vérifier que le montant ne dépasse pas le reste à payer
        $commande = Commande::with('paiements')->find($request->commande_id);
        $resteAPayer = $commande->reste_a_payer;

        if ($request->montant > $resteAPayer) {
            return back()->withErrors([
                'montant' => "Le montant ne peut pas dépasser le reste à payer (" . number_format($resteAPayer, 2) . " €)"
            ])->withInput();
        }

        try {
            // Démarrer une transaction pour garantir l'intégrité des données
            DB::beginTransaction();

            // Créer le paiement
            PaiementCommande::create([
                'commande_id' => $request->commande_id,
                'montant' => $request->montant,
                'mode' => $request->mode,
                'date_paiement' => $request->date_paiement,
                'observations' => $request->observations
            ]);

            // Mettre à jour le StatutPaiement de la commande
            $this->updateStatutPaiement($commande);

            DB::commit();

            return redirect()->route('paiementscommande.dashboard')
                ->with('success', 'Paiement enregistré avec succès!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors([
                'error' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Met à jour le statut de paiement d'une commande
     */
    private function updateStatutPaiement(Commande $commande)
    {
        $montantPaye = $commande->montantPaye();
        $total = $commande->total;

        if ($montantPaye >= $total) {
            // Paiement total
            $commande->update(['StatutPaiement' => 'total']);
        } elseif ($montantPaye > 0 && $montantPaye < $total) {
            // Paiement partiel
            $commande->update(['StatutPaiement' => 'partielle']);
        } else {
            // Aucun paiement
            $commande->update(['StatutPaiement' => 'en_cours']);
        }
    }

    public function history($commandeId)
    {
        $commande = Commande::with(['paiements', 'fournisseur'])->findOrFail($commandeId);
        $paiements = $commande->paiements()->latest()->get();

        return view('application.paiementcommande.history', compact('commande', 'paiements'));
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $paiement = PaiementCommande::findOrFail($id);
            $commande = $paiement->commande;

            $paiement->delete();

            // Mettre à jour le statut de paiement de la commande
            $this->updateStatutPaiement($commande);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Paiement supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Récupérer les commandes impayées via API
     */
    public function getUnpaidOrders()
    {
        $unpaidOrders = Commande::with(['fournisseur', 'paiements'])
            ->where(function($query) {
                $query->whereHas('paiements', function($subQuery) {
                    $subQuery->selectRaw('commande_id, SUM(montant) as total_paye')
                        ->groupBy('commande_id')
                        ->havingRaw('total_paye < commandes.total');
                })->orDoesntHave('paiements');
            })
            ->where('statut', '!=', 'annuler')
            ->get()
            ->map(function($commande) {
                return [
                    'id' => $commande->id,
                    'reference' => $commande->reference,
                    'fournisseur' => $commande->fournisseur->nom ?? 'N/A',
                    'total' => $commande->total,
                    'montant_paye' => $commande->montantPaye(), // Méthode existe
                    'reste_a_payer' => $commande->reste_a_payer, // Accesseur
                    'statut_paiement' => $commande->payment_status_text // Accesseur
                ];
            });

        return response()->json($unpaidOrders);
    }

}
