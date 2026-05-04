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
use Yajra\DataTables\Facades\DataTables;

class ReceptionController extends Controller
{

    public function index(Request $request)
    {
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

                ->addColumn('pourcentage', function($row){
                    if ($row->commande) {
                        $totalCmd = $row->commande->lignes->sum('quantite');
                        $totalRecu = $row->commande->lignes->sum('quantiterecue'); // Total réceptionné sur la commande globale
                        
                        if ($totalCmd > 0) {
                            $percent = round(($totalRecu / $totalCmd) * 100);
                            $color = $percent >= 100 ? 'bg-success' : 'bg-primary';
                            return '<div class="progress text-center" style="height: 18px; min-width:80px;" title="'.$percent.'% de la commande">
                                      <div class="progress-bar '.$color.'" role="progressbar" style="width: '.$percent.'%; font-size:11px; font-weight:bold; line-height:18px;" aria-valuenow="'.$percent.'" aria-valuemin="0" aria-valuemax="100">'.$percent.'%</div>
                                    </div>';
                        }
                    }
                    return '<span class="text-muted">N/A</span>';
                })

                ->addColumn('actions', function($row){
                    $view   = '<a href="'.route('receptions.show', $row->id).'" class="btn btn-sm btn-outline-primary" title="Détails"><i class="fa fa-eye"></i></a>';
                    
                    if (!$row->commande || $row->commande->statut !== 'valide') {
                        $edit = '<a href="'.route('receptions.edit', $row->id).'" class="btn btn-sm btn-outline-info" title="Modifier"><i class="fa fa-pencil-alt"></i></a>';
                    } else {
                        $edit = '<button type="button" class="btn btn-sm btn-outline-secondary disabled" title="Modif. bloquée: commande totalement reçue" style="cursor:not-allowed;"><i class="fa fa-pencil-alt"></i></button>';
                    }

                    $delete = '<button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('.$row->id.', \''.$row->reference_reception.'\')" title="Supprimer"><i class="fa fa-trash"></i></button>';
                    
                    $actions = '<div class="d-flex align-items-center justify-content-center gap-1">' . $view . $edit . $delete . '</div>';
                    $actions .= '<form id="delete-form-'.$row->id.'" method="POST" action="'.route('receptions.destroy', $row->id).'" style="display:none;">'.csrf_field().method_field('DELETE').'</form>';
                    
                    return $actions;
                })

                ->rawColumns(['reference','commande','pourcentage','actions'])
                ->make(true);
        }

        return view('application.reception.index');
    }


       public function create()
       {
           $commandes = Commande::with('fournisseur')->get();
           return view('application.reception.create', compact('commandes'));
       }

    public function store(Request $request)
    {
        $request->validate([
            'commande_id' => 'required|exists:commandes,id',
            'fournisseur_id' => 'required',
            'date_reception' => 'required|date',
            'reference_reception' => 'required|string',
            'receptions.*.commande_medicament_id' => 'required|exists:commande_medicaments,id',
            'receptions.*.medicament_id' => 'required|exists:medicaments,id',
            'receptions.*.quantite_recue' => 'required|numeric|min:0',
            'receptions.*.prix_unitaire' => 'required|numeric|min:0',
        ]);

        // ✅ Création de la réception principale
        $reception = Reception::create([
            'commande_id' => $request->commande_id,
            'fournisseur_id' => $request->fournisseur_id,
            'date_reception' => $request->date_reception,
            'reference_reception' => $request->reference_reception,
            'user_id' => auth()->id(),
            'statut' => 'partielle',
        ]);

        if ($request->has('receptions')) {
            foreach ($request->receptions as $ligne) {
                // 🧩 Récupération de la ligne commande_medicament par son ID
                $commandeMedicament = DB::table('commande_medicaments')
                    ->where('id', $ligne['commande_medicament_id'])
                    ->first();

                if (!$commandeMedicament) {
                    return response()->json(['error' => 'Produit non trouvé dans la commande.'], 422);
                }

                $quantiteCommandee = $commandeMedicament->quantite;
                $quantiteRecueActuelle = $commandeMedicament->quantiterecue ?? 0;
                $nouvelleQuantiteTotale = $quantiteRecueActuelle + $ligne['quantite_recue'];

                // ❌ Validation si quantité reçue > quantité commandée
                if ($nouvelleQuantiteTotale > $quantiteCommandee) {
                    return response()->json([
                        'error' => "Erreur : la quantité reçue ({$nouvelleQuantiteTotale}) dépasse la quantité commandée ({$quantiteCommandee}) pour le médicament ID {$ligne['medicament_id']}."
                    ], 422);
                }

                // ✅ Enregistrer la ligne dans receptions_lignes
                $reception->lignes()->create([
                    'medicament_id' => $ligne['medicament_id'],
                    'quantite_commandee' => $quantiteCommandee,
                    'quantite_recue' => $ligne['quantite_recue'],
                    'prix_unitaire' => $ligne['prix_unitaire'],
                    'lot' => $ligne['lot'] ?? null,
                    'date_peremption' => $ligne['date_peremption'] ?? null,
                ]);

                // 🔁 Mettre à jour la ligne commande_medicament via ID direct
                DB::table('commande_medicaments')
                    ->where('id', $ligne['commande_medicament_id'])
                    ->update(['quantiterecue' => $nouvelleQuantiteTotale]);
            }
        }

        // 🟢 Vérifier si la commande est complètement reçue
        $lignes = DB::table('commande_medicaments')->where('commande_id', $request->commande_id)->get();
        $toutesRecues = $lignes->every(fn($l) => $l->quantite == $l->quantiterecue);

        // 🏁 Mise à jour du statut de la commande
        DB::table('commandes')
            ->where('id', $request->commande_id)
            ->update(['statut' => $toutesRecues ? 'valide' : 'en_cours']);

        return response()->json([
            'message' => 'Réception enregistrée avec succès !',
            'reception_id' => $reception->id,
        ]);
    }


    //    public function store(Request $request)
    //    {
    //        $request->validate([
    //            'commande_id' => 'required|exists:commandes,id',
    //            'fournisseur_id' => 'required',
    //            'date_reception' => 'required|date',
    //            'reference_reception' => 'required|string',
    //            'receptions.*.medicament_id' => 'required|exists:medicaments,id',
    //            'receptions.*.quantite_recue' => 'required|numeric|min:0',
    //            'receptions.*.prix_unitaire' => 'required|numeric|min:0',
    //        ]);
    //
    //        // ✅ Création de la réception principale
    //        $reception = Reception::create([
    //            'commande_id' => $request->commande_id,
    //            'fournisseur_id' => $request->fournisseur_id,
    //            'date_reception' => $request->date_reception,
    //            'reference_reception' => $request->reference_reception,
    //            'user_id' => auth()->id(),
    //            'statut' => 'partielle',
    //            'observations' => $request->observations ?? null,
    //        ]);
    //
    //        // ✅ Parcours des lignes reçues
    //        if ($request->has('receptions')) {
    //            foreach ($request->receptions as $ligne) {
    //
    //                $commandeMedicament = DB::table('commande_medicaments')
    //                    ->where('commande_id', $request->commande_id)
    //                    ->where('medicament_id', $ligne['medicament_id'])
    //                    ->first();
    //
    //                if (!$commandeMedicament) {
    //                    return response()->json(['error' => 'Produit non trouvé dans la commande.'], 422);
    //                }
    //
    //                $quantiteCommandee = $commandeMedicament->quantite;
    //                $quantiteRecueActuelle = $commandeMedicament->quantiterecue ?? 0;
    //                $nouvelleQuantiteTotale = $quantiteRecueActuelle + $ligne['quantite_recue'];
    //
    //                // 🔒 Empêcher de dépasser la quantité commandée
    //                if ($nouvelleQuantiteTotale > $quantiteCommandee) {
    //                    return response()->json([
    //                        'error' => "Erreur : la quantité reçue ({$nouvelleQuantiteTotale}) dépasse la quantité commandée ({$quantiteCommandee}) pour le médicament ID {$ligne['medicament_id']}."
    //                    ], 422);
    //                }
    //
    //                // ✅ Enregistrement de la ligne de réception
    //                $reception->lignes()->create([
    //                    'medicament_id' => $ligne['medicament_id'],
    //                    'quantite_commandee' => $quantiteCommandee,
    //                    'quantite_recue' => $ligne['quantite_recue'],
    //                    'prix_unitaire' => $ligne['prix_unitaire'],
    //                    'lot' => $ligne['lot'] ?? null,
    //                    'date_peremption' => $ligne['date_peremption'] ?? null,
    //                ]);
    //
    //                // ✅ Mise à jour de la quantité reçue dans commande_medicaments
    //                DB::table('commande_medicaments')
    //                    ->where('commande_id', $request->commande_id)
    //                    ->where('medicament_id', $ligne['medicament_id'])
    //                    ->update(['quantiterecue' => $nouvelleQuantiteTotale]);
    //            }
    //        }
    //
    //        // ✅ Vérifier si la commande est totalement reçue
    //        $toutesRecues = DB::table('commande_medicaments')
    //            ->where('commande_id', $request->commande_id)
    //            ->whereColumn('quantiterecue', '<', 'quantite')
    //            ->doesntExist();
    //
    //        if ($toutesRecues) {
    //            Commande::where('id', $request->commande_id)->update(['statut' => 'valide']);
    //        }
    //
    //        return response()->json([
    //            'message' => '✅ Réception enregistrée avec succès !',
    //            'reception_id' => $reception->id,
    //        ]);
    //    }

    public function getProduits(Commande $commande)
    {
        $commande->load(['fournisseur', 'lignes.medicament']);

        // Produits non complètement reçus
        $produits = $commande->lignes
            ->filter(function ($ligne) {
                return $ligne->quantiterecue < $ligne->quantite;
            })
            ->map(function ($ligne) {
                return [
                    'commande_medicament_id' => $ligne->id, // 🟢 On ajoute l’ID de la ligne commande_medicament
                    'medicament_id' => $ligne->medicament_id,
                    'nom' => $ligne->medicament->nom ?? '',
                    'quantite_commandee' => $ligne->quantite,
                    'quantite_recue' => $ligne->quantiterecue ?? 0,
                    'quantite_restante' => ($ligne->quantite - ($ligne->quantiterecue ?? 0)),
                    'prix_unitaire' => $ligne->prix_unitaire,
                    'stock_ancien' => $ligne->medicament->stock ?? 0,
                ];
            })
            ->values();

        return response()->json([
            'fournisseur_id' => $commande->fournisseur_id,
            'fournisseur_nom' => $commande->fournisseur->nom ?? '',
            'produits' => $produits
        ]);
    }

    public function show($id)
    {
        $reception = Reception::with(['commande.fournisseur', 'fournisseur', 'lignes.medicament', 'user'])->findOrFail($id);
        return view('application.reception.show', compact('reception'));
    }

    public function edit($id)
    {
        $reception = Reception::with(['commande', 'fournisseur', 'lignes.medicament'])->findOrFail($id);
        
        if ($reception->commande && $reception->commande->statut === 'valide') {
            return redirect()->route('receptions.index')->with('error', 'Modification bloquée : La commande a déjà été totalement réceptionnée.');
        }
        
        $commande = Commande::with(['lignes.medicament'])->findOrFail($reception->commande_id);
        
        $produits = $commande->lignes->map(function ($ligne) use ($reception) {
            $ligneReception = $reception->lignes->where('medicament_id', $ligne->medicament_id)->first();
            
            $quantite_recue_courante = $ligneReception ? $ligneReception->quantite_recue : 0;
            $lot = $ligneReception ? $ligneReception->lot : '';
            $date_peremption = $ligneReception ? $ligneReception->date_peremption : '';
            
            $quantite_totale_recue = $ligne->quantiterecue ?? 0;
            // Quantité restante = Qte commandée - (Qte totale reçue - Qte reçue dans cette réception)
            $quantite_restante = $ligne->quantite - ($quantite_totale_recue - $quantite_recue_courante);
            
            return [
                'commande_medicament_id' => $ligne->id,
                'medicament_id' => $ligne->medicament_id,
                'nom' => $ligne->medicament->nom ?? '',
                'quantite_commandee' => $ligne->quantite,
                'quantite_recue' => $quantite_recue_courante,
                'quantite_restante' => max(0, $quantite_restante),
                'prix_unitaire' => $ligne->prix_unitaire,
                'stock_ancien' => $ligne->medicament->stock ?? 0,
                'lot' => $lot,
                'date_peremption' => $date_peremption,
            ];
        });

        return view('application.reception.edit', compact('reception', 'produits'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'date_reception' => 'required|date',
            'reference_reception' => 'required|string',
            'receptions.*.commande_medicament_id' => 'required|exists:commande_medicaments,id',
            'receptions.*.medicament_id' => 'required|exists:medicaments,id',
            'receptions.*.quantite_recue' => 'required|numeric|min:0',
        ]);

        $reception = Reception::with('lignes')->findOrFail($id);
        
        $reception->update([
            'date_reception' => $request->date_reception,
            'reference_reception' => $request->reference_reception,
        ]);

        if ($request->has('receptions')) {
            foreach ($request->receptions as $ligne) {
                $commandeMedicament = DB::table('commande_medicaments')
                    ->where('id', $ligne['commande_medicament_id'])
                    ->first();

                if (!$commandeMedicament) continue;

                $ligneReceptionExistante = $reception->lignes()->where('medicament_id', $ligne['medicament_id'])->first();

                $nouvelleQuantiteRecue = $ligne['quantite_recue'];

                if ($nouvelleQuantiteRecue > 0) {
                    if ($ligneReceptionExistante) {
                        $ligneReceptionExistante->update([
                            'quantite_recue' => $nouvelleQuantiteRecue,
                            'lot' => $ligne['lot'] ?? null,
                            'date_peremption' => $ligne['date_peremption'] ?? null,
                        ]);
                    } else {
                        $reception->lignes()->create([
                            'medicament_id' => $ligne['medicament_id'],
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
    }

    public function destroy($id)
    {
        $reception = Reception::with('lignes')->findOrFail($id);
        
        foreach ($reception->lignes as $ligne) {
            $commandeMedicament = DB::table('commande_medicaments')
                ->where('commande_id', $reception->commande_id)
                ->where('medicament_id', $ligne->medicament_id)
                ->first();

            if ($commandeMedicament) {
                $nouvelleQuantite = max(0, $commandeMedicament->quantiterecue - $ligne->quantite_recue);
                DB::table('commande_medicaments')
                    ->where('id', $commandeMedicament->id)
                    ->update(['quantiterecue' => $nouvelleQuantite]);
            }
        }
        
        DB::table('commandes')
            ->where('id', $reception->commande_id)
            ->update(['statut' => 'en_cours']);

        $reception->lignes()->delete();
        $reception->delete();

        return redirect()->route('receptions.index')->with('success', 'Réception supprimée avec succès.');
    }
}
