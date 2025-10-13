<?php

namespace App\Http\Controllers;

use App\Models\Hospitalisation;
use App\Models\Paiement;
use Illuminate\Http\Request;

class PaiementController extends Controller
{
    /**
     * Afficher la liste des paiements.
     */
    public function index()
    {
        $paiements = Paiement::with(['hospitalisation.patient', 'analyse'])
            ->latest()
            ->paginate(10);

        return view('paiements.index', compact('paiements'));
    }

    /**
     * Formulaire de création.
     */
    public function create()
    {
        return view('paiements.create');
    }

    /**
     * Enregistrer un paiement.
     */

    public function store(Request $request)
    {
        // 1️⃣ Validation
        $request->validate([
            'hospitalisation_id' => 'required|exists:hospitalisations,id',
            'dateSortie'         => 'required|date',
            'montantTotal'       => 'required|numeric|min:0',
            'montantRecu'        => 'required|numeric|min:0',
            'mode_paiement'      => 'nullable|string',
        ]);

        // 2️⃣ Récupérer l’hospitalisation
        $hospitalisation = Hospitalisation::findOrFail($request->hospitalisation_id);

        // 3️⃣ Mettre à jour la date de sortie et l’état
        $hospitalisation->date_sortie = $request->dateSortie;
        $hospitalisation->etat = 'terminé';
        $hospitalisation->save();

        // 4️⃣ Calculer montant restant et statut
        $montantTotal = $request->montantTotal;
        $montantRecu = $request->montantRecu;
        $montantRestant = $montantTotal - $montantRecu;

        $statut = 'en_attente';
        if ($montantRestant <= 0) {
            $statut = 'payé';
        } elseif ($montantRecu > 0) {
            $statut = 'partiel';
        }

        // 5️⃣ Créer le paiement
        $paiement = Paiement::create([
            'hospitalisation_id' => $hospitalisation->id,
            'montant_total'      => $montantTotal,
            'montant_recu'       => $montantRecu,
            'montant_restant'    => $montantRestant,
            'statut'             => $statut,
            'mode_paiement'      => $request->mode_paiement ?? 'non précisé',
            'date_paiement'      => now(),
        ]);

        // 6️⃣ Réponse JSON pour AJAX
        return response()->json([
            'success' => true,
            'message' => 'Paiement enregistré avec succès ✅',
            'paiement' => $paiement,
        ]);
    }



    /**
     * Afficher un paiement.
     */
    public function show(Paiement $paiement)
    {
        return view('paiements.show', compact('paiement'));
    }

    /**
     * Formulaire d’édition.
     */
    public function edit(Paiement $paiement)
    {
        return view('paiements.edit', compact('paiement'));
    }

    /**
     * Mettre à jour un paiement.
     */
    public function update(Request $request, Paiement $paiement)
    {
        $data = $request->validate([
            'montant_total' => 'required|numeric|min:0',
            'montant_recu'  => 'required|numeric|min:0',
            'mode_paiement' => 'nullable|string',
        ]);

        $data['montant_restant'] = $data['montant_total'] - $data['montant_recu'];
        $data['statut'] = $data['montant_recu'] >= $data['montant_total'] ? 'payé' : 'partiel';

        $paiement->update($data);

        return redirect()->route('paiements.index')->with('success', 'Paiement mis à jour.');
    }

    /**
     * Supprimer un paiement.
     */
    public function destroy(Paiement $paiement)
    {
        $paiement->delete();

        return redirect()->route('paiements.index')->with('success', 'Paiement supprimé.');
    }
}
