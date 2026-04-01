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

            $data = Reception::with(['commande', 'fournisseur', 'lignes'])
                ->latest();

            return DataTables::of($data)

                ->addColumn('reference', function($row){
                    return '<span class="badge bg-info">'.($row->reference_reception ?? 'N/A').'</span>';
                })

                ->addColumn('commande', function($row){
                    if ($row->commande) {
                        return '<a href="'.route('commandes.show', $row->commande->id).'">'.
                            ($row->commande->reference ?? 'CMD-'.$row->commande->id).
                            '</a>';
                    }
                    return '<span class="text-muted">Non liée</span>';
                })

                ->addColumn('fournisseur', function($row){
                    return $row->fournisseur->nom ?? $row->commande->fournisseur->nom ?? 'N/A';
                })

                ->addColumn('date', function($row){
                    return \Carbon\Carbon::parse($row->date_reception)->format('d/m/Y');
                })

                ->addColumn('produits', function($row){
                    return '<span class="badge bg-primary">'.$row->lignes->count().' produit(s)</span>';
                })

                ->addColumn('observation', function($row){
                    return \Str::limit($row->observations, 30);
                })

                ->addColumn('actions', function($row){
                    return '
                <div class="btn-group">
                    <a href="'.route('receptions.show', $row->id).'" class="btn btn-sm btn-info">
                        <i class="fa fa-eye"></i>
                    </a>
                    <a href="'.route('receptions.edit', $row->id).'" class="btn btn-sm btn-warning">
                        <i class="fa fa-edit"></i>
                    </a>
                    <button class="btn btn-sm btn-danger" onclick="confirmDelete('.$row->id.', \''.$row->reference_reception.'\')">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>

                <form id="delete-form-'.$row->id.'" method="POST" action="'.route('receptions.destroy', $row->id).'" style="display:none;">
                    '.csrf_field().method_field('DELETE').'
                </form>
                ';
                })

                ->rawColumns(['reference','commande','produits','actions'])
                ->make(true);
        }

        return view('application.reception.index');
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
            'observations' => $request->observations ?? null,
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

    public function getProduits($id)
    {
        $commande = Commande::with(['fournisseur', 'lignes.medicament'])->findOrFail($id);

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
}
