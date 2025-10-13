<?php

namespace App\Http\Controllers;

use App\Models\CommandeMedicaments;
use App\Models\Reception;
use App\Models\ReceptionLigne;
use App\Models\Commande;
use App\Models\Medicament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReceptionController extends Controller
{
    public function index()
    {
        $commandes = Commande::with('fournisseur')->latest()->get();

        return view('application.reception.create', compact('commandes'));
    }

//    public function create()
//    {
//        $commandes = Commande::with('fournisseur')->get();
//        return view('receptions.create', compact('commandes'));
//    }

    public function store(Request $request)
    {
        $request->validate([
            'commande_id' => 'required|exists:commandes,id',
            'fournisseur_id' => 'required',
            'date_reception' => 'required|date',
            'reference_reception' => 'required|string',
            'receptions.*.medicament_id' => 'required|exists:medicaments,id',
            'receptions.*.quantite_recue' => 'required|numeric|min:0',
            'receptions.*.prix_unitaire' => 'required|numeric|min:0',
        ]);

        $reception = Reception::create([
            'commande_id' => $request->commande_id,
            'fournisseur_id' => $request->fournisseur_id,
            'date_reception' => $request->date_reception,
            'reference_reception' => $request->reference_reception,
            'user_id' => auth()->id(),
            'statut' => 'partielle',
            'observations' => $request->observations ?? null,
        ]);

        if ($request->has('receptions')) {
            foreach ($request->receptions as $ligne) {
                // 🔍 Vérifier la quantité déjà reçue
                $commandeMedicament = DB::table('commande_medicaments')
                    ->where('commande_id', $request->commande_id)
                    ->where('medicament_id', $ligne['medicament_id'])
                    ->first();

                if (!$commandeMedicament) {
                    return response()->json(['error' => 'Produit non trouvé dans la commande.'], 422);
                }

                $quantiteCommandee = $commandeMedicament->quantite;
                $quantiteRecueActuelle = $commandeMedicament->quantite_recue ?? 0;
                $nouvelleQuantiteTotale = $quantiteRecueActuelle + $ligne['quantite_recue'];

                // ❌ Si la quantité totale dépasse la commandée
                if ($nouvelleQuantiteTotale > $quantiteCommandee) {
                    return response()->json([
                        'error' => "Erreur : la quantité reçue ({$nouvelleQuantiteTotale}) dépasse la quantité commandée ({$quantiteCommandee}) pour le médicament ID {$ligne['medicament_id']}."
                    ], 422);
                }

                // ✅ Enregistrer la ligne de réception
                $reception->lignes()->create([
                    'medicament_id' => $ligne['medicament_id'],
                    'quantite_commandee' => $quantiteCommandee,
                    'quantite_recue' => $ligne['quantite_recue'],
                    'prix_unitaire' => $ligne['prix_unitaire'],
                    'lot' => $ligne['lot'] ?? null,
                    'date_peremption' => $ligne['date_peremption'] ?? null,
                ]);

                // 🔄 Mettre à jour la quantité reçue dans la commande_medicament
                DB::table('commande_medicaments')
                    ->where('commande_id', $request->commande_id)
                    ->where('medicament_id', $ligne['medicament_id'])
                    ->update(['quantiterecue' => $nouvelleQuantiteTotale]);
            }
        }

        return response()->json([
            'message' => 'Réception enregistrée avec succès !',
            'reception_id' => $reception->id,
        ]);
    }

    public function getProduits($id)
    {
        Log::info('🔥 getProduits appelé ! ID = ' . $id);

        $commande = Commande::with(['fournisseur', 'lignes.medicament'])->findOrFail($id);

        // 🔍 Ne garder que les lignes dont la quantité reçue est inférieure à la quantité commandée
        $produits = $commande->lignes
            ->filter(function ($ligne) {
                $quantiteRecue = $ligne->quantiterecue ?? 0; // champ existant dans commande_medicaments
                return $quantiteRecue < $ligne->quantite; // produit non totalement reçu
            })
            ->map(function ($ligne) {
                $quantiteRecue = $ligne->quantite_recue ?? 0;
                $quantiteRestante = $ligne->quantite - $quantiteRecue;

                return [
                    'medicament_id' => $ligne->medicament->id,
                    'nom' => $ligne->medicament->nom,
                    'quantite_commandee' => $ligne->quantite,
                    'quantite_recue' => $quantiteRecue,
                    'quantite_restante' => $quantiteRestante,
                    'prix_unitaire' => $ligne->prix_unitaire,
                    'stock_ancien' => $ligne->medicament->stock,
                ];
            })
            ->values();

        return response()->json([
            'fournisseur_id' => $commande->fournisseur->id,
            'fournisseur_nom' => $commande->fournisseur->nom,
            'produits' => $produits,
        ]);
    }

}
