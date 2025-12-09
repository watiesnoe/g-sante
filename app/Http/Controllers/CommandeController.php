<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Fournisseur;
use App\Models\Medicament;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class CommandeController extends Controller
{
    // Liste des commandes
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $commandes = Commande::with('fournisseur')->latest();

            return DataTables::of($commandes)
                ->addColumn('fournisseur', function ($row) {
                    return $row->fournisseur ? $row->fournisseur->nom : '-';
                })->addColumn('actions', function ($row) {
                    return '
                <div class="dropdown text-center">
                    <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        ⚙️ Actions
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="'.route('commandes.show', $row->id).'" class="dropdown-item">📄 Voir détails</a></li>
                        <li><a href="'.route('commandes.edit', $row->id).'" class="dropdown-item">✏️ Modifier</a></li>
                        <li><a href="'.route('commandes.pdf', $row->id).'" class="dropdown-item" target="_blank">🧾 Imprimer</a></li>

                        <li><hr class="dropdown-divider"></li>
                        <li><button type="button" class="dropdown-item text-danger btnSupprimer" data-id="'.$row->id.'">🗑️ Supprimer</button></li>
                    </ul>
                </div> ';
                })->rawColumns(['actions'])

                ->make(true);
        }

        return view('application.commande.index');
    }

    // Formulaire de création
    public function create()
    {
        $fournisseurs = Fournisseur::all();
        $medicaments = Medicament::all();
        $panier = session()->get('commande_panier', []);

        return view('application.commande.create', compact('fournisseurs', 'medicaments', 'panier'));
    }

    // Ajouter un médicament au panier (session)
//    public function ajouterAuPanier($id)
//    {
//        // Charger la commande avec ses produits et le fournisseur
//        $commande = Commande::with(['fournisseur', 'medicaments'])->findOrFail($id);
//
//        // Récupérer les produits liés à la commande
//        $produits = $commande->medicaments->map(function ($medicament) {
//            return [
//                'medicament_id' => $medicament->id,
//                'nom' => $medicament->nom,
//                'quantite_commandee' => $medicament->pivot->quantite,
//                'prix_unitaire' => $medicament->pivot->prix_unitaire,
//                'stock_ancien' => $medicament->stock ?? 0, // si tu as une colonne stock
//            ];
//        });
//
//
//    }


// Supprimer du panier
    public function supprimerDuPanier(Request $request)
    {
        $panier = session('panier', []);
        $id = $request->medicament_id;

        if(isset($panier[$id])){
            unset($panier[$id]);
        }

        session(['panier' => $panier]);
        return response()->json($panier);
    }

// Vider le panier si nécessaire
    public function viderPanier()
    {
        session()->forget('panier');
        return response()->json([]);
    }

    // Vider le panier

    // Enregistrer la commande finale
    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'reference' => 'required|string',
            'date_commande' => 'required|date',
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'medicament_id' => 'required|array',
            'quantite' => 'required|array',
            'prix_unitaire' => 'required|array',
        ]);

        $medicaments = $request->medicament_id;
        $quantites = $request->quantite;
        $prix_unitaires = $request->prix_unitaire;

        // Création de la commande (sans total pour le moment)
        $commande = Commande::create([
            'reference' => $request->reference,
            'date_commande' => $request->date_commande,
            'fournisseur_id' => $request->fournisseur_id,
            'statut' => 'en_cours',
            'total' => 0, // sera mis à jour après
        ]);

        $total_commande = 0;

        // Insertion des lignes de commande
        foreach ($medicaments as $index => $med_id) {
            $quantite = (int) $quantites[$index];
            $prix = (float) $prix_unitaires[$index];
            $total = $quantite * $prix;

            $commande->lignes()->create([
                'commande_id' => $commande->id, // explicitement défini
                'medicament_id' => $med_id,
                'quantite' => $quantite,
                'prix_unitaire' => $prix,
                'total' => $total,
            ]);

            $total_commande += $total;
        }

        // Mise à jour du total de la commande
        $commande->update(['total' => $total_commande]);

        // Vider le panier de session
        session()->forget('panier');

        return redirect()
            ->route('commandes.index')
            ->with('success', 'Commande enregistrée avec succès !');
    }

    public function show($id)
    {
        $commande = Commande::with(['fournisseur', 'lignes.medicament'])->findOrFail($id);

        return view('application.commande.show', compact('commande'));
    }

    public function pdf($id)
    {
        $commande = Commande::with(['fournisseur', 'lignes.medicament'])->findOrFail($id);

        $pdf = PDF::loadView('application.commande.pdf', compact('commande'))
            ->setPaper('A4', 'portrait');

        return $pdf->download('Commande_'.$commande->reference.'.pdf');
    }

}
