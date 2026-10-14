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

            $commandes = Commande::with(['fournisseur', 'lignes', 'receptions', 'paiements'])
                ->orderBy('created_at', 'desc');

            return DataTables::of($commandes)

                // Référence
                ->addColumn('reference', function ($row) {
                    return '<strong>'.$row->reference.'</strong>';
                })

                // Fournisseur
                ->addColumn('fournisseur', function ($row) {
                    return $row->fournisseur->nom ?? '-';
                })

                // Date formatée
                ->editColumn('date_commande', function ($row) {
                    return \Carbon\Carbon::parse($row->date_commande)->format('d/m/Y');
                })

                // Statut stylé
                ->editColumn('statut', function ($row) {
                    return self::formatStatutBadge($row->statut);
                })

                // Avancement de la réception
                ->addColumn('avancement', function ($row) {
                    $totalCmd = $row->lignes->sum('quantite');
                    $totalRecu = $row->lignes->sum('quantiterecue');

                    if ($totalCmd > 0) {
                        $percent = min(100, round(($totalRecu / $totalCmd) * 100));
                        $color = $percent >= 100 ? 'bg-success' : ($percent > 0 ? 'bg-primary' : 'bg-secondary');
                        return '<div class="progress text-center" style="height: 18px; min-width:80px;" title="'.$percent.'% de la commande reçue">
                                  <div class="progress-bar '.$color.'" role="progressbar" style="width: '.$percent.'%; font-size:11px; font-weight:bold; line-height:18px;" aria-valuenow="'.$percent.'" aria-valuemin="0" aria-valuemax="100">'.$percent.'%</div>
                                </div>';
                    }
                    return '<span class="badge bg-secondary">0%</span>';
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
                    
                    // Bloquer la modification et la suppression dès qu'une réception ou un paiement a été effectué
                    $aEteReceptionnee = $row->receptions->count() > 0 || $row->lignes->sum('quantiterecue') > 0;
                    $aDesPaiements    = $row->paiements->count() > 0;
                    $estVerrouillee   = $row->statut === 'valide' || $aEteReceptionnee || $aDesPaiements;

                    if ($user->can('stock.commandes') && !$estVerrouillee) {
                        $edit   = '<a href="'.route('commandes.edit', $row->uuid).'" class="btn btn-sm btn-outline-info" title="Modifier"><i class="fa fa-pencil-alt"></i></a>';
                        $delete = '<button type="button" class="btn btn-sm btn-outline-danger btnSupprimer" data-id="'.$row->uuid.'" title="Supprimer"><i class="fa fa-trash"></i></button>';
                    }

                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $view . $edit . $pdf . $delete . '</div>';
                })
                ->rawColumns(['reference', 'statut', 'avancement', 'actions'])

                ->make(true);
        }

    return view('application.commande.index');
}

    // Formulaire de création
    public function create()
    {
        abort_unless(Auth::user()->can('stock.commandes'), 403, 'Accès non autorisé.');

        $fournisseurs = \App\Models\Etablissement::table('fournisseurs')->select('id', 'nom')->get();
        $medicaments = Medicament::with('unites')->get();
        $panier = session()->get('commande_panier', []);

        // Assurer la présence des unités et prix pour les items du panier
        foreach($panier as $id => &$item) {
            $m = Medicament::with('unites')->find($id);
            if ($m) {
                $item['unites'] = $m->unites->map(fn($u) => [
                    'id' => $u->id,
                    'nom' => $u->nom,
                    'prix_achat' => $u->prix_achat,
                    'is_default' => $u->is_default,
                ])->toArray();

                if (empty($item['unite_id'])) {
                    $def = $m->uniteDefault ?? $m->unites->first();
                    $item['unite_id'] = $def->id ?? null;
                    if (!isset($item['prix_unitaire']) || $item['prix_unitaire'] <= 0) {
                        $item['prix_unitaire'] = $def->prix_achat ?? 0;
                    }
                }
            }
        }
        session(['commande_panier' => $panier]);

        return view('application.commande.create', compact('fournisseurs', 'medicaments', 'panier'));
    }

    // Ajouter un médicament au panier (session)
    public function ajouterAuPanier(Request $request)
    {
        abort_unless(Auth::user()->can('stock.commandes'), 403, 'Accès non autorisé.');

        $id = $request->medicament_id;
        $medicament = Medicament::with(['unites', 'uniteDefault'])->findOrFail($id);
        
        $panier = session()->get('commande_panier', []);
        $uniteDefaut = $medicament->uniteDefault ?? $medicament->unites->first();
        
        if(isset($panier[$id])) {
            $panier[$id]['quantite']++;
        } else {
            $panier[$id] = [
                'id' => $id,
                'nom' => $medicament->nom,
                'quantite' => 1,
                'unite_id' => $uniteDefaut ? $uniteDefaut->id : null,
                'prix_unitaire' => $uniteDefaut->prix_achat ?? 0,
                'unites' => $medicament->unites->map(fn($u) => [
                    'id' => $u->id,
                    'nom' => $u->nom,
                    'prix_achat' => $u->prix_achat,
                    'is_default' => $u->is_default,
                ])->toArray(),
            ];
        }
        
        session()->put('commande_panier', $panier);
        return response()->json($panier);
    }

    // Modifier un item dans le panier
    public function modifierPanier(Request $request)
    {
        abort_unless(Auth::user()->can('stock.commandes'), 403, 'Accès non autorisé.');

        $id = $request->medicament_id;
        $panier = session()->get('commande_panier', []);

        if(isset($panier[$id])){
            $panier[$id]['quantite'] = $request->quantite;
            $panier[$id]['prix_unitaire'] = $request->prix_unitaire;
            if ($request->has('unite_id')) {
                $panier[$id]['unite_id'] = $request->unite_id;
            }
            session(['commande_panier' => $panier]);
        }

        return response()->json($panier);
    }

    // Supprimer du panier
    public function supprimerDuPanier(Request $request)
    {
        abort_unless(Auth::user()->can('stock.commandes'), 403, 'Accès non autorisé.');

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
        abort_unless(Auth::user()->can('stock.commandes'), 403, 'Accès non autorisé.');

        session()->forget('commande_panier');
        return response()->json([]);
    }

    // Enregistrer la commande finale
    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('stock.commandes'), 403, 'Accès non autorisé.');

        // Validation
        $request->validate([
            'reference' => 'required|string',
            'date_commande' => 'required|date',
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'medicament_id' => 'required|array|min:1',
            'medicament_id.*' => 'integer|exists:medicaments,id',
            'quantite' => 'required|array|min:1',
            'quantite.*' => 'integer|min:1',
            'prix_unitaire' => 'required|array|min:1',
            'prix_unitaire.*' => 'numeric|min:0',
            'unite_id' => 'nullable|array',
            'unite_id.*' => 'nullable|integer|exists:unites,id',
        ]);
        

        $medicaments = $request->medicament_id;
        $quantites = $request->quantite;
        $prix_unitaires = $request->prix_unitaire;
        $unites = $request->unite_id ?? [];

        // Création de la commande
        $commande = Commande::create([
            'reference' => $request->reference,
            'date_commande' => $request->date_commande,
            'fournisseur_id' => $request->fournisseur_id,
            'statut' => 'en_cours',
            'total' => 0,
        ]);

        $total_commande = 0;

        // Insertion des lignes de commande
        foreach ($medicaments as $index => $med_id) {
            $quantite = (int) $quantites[$index];
            $prix = (float) $prix_unitaires[$index];
            $unite_id = isset($unites[$index]) && !empty($unites[$index]) ? $unites[$index] : null;
            $total = $quantite * $prix;

            $commande->lignes()->create([
                'commande_id' => $commande->id,
                'medicament_id' => $med_id,
                'unite_id' => $unite_id,
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
            ->with('success', 'Commande enregistrée avec succès !');
    }

    public function bulkAjouterAuPanier(Request $request)
    {
        abort_unless(Auth::user()->can('stock.commandes'), 403, 'Accès non autorisé.');

        $ids = $request->medicament_ids;
        if(!$ids || !is_array($ids)) {
            return redirect()->back()->with('error', 'Aucun médicament sélectionné.');
        }

        $medicaments = Medicament::with(['unites', 'uniteDefault'])->whereIn('uuid', $ids)->get();
        $panier = session()->get('commande_panier', []);

        foreach($medicaments as $m) {
            $uniteDefaut = $m->uniteDefault ?? $m->unites->first();

            if(isset($panier[$m->id])) {
                $panier[$m->id]['quantite']++;
            } else {
                $panier[$m->id] = [
                    'id' => $m->id,
                    'nom' => $m->nom,
                    'quantite' => 1,
                    'unite_id' => $uniteDefaut ? $uniteDefaut->id : null,
                    'prix_unitaire' => $uniteDefaut->prix_achat ?? 0,
                    'unites' => $m->unites->map(fn($u) => [
                        'id' => $u->id,
                        'nom' => $u->nom,
                        'prix_achat' => $u->prix_achat,
                        'is_default' => $u->is_default,
                    ])->toArray(),
                ];
            }
        }

        session(['commande_panier' => $panier]);

        return redirect()->route('commandes.create');
    }

    public function edit(Commande $commande)
    {
        abort_unless(Auth::user()->can('stock.commandes'), 403, 'Accès non autorisé.');

        if ($commande->receptions()->exists() || $commande->paiements()->exists() || $commande->lignes()->where('quantiterecue', '>', 0)->exists() || $commande->statut === 'valide') {
            return redirect()->route('commandes.index')->with('error', 'Modification impossible : cette commande possède déjà des réceptions ou paiements associés.');
        }

        $commande->load(['lignes.medicament.unites', 'lignes.unite']);
        $fournisseurs = \App\Models\Etablissement::table('fournisseurs')->select('id', 'nom')->get();
        $medicaments = Medicament::with('unites')->get();

        // Charger les lignes dans le panier de session
        $panier = [];
        foreach($commande->lignes as $ligne) {
            $unitesMed = $ligne->medicament ? $ligne->medicament->unites->map(fn($u) => [
                'id' => $u->id,
                'nom' => $u->nom,
                'prix_achat' => $u->prix_achat,
                'is_default' => $u->is_default,
            ])->toArray() : [];

            $panier[$ligne->medicament_id] = [
                'id' => $ligne->medicament_id,
                'nom' => $ligne->medicament->nom ?? 'Inconnu',
                'quantite' => $ligne->quantite,
                'unite_id' => $ligne->unite_id,
                'prix_unitaire' => $ligne->prix_unitaire,
                'unites' => $unitesMed,
            ];
        }
        session(['commande_panier' => $panier]);

        return view('application.commande.create', compact('commande', 'fournisseurs', 'medicaments', 'panier'));
    }

    public function update(Request $request, Commande $commande)
    {
        abort_unless(Auth::user()->can('stock.commandes'), 403, 'Accès non autorisé.');

        if ($commande->receptions()->exists() || $commande->paiements()->exists() || $commande->lignes()->where('quantiterecue', '>', 0)->exists() || $commande->statut === 'valide') {
            return redirect()->route('commandes.index')->with('error', 'Modification impossible : cette commande possède déjà des réceptions ou paiements associés.');
        }

        $request->validate([
            'reference' => 'required|string',
            'date_commande' => 'required|date',
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'medicament_id' => 'required|array|min:1',
            'medicament_id.*' => 'integer|exists:medicaments,id',
            'quantite' => 'required|array|min:1',
            'quantite.*' => 'integer|min:1',
            'prix_unitaire' => 'required|array|min:1',
            'prix_unitaire.*' => 'numeric|min:0',
            'unite_id' => 'nullable|array',
            'unite_id.*' => 'nullable|integer|exists:unites,id',
        ]);

        $medicaments = $request->medicament_id;
        $quantites = $request->quantite;
        $prix_unitaires = $request->prix_unitaire;
        $unites = $request->unite_id ?? [];

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
            $unite_id = isset($unites[$index]) && !empty($unites[$index]) ? $unites[$index] : null;
            $total = $quantite * $prix;

            $commande->lignes()->create([
                'medicament_id' => $med_id,
                'unite_id' => $unite_id,
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

        $commande->load(['fournisseur', 'lignes.medicament', 'lignes.unite']);
        return view('application.commande.show', compact('commande'));
    }

    public function pdf(Commande $commande)
    {
        abort_unless(Auth::user()->can('stock.commandes'), 403, 'Accès non autorisé.');

        $commande->load(['fournisseur', 'lignes.medicament', 'lignes.unite']);

        $pdf = Pdf::loadView('application.commande.pdf', compact('commande'))
            ->setPaper('A4', 'portrait');

        return $pdf->download('Commande_'.$commande->reference.'.pdf');
    }

    public function destroy(Commande $commande)
    {
        abort_unless(Auth::user()->can('stock.commandes'), 403, 'Accès non autorisé.');

        $hasReceptions = $commande->receptions()->exists();
        $hasPaiements  = $commande->paiements()->exists();
        $hasQteRecue   = $commande->lignes()->where('quantiterecue', '>', 0)->exists();

        if ($hasReceptions || $hasPaiements || $hasQteRecue || $commande->statut === 'valide') {
            $elements = [];
            if ($hasReceptions || $hasQteRecue) {
                $elements[] = 'des réceptions';
            }
            if ($hasPaiements) {
                $elements[] = 'des paiements';
            }
            if (empty($elements)) {
                $elements[] = 'des opérations';
            }

            $message = "Impossible de supprimer cette commande : " . implode(' et ', $elements) . " y sont déjà associés.";

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()->back()->with('error', $message);
        }

        $commande->lignes()->delete();
        $commande->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Commande supprimée avec succès !'
            ]);
        }

        return redirect()->route('commandes.index')->with('success', 'Commande supprimée avec succès !');
    }

    /**
     * Retourner les médicaments d'une commande (AJAX).
     */
    public function medicaments(Commande $commande)
    {
        abort_unless(Auth::user()->can('stock.commandes'), 403, 'Accès non autorisé.');

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