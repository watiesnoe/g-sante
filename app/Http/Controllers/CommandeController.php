<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Fournisseur;
use App\Models\Medicament;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class CommandeController extends Controller
{
    // Liste des commandes
   

    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('stock.commandes'), 403, 'Accès non autorisé : vous n\'avez pas accès à la gestion des commandes.');

        if ($request->ajax()) {

            $commandes = DB::table('commandes')
                ->join('fournisseurs', 'commandes.fournisseur_id', '=', 'fournisseurs.id')
                ->select([
                    'commandes.id',
                    'commandes.uuid',
                    'commandes.reference',
                    'commandes.date_commande',
                    'commandes.statut',
                    'commandes.total',
                    'fournisseurs.nom as fournisseur_nom'
                ])
                ->orderBy('commandes.created_at', 'desc');

            return DataTables::of($commandes)

                // Référence
                ->addColumn('reference', function ($row) {
                    return '<strong>'.$row->reference.'</strong>';
                })

                // Fournisseur
                ->addColumn('fournisseur', function ($row) {
                    return $row->fournisseur_nom ?? '-';
                })

            // Date formatée
            ->editColumn('date_commande', function ($row) {
                return $row->date_commande;
            })

            // Statut stylé
            ->editColumn('statut', function ($row) {
                return self::formatStatutBadge($row->statut);
            })

            // Total formaté
            ->editColumn('total', function ($row) {
                return number_format($row->total, 0, ',', ' ') . ' FCFA';
            })

            // Actions
            ->addColumn('actions', function ($row) {
                $user = Auth::user();
                $view = ''; $edit = ''; $pdf = ''; $delete = '';

                if ($user->can('stock.commandes')) {
                    $view   = '<a href="'.route('commandes.show', $row->uuid).'" class="btn btn-sm btn-outline-primary" title="Voir"><i class="fa fa-eye"></i></a>';
                    $pdf    = '<a href="'.route('commandes.pdf', $row->uuid).'" target="_blank" class="btn btn-sm btn-outline-danger" title="Imprimer PDF"><i class="fa fa-file-pdf"></i></a>';
                }
                
                // Assuming edit/delete fall under stock.commandes for now since there's no specific commande.edit permission
                if ($user->can('stock.commandes') && $row->statut != 'valide') {
                    $edit   = '<a href="'.route('commandes.edit', $row->uuid).'" class="btn btn-sm btn-outline-info" title="Modifier"><i class="fa fa-pencil-alt"></i></a>';
                    $delete = '<button type="button" class="btn btn-sm btn-outline-danger btnSupprimer" data-id="'.$row->uuid.'" title="Supprimer"><i class="fa fa-trash"></i></button>';
                }

                return '<div class="d-flex align-items-center justify-content-center gap-1">' . $view . $edit . $pdf . $delete . '</div>';
            })
            ->rawColumns(['reference', 'statut', 'actions'])

            ->make(true);
    }

    return view('application.commande.index');
}

    // Formulaire de création
    public function create()
    {
        abort_unless(Auth::user()->can('stock.commandes'), 403, 'Accès non autorisé.');

        $fournisseurs = DB::table('fournisseurs')->select('id', 'nom')->get();
        $medicaments = DB::table('medicaments')
            ->leftJoin('unites', function($join) {
                $join->on('unites.medicament_id', '=', 'medicaments.id')
                     ->where('unites.is_default', '=', true);
            })
            ->select('medicaments.id', 'medicaments.nom', 'unites.prix_achat as prix_achat', 'medicaments.stock')
            ->get();
        $panier = session()->get('commande_panier', []);

        // Assurer la présence des prix pour les anciens paniers en session
        foreach($panier as $id => &$item) {
            if(!isset($item['prix_unitaire']) || $item['prix_unitaire'] <= 0) {
                $m = DB::table('unites')
                    ->where('medicament_id', $id)
                    ->where('is_default', true)
                    ->first(['prix_achat']);
                if($m) $item['prix_unitaire'] = $m->prix_achat;
            }
        }
        session(['commande_panier' => $panier]);

        return view('application.commande.create', compact('fournisseurs', 'medicaments', 'panier'));
    }

    // Ajouter un médicament au panier (session)
    public function ajouterAuPanier(Request $request)
    {
        $id = $request->medicament_id;
        $medicament = Medicament::with('uniteDefault')->findOrFail($id);
        
        $panier = session()->get('commande_panier', []);
        
        if(isset($panier[$id])) {
            $panier[$id]['quantite']++;
        } else {
            $panier[$id] = [
                'id' => $id,
                'nom' => $medicament->nom,
                'quantite' => 1,
                'prix_unitaire' => $medicament->uniteDefault->prix_achat ?? 0,
            ];
        }
        
        session()->put('commande_panier', $panier);
        return response()->json($panier);
    }

    // Modifier un item dans le panier
    public function modifierPanier(Request $request)
    {
        $id = $request->medicament_id;
        $panier = session()->get('commande_panier', []);

        if(isset($panier[$id])){
            $panier[$id]['quantite'] = $request->quantite;
            $panier[$id]['prix_unitaire'] = $request->prix_unitaire;
            session(['commande_panier' => $panier]);
        }

        return response()->json($panier);
    }

    // Supprimer du panier
    public function supprimerDuPanier(Request $request)
    {
        $panier = session('commande_panier', []);
        $id = $request->medicament_id;

        if(isset($panier[$id])){
            unset($panier[$id]);
        }

        session(['commande_panier' => $panier]);
        return response()->json($panier);
    }

    // Vider le panier
    public function viderPanier()
    {
        session()->forget('commande_panier');
        return response()->json([]);
    }

    // Vider le panier

    // Enregistrer la commande finale
    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('stock.commandes'), 403, 'Accès non autorisé.');

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
        session()->forget('commande_panier');

        return redirect()
            ->route('commandes.index')
            ->with('success', 'Commande enregistrée avec succès !');
    }

    public function bulkAjouterAuPanier(Request $request)
    {
        $ids = $request->medicament_ids;
        if(!$ids || !is_array($ids)) {
            return redirect()->back()->with('error', 'Aucun médicament sélectionné.');
        }

        $medicaments = Medicament::with('uniteDefault')->whereIn('uuid', $ids)->get();
        
        $panier = session()->get('commande_panier', []);

        foreach($medicaments as $m) {
            if(isset($panier[$m->id])) {
                $panier[$m->id]['quantite']++;
            } else {
                $panier[$m->id] = [
                    'id' => $m->id,
                    'nom' => $m->nom,
                    'quantite' => 1,
                    'prix_unitaire' => $m->uniteDefault->prix_achat ?? 0,
                ];
            }
        }

        session(['commande_panier' => $panier]);

        return redirect()->route('commandes.create');
    }

    public function edit(Commande $commande)
    {
        abort_unless(Auth::user()->can('stock.commandes'), 403, 'Accès non autorisé.');

        $commande->load('lignes.medicament');
        $fournisseurs = DB::table('fournisseurs')->select('id', 'nom')->get();
        $medicaments = DB::table('medicaments')
            ->leftJoin('unites', function($join) {
                $join->on('unites.medicament_id', '=', 'medicaments.id')
                     ->where('unites.is_default', '=', true);
            })
            ->select('medicaments.id', 'medicaments.nom', 'unites.prix_achat as prix_achat', 'medicaments.stock')
            ->get();

        // Charger les lignes dans le panier de session
        $panier = [];
        foreach($commande->lignes as $ligne) {
            $panier[$ligne->medicament_id] = [
                'id' => $ligne->medicament_id,
                'nom' => $ligne->medicament->nom ?? 'Inconnu',
                'quantite' => $ligne->quantite,
                'prix_unitaire' => $ligne->prix_unitaire,
            ];
        }
        session(['commande_panier' => $panier]);

        return view('application.commande.create', compact('commande', 'fournisseurs', 'medicaments', 'panier'));
    }

    public function update(Request $request, Commande $commande)
    {
        abort_unless(Auth::user()->can('stock.commandes'), 403, 'Accès non autorisé.');

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

        $commande->update([
            'reference' => $request->reference,
            'date_commande' => $request->date_commande,
            'fournisseur_id' => $request->fournisseur_id,
        ]);

        // Supprimer les anciennes lignes
        $commande->lignes()->delete();

        $total_commande = 0;
        foreach ($medicaments as $index => $med_id) {
            $quantite = (int) $quantites[$index];
            $prix = (float) $prix_unitaires[$index];
            $total = $quantite * $prix;

            $commande->lignes()->create([
                'medicament_id' => $med_id,
                'quantite' => $quantite,
                'prix_unitaire' => $prix,
                'total' => $total,
            ]);

            $total_commande += $total;
        }

        $commande->update(['total' => $total_commande]);

        session()->forget('commande_panier');

        return redirect()
            ->route('commandes.index')
            ->with('success', 'Commande mise à jour avec succès !');
    }

    public function show(Commande $commande)
    {
        abort_unless(Auth::user()->can('stock.commandes'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les commandes.');

        $commande->load(['fournisseur', 'lignes.medicament']);
        return view('application.commande.show', compact('commande'));
    }

    public function pdf(Commande $commande)
    {
        abort_unless(Auth::user()->can('stock.commandes'), 403, 'Accès non autorisé.');

        $commande->load(['fournisseur', 'lignes.medicament']);

        $pdf = Pdf::loadView('application.commande.pdf', compact('commande'))
            ->setPaper('A4', 'portrait');

        return $pdf->download('Commande_'.$commande->reference.'.pdf');
    }

    public function destroy(Commande $commande)
    {
        abort_unless(Auth::user()->can('stock.commandes'), 403, 'Accès non autorisé.');

        $commande->lignes()->delete();
        $commande->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Retourner les médicaments d'une commande (AJAX).
     */
    public function medicaments(Commande $commande)
    {
        $commande->load('lignes.medicament');

        $medicaments = $commande->lignes->map(function ($ligne) {
            return [
                'id'            => $ligne->medicament_id,
                'nom'           => $ligne->medicament->nom ?? 'Inconnu',
                'quantite'      => $ligne->quantite,
                'prix_unitaire' => $ligne->prix_unitaire,
                'total'         => $ligne->total,
            ];
        });

        return response()->json($medicaments);
    }
    /**
     * Formate un statut de commande en badge HTML lisible.
     * Remplace les underscores par des espaces et capitalise.
     */
    public static function formatStatutBadge(string $statut): string
    {
        $map = [
            'en_cours'    => ['label' => 'En cours',    'color' => 'primary'],
            'en_attente'  => ['label' => 'En attente',  'color' => 'warning text-dark'],
            'valide'      => ['label' => 'Validée',     'color' => 'success'],
            'validée'     => ['label' => 'Validée',     'color' => 'success'],
            'annuler'     => ['label' => 'Annulée',     'color' => 'danger'],
            'annulée'     => ['label' => 'Annulée',     'color' => 'danger'],
            'livraison'   => ['label' => 'En livraison','color' => 'info'],
        ];

        if (isset($map[$statut])) {
            $info = $map[$statut];
        } else {
            // Fallback générique : underscore → espace + ucfirst
            $info = [
                'label' => ucfirst(str_replace('_', ' ', $statut)),
                'color' => 'secondary',
            ];
        }

        return '<span class="badge bg-' . $info['color'] . '">' . $info['label'] . '</span>';
    }
}