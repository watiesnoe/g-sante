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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ReceptionController extends Controller
{

    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('stock.receptions'), 403, 'Accès non autorisé : vous n\'avez pas accès à la gestion des réceptions.');

        if ($request->ajax()) {

            $data = Reception::with(['commande.lignes', 'fournisseur', 'lignes'])
                ->latest();

            return DataTables::of($data)

                ->addColumn('reference', function($row){
                    return '<span class="badge bg-info">'.($row->reference_reception ?? 'N/A').'</span>';
                })

                ->addColumn('commande', function($row){
                    if ($row->commande) {
                        return '<a href="'.route('commandes.show', $row->commande).'">'.
                            ($row->commande->reference ?? 'CMD-'.$row->commande->id).
                            '</a>';
                    }
                    return '<span class="text-muted">Non liée</span>';
                })

                ->addColumn('fournisseur', function($row){
                    return $row->fournisseur->nom ?? $row->commande->fournisseur->nom ?? 'N/A';
                })

                ->addColumn('date', function($row){
                    return \Carbon\Carbon::parse($row->date_reception)->format('d-m-Y');
                })

                ->addColumn('actions', function($row){
                    $user = Auth::user();
                    $view = ''; $pdf = ''; $delete = '';
                    $actions = '';

                    if ($user->can('stock.receptions')) {
                        $view   = '<a href="'.route('receptions.show', $row).'" class="btn btn-sm btn-outline-primary" title="Voir les détails"><i class="fa fa-eye"></i></a>';
                        $pdf    = '<a href="'.route('receptions.pdf', $row).'" target="_blank" class="btn btn-sm btn-outline-danger" title="Imprimer PDF"><i class="fa fa-file-pdf"></i></a>';
                        $delete = '<button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('.$row->id.', \''.$row->reference_reception.'\')" title="Supprimer"><i class="fa fa-trash"></i></button>';
                        
                        $actions = '<div class="d-flex align-items-center justify-content-center gap-1">' . $view . $pdf . $delete . '</div>';
                        $actions .= '<form id="delete-form-'.$row->id.'" method="POST" action="'.route('receptions.destroy', $row).'" style="display:none;">'.csrf_field().method_field('DELETE').'</form>';
                    }

                    return $actions;
                })

                ->rawColumns(['reference','commande','actions'])
                ->make(true);
        }

        return view('application.reception.index');
    }


       public function create()
       {
           abort_unless(Auth::user()->can('stock.receptions'), 403, 'Accès non autorisé.');

           $commandes = Commande::with('fournisseur')->get();
           return view('application.reception.create', compact('commandes'));
       }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('stock.receptions'), 403, 'Accès non autorisé.');

        $request->validate([
            'commande_id'                          => 'required|exists:commandes,id',
            'fournisseur_id'                       => 'required',
            'date_reception'                       => 'required|date',
            'receptions.*.commande_medicament_id'  => 'required|exists:commande_medicaments,id',
            'receptions.*.medicament_id'           => 'required|exists:medicaments,id',
            'receptions.*.unite_id'                => 'nullable|exists:unites,id',
            'receptions.*.quantite_recue'          => 'required|numeric|min:0',
            'receptions.*.prix_unitaire'           => 'required|numeric|min:0',
        ]);

        // ✅ Validation qu'au moins une quantité reçue est > 0
        $totalRecuGlobal = 0;
        if ($request->has('receptions')) {
            foreach ($request->receptions as $l) {
                $totalRecuGlobal += (float) ($l['quantite_recue'] ?? 0);
            }
        }

        if ($totalRecuGlobal <= 0) {
            return response()->json(['message' => 'Veuillez saisir au moins une quantité reçue supérieure à 0.'], 422);
        }

        // ✅ Génération de la référence côté serveur
        $referenceReception = 'REC-' . strtoupper(Str::random(8)) . '-' . now()->format('dmY');

        return DB::transaction(function () use ($request, $referenceReception) {

            // ✅ Création de la réception principale
            $reception = Reception::create([
                'commande_id'         => $request->commande_id,
                'fournisseur_id'      => $request->fournisseur_id,
                'date_reception'      => $request->date_reception,
                'reference_reception' => $referenceReception,
                'user_id'             => auth()->id(),
            ]);

            if ($request->has('receptions')) {
                foreach ($request->receptions as $ligne) {
                    $qteRecue = (float) ($ligne['quantite_recue'] ?? 0);
                    if ($qteRecue <= 0) {
                        continue; // Ne pas enregistrer de ligne pour les produits non reçus (quantité 0)
                    }

                    // 🔒 Verrou pessimiste
                    $commandeMedicament = DB::table('commande_medicaments')
                        ->where('id', $ligne['commande_medicament_id'])
                        ->lockForUpdate()
                        ->first();

                    if (!$commandeMedicament) {
                        abort(422, 'Produit non trouvé dans la commande.');
                    }

                    $quantiteCommandee     = $commandeMedicament->quantite;
                    $quantiteRecueActuelle = $commandeMedicament->quantiterecue ?? 0;
                    $nouvelleQuantiteTotale = $quantiteRecueActuelle + $qteRecue;

                    // ❌ Blocage si la quantité totale dépasse la commande
                    if ($nouvelleQuantiteTotale > $quantiteCommandee) {
                        abort(422, "Quantité reçue ({$nouvelleQuantiteTotale}) dépasse la quantité commandée ({$quantiteCommandee}) pour le médicament ID {$ligne['medicament_id']}.");
                    }

                    $uniteId = $ligne['unite_id'] ?? $commandeMedicament->unite_id ?? null;
                    $facteur = 1.0;
                    if ($uniteId) {
                        $uniteObj = DB::table('unites')->where('id', $uniteId)->first();
                        if ($uniteObj && $uniteObj->facteur > 0) {
                            $facteur = (float) $uniteObj->facteur;
                        }
                    }

                    // ✅ Enregistrer la ligne de réception
                    $reception->lignes()->create([
                        'medicament_id'     => $ligne['medicament_id'],
                        'unite_id'          => $uniteId,
                        'quantite_commandee'=> $quantiteCommandee,
                        'quantite_recue'    => $qteRecue,
                        'prix_unitaire'     => $ligne['prix_unitaire'],
                        'lot'               => $ligne['lot'] ?? null,
                        'date_peremption'   => $ligne['date_peremption'] ?? null,
                    ]);

                    // 🔁 Mettre à jour quantiterecue dans la commande
                    DB::table('commande_medicaments')
                        ->where('id', $ligne['commande_medicament_id'])
                        ->update(['quantiterecue' => $nouvelleQuantiteTotale]);

                    // 📦 Incrémenter le stock de base du médicament (quantité reçue * facteur)
                    $quantiteBaseIncr = $qteRecue * $facteur;
                    if ($quantiteBaseIncr > 0) {
                        DB::table('medicaments')
                            ->where('id', $ligne['medicament_id'])
                            ->increment('stock', $quantiteBaseIncr);
                    }
                }
            }

            // 🟢 Vérifier si la commande est complètement reçue
            $lignes      = DB::table('commande_medicaments')->where('commande_id', $request->commande_id)->get();
            $toutesRecues = $lignes->every(fn($l) => $l->quantite == $l->quantiterecue);

            // 🏁 Mise à jour du statut de la commande
            DB::table('commandes')
                ->where('id', $request->commande_id)
                ->update(['statut' => $toutesRecues ? 'valide' : 'en_cours']);

            return response()->json([
                'message'              => 'Réception enregistrée avec succès !',
                'reception_id'         => $reception->id,
                'reference_reception'  => $referenceReception,
            ]);
        });
    }

    public function getProduits($id)
    {
        abort_unless(Auth::user()->can('stock.receptions'), 403, 'Accès non autorisé.');

        $commande = Commande::findOrFail($id);
        $commande->load(['fournisseur', 'lignes.medicament', 'lignes.unite']);

        // Produits non complètement reçus
        $produits = $commande->lignes
            ->filter(function ($ligne) {
                return $ligne->quantiterecue < $ligne->quantite;
            })
            ->map(function ($ligne) {
                $uniteNom = $ligne->unite ? $ligne->unite->nom : '';
                $facteur  = $ligne->unite ? (float) $ligne->unite->facteur : 1.0;
                return [
                    'commande_medicament_id' => $ligne->id,
                    'medicament_id'          => $ligne->medicament_id,
                    'unite_id'               => $ligne->unite_id,
                    'unite_nom'              => $uniteNom,
                    'facteur'                => $facteur,
                    'nom'                    => $ligne->medicament->nom ?? '',
                    'quantite_commandee'     => $ligne->quantite,
                    'quantite_recue'         => $ligne->quantiterecue ?? 0,
                    'quantite_restante'      => ($ligne->quantite - ($ligne->quantiterecue ?? 0)),
                    'prix_unitaire'          => $ligne->prix_unitaire,
                    'stock_ancien'           => $ligne->medicament->stock ?? 0,
                ];
            })
            ->values();

        return response()->json([
            'fournisseur_id'  => $commande->fournisseur_id,
            'fournisseur_nom' => $commande->fournisseur->nom ?? '',
            'produits'        => $produits
        ]);
    }

    public function show(Reception $reception)
    {
        abort_unless(Auth::user()->can('stock.receptions'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les réceptions.');

        $reception->load(['commande.fournisseur', 'fournisseur', 'lignes.medicament', 'lignes.unite', 'user']);
        return view('application.reception.show', compact('reception'));
    }

    public function pdf(Reception $reception)
    {
        abort_unless(Auth::user()->can('stock.receptions'), 403, 'Accès non autorisé.');

        $reception->load(['commande.fournisseur', 'fournisseur', 'lignes.medicament', 'lignes.unite', 'user']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('application.reception.pdf', compact('reception'))
            ->setPaper('A4', 'portrait');

        return $pdf->download('Bon_Reception_'.$reception->reference_reception.'.pdf');
    }

    public function edit(Reception $reception)
    {
        abort_unless(Auth::user()->can('stock.receptions'), 403, 'Accès non autorisé.');

        $reception->load(['commande', 'fournisseur', 'lignes.medicament', 'lignes.unite']);
        
        if ($reception->commande && $reception->commande->statut === 'valide') {
            return redirect()->route('receptions.index')->with('error', 'Modification bloquée : La commande a déjà été totalement réceptionnée.');
        }
        
        $commande = Commande::with(['lignes.medicament', 'lignes.unite'])->findOrFail($reception->commande_id);
        
        $produits = $commande->lignes->map(function ($ligne) use ($reception) {
            $ligneReception = $reception->lignes->where('medicament_id', $ligne->medicament_id)->first();
            
            $quantite_recue_courante = $ligneReception ? $ligneReception->quantite_recue : 0;
            $lot = $ligneReception ? $ligneReception->lot : '';
            $date_peremption = $ligneReception ? $ligneReception->date_peremption : '';
            
            $quantite_totale_recue = $ligne->quantiterecue ?? 0;
            // Quantité restante = Qte commandée - (Qte totale reçue - Qte reçue dans cette réception)
            $quantite_restante = $ligne->quantite - ($quantite_totale_recue - $quantite_recue_courante);

            $uniteId  = $ligneReception->unite_id ?? $ligne->unite_id;
            $uniteNom = ($ligneReception && $ligneReception->unite) ? $ligneReception->unite->nom : ($ligne->unite ? $ligne->unite->nom : '');
            $facteur  = ($ligneReception && $ligneReception->unite) ? (float) $ligneReception->unite->facteur : ($ligne->unite ? (float) $ligne->unite->facteur : 1.0);
            
            return [
                'commande_medicament_id' => $ligne->id,
                'medicament_id'          => $ligne->medicament_id,
                'unite_id'               => $uniteId,
                'unite_nom'              => $uniteNom,
                'facteur'                => $facteur,
                'nom'                    => $ligne->medicament->nom ?? '',
                'quantite_commandee'     => $ligne->quantite,
                'quantite_recue'         => $quantite_recue_courante,
                'quantite_restante'      => max(0, $quantite_restante),
                'prix_unitaire'          => $ligne->prix_unitaire,
                'stock_ancien'           => $ligne->medicament->stock ?? 0,
                'lot'                    => $lot,
                'date_peremption'        => $date_peremption,
            ];
        });

        return view('application.reception.edit', compact('reception', 'produits'));
    }

    public function update(Request $request, Reception $reception)
    {
        abort_unless(Auth::user()->can('stock.receptions'), 403, 'Accès non autorisé.');

        $request->validate([
            'date_reception' => 'required|date',
            'reference_reception' => 'required|string',
            'receptions.*.commande_medicament_id' => 'required|exists:commande_medicaments,id',
            'receptions.*.medicament_id' => 'required|exists:medicaments,id',
            'receptions.*.unite_id' => 'nullable|exists:unites,id',
            'receptions.*.quantite_recue' => 'required|numeric|min:0',
        ]);

        $reception->load('lignes');

        // ✅ Validation qu'au moins une quantité reçue est > 0
        $totalRecuGlobal = 0;
        if ($request->has('receptions')) {
            foreach ($request->receptions as $l) {
                $totalRecuGlobal += (float) ($l['quantite_recue'] ?? 0);
            }
        }

        if ($totalRecuGlobal <= 0) {
            return response()->json(['message' => 'Veuillez saisir au moins une quantité reçue supérieure à 0.'], 422);
        }
        
        return DB::transaction(function () use ($request, $reception) {

        $reception->update([
            'date_reception' => $request->date_reception,
            'reference_reception' => $request->reference_reception,
        ]);

        if ($request->has('receptions')) {
            foreach ($request->receptions as $ligne) {
                // 🔒 Verrou pessimiste pour éviter les dépassements en cas d'accès concurrent
                $commandeMedicament = DB::table('commande_medicaments')
                    ->where('id', $ligne['commande_medicament_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$commandeMedicament) continue;

                $ligneReceptionExistante = $reception->lignes->where('medicament_id', $ligne['medicament_id'])->first();

                $uniteId = $ligne['unite_id'] ?? $ligneReceptionExistante?->unite_id ?? $commandeMedicament->unite_id ?? null;
                $facteur = 1.0;
                if ($uniteId) {
                    $uniteObj = DB::table('unites')->where('id', $uniteId)->first();
                    if ($uniteObj && $uniteObj->facteur > 0) {
                        $facteur = (float) $uniteObj->facteur;
                    }
                }

                $ancienneQteRecue = $ligneReceptionExistante ? (float) $ligneReceptionExistante->quantite_recue : 0.0;
                $nouvelleQuantiteRecue = (float) $ligne['quantite_recue'];

                // ❌ Blocage si la nouvelle quantité totale (toutes réceptions confondues) dépasse la quantité commandée
                $autresReceptionsRecu = (float) DB::table('reception_lignes')
                    ->join('receptions', 'reception_lignes.reception_id', '=', 'receptions.id')
                    ->where('receptions.commande_id', $reception->commande_id)
                    ->where('reception_lignes.medicament_id', $ligne['medicament_id'])
                    ->where('receptions.id', '!=', $reception->id)
                    ->sum('reception_lignes.quantite_recue');

                if ($autresReceptionsRecu + $nouvelleQuantiteRecue > $commandeMedicament->quantite) {
                    abort(422, "Quantité reçue (" . ($autresReceptionsRecu + $nouvelleQuantiteRecue) . ") dépasse la quantité commandée ({$commandeMedicament->quantite}) pour le médicament ID {$ligne['medicament_id']}.");
                }

                if ($nouvelleQuantiteRecue > 0) {
                    if ($ligneReceptionExistante) {
                        $ligneReceptionExistante->update([
                            'unite_id' => $uniteId,
                            'quantite_recue' => $nouvelleQuantiteRecue,
                            'lot' => $ligne['lot'] ?? null,
                            'date_peremption' => $ligne['date_peremption'] ?? null,
                        ]);
                    } else {
                        $reception->lignes()->create([
                            'medicament_id' => $ligne['medicament_id'],
                            'unite_id' => $uniteId,
                            'quantite_commandee' => $commandeMedicament->quantite,
                            'quantite_recue' => $nouvelleQuantiteRecue,
                            'prix_unitaire' => $commandeMedicament->prix_unitaire,
                            'lot' => $ligne['lot'] ?? null,
                            'date_peremption' => $ligne['date_peremption'] ?? null,
                        ]);
                    }
                } elseif ($ligneReceptionExistante) {
                    $ligneReceptionExistante->delete();
                }

                // 📦 Ajuster le stock du médicament (plafonné à 0 au minimum)
                $diffQte = $nouvelleQuantiteRecue - $ancienneQteRecue;
                if ($diffQte != 0) {
                    $diffBase = $diffQte * $facteur;
                    if ($diffBase > 0) {
                        DB::table('medicaments')->where('id', $ligne['medicament_id'])->increment('stock', $diffBase);
                    } else {
                        $absDiff = abs($diffBase);
                        $medicamentActuel = DB::table('medicaments')
                            ->where('id', $ligne['medicament_id'])
                            ->lockForUpdate()
                            ->first();
                        $nouveauStock = max(0, ($medicamentActuel->stock ?? 0) - $absDiff);
                        DB::table('medicaments')
                            ->where('id', $ligne['medicament_id'])
                            ->update(['stock' => $nouveauStock]);
                    }
                }

                // Recalculer le total recu
                $totalRecu = DB::table('reception_lignes')
                    ->join('receptions', 'reception_lignes.reception_id', '=', 'receptions.id')
                    ->where('receptions.commande_id', $reception->commande_id)
                    ->where('reception_lignes.medicament_id', $ligne['medicament_id'])
                    ->sum('reception_lignes.quantite_recue');

                DB::table('commande_medicaments')
                    ->where('id', $commandeMedicament->id)
                    ->update(['quantiterecue' => $totalRecu]);
            }
        }

        $lignes = DB::table('commande_medicaments')->where('commande_id', $reception->commande_id)->get();
        $toutesRecues = $lignes->every(fn($l) => $l->quantite <= $l->quantiterecue);

        DB::table('commandes')
            ->where('id', $reception->commande_id)
            ->update(['statut' => $toutesRecues ? 'valide' : 'en_cours']);

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Réception modifiée avec succès !',
                'reception_id' => $reception->id,
            ]);
        }

        return redirect()->route('receptions.index')->with('success', 'Réception mise à jour avec succès.');

        });
    }

    public function destroy(Reception $reception)
    {
        abort_unless(Auth::user()->can('stock.receptions'), 403, 'Accès non autorisé.');

        return DB::transaction(function () use ($reception) {
            $reception->load('lignes.unite');
            $commandeId = $reception->commande_id;

            foreach ($reception->lignes as $ligne) {
                // 1. Restitution de la quantité reçue sur la ligne de commande
                $query = DB::table('commande_medicaments')
                    ->where('commande_id', $commandeId)
                    ->where('medicament_id', $ligne->medicament_id);

                if ($ligne->unite_id) {
                    $matched = (clone $query)->where('unite_id', $ligne->unite_id)->first();
                    $commandeMedicament = $matched ?? $query->first();
                } else {
                    $commandeMedicament = $query->first();
                }

                if ($commandeMedicament) {
                    $nouvelleQuantite = max(0, $commandeMedicament->quantiterecue - $ligne->quantite_recue);
                    DB::table('commande_medicaments')
                        ->where('id', $commandeMedicament->id)
                        ->update(['quantiterecue' => $nouvelleQuantite]);
                }

                // 2. Restitution du stock médicament (décrémentation des unités de base entrées, sans descendre sous 0)
                $facteur = $ligne->unite ? (float) $ligne->unite->facteur : 1.0;
                $qteBase = $ligne->quantite_recue * $facteur;
                if ($qteBase > 0) {
                    $medicamentActuel = DB::table('medicaments')
                        ->where('id', $ligne->medicament_id)
                        ->lockForUpdate()
                        ->first();
                    $nouveauStock = max(0, ($medicamentActuel->stock ?? 0) - $qteBase);
                    DB::table('medicaments')
                        ->where('id', $ligne->medicament_id)
                        ->update(['stock' => $nouveauStock]);
                }
            }
            
            // 3. Supprimer les lignes et la réception
            $reception->lignes()->delete();
            $reception->delete();

            // 4. Mettre à jour le statut global de la commande
            if ($commandeId) {
                $lignes = DB::table('commande_medicaments')->where('commande_id', $commandeId)->get();
                $toutesRecues = $lignes->count() > 0 && $lignes->every(fn($l) => $l->quantite <= $l->quantiterecue);
                $nouveauStatut = $toutesRecues ? 'valide' : 'en_cours';

                DB::table('commandes')
                    ->where('id', $commandeId)
                    ->update(['statut' => $nouveauStatut]);
            }

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Réception supprimée et quantités restituées avec succès !',
                ]);
            }

            return redirect()->route('receptions.index')->with('success', 'Réception supprimée et quantités restituées avec succès.');
        });
    }
}
