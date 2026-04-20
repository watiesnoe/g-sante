<?php

namespace App\Http\Controllers;

use App\Models\Hospitalisation;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaiementController extends Controller
{
    /**
     * Afficher la liste des paiements avec patients.
     */
    public function index()
    {
        $paiements = Paiement::with(['hospitalisation.patient'])
            ->latest()
            ->paginate(10);

        return view('paiements.index', compact('paiements'));
    }

    /**
     * Formulaire de création de paiement.
     */
    public function create()
    {
        // On peut récupérer les hospitalisations en cours
        $hospitalisations = Hospitalisation::where('etat', 'en_cours')->with('patient')->get();
        return view('paiements.create', compact('hospitalisations'));
    }

    /**
     * Enregistrer un paiement et mettre à jour l'hospitalisation.
     */
   public function store(Request $request)
{
    $request->validate([
        'hospitalisation_id' => 'required|exists:hospitalisations,id',
        'date_sortie'        => 'required|date|after_or_equal:today',
        'montant_total'      => 'required|numeric|min:0',
        'montant_recu'       => 'required|numeric|min:0',
        'mode_paiement'      => 'nullable|string',
        'statut_sortie'      => 'required|string',
    ]);

    try {
        DB::transaction(function () use ($request) {

            $hospitalisation = Hospitalisation::findOrFail($request->hospitalisation_id);

            // 🔒 Vérifier si déjà terminé
            if ($hospitalisation->etat === 'terminé') {
                throw new \Exception("Cette hospitalisation est déjà clôturée.");
            }

            // ✅ Mode de sortie
            $statutSortie = $request->statut_sortie;

            // ✅ Mise à jour hospitalisation
            $hospitalisation->update([
                'date_sortie' => $request->date_sortie,
                'etat' => 'terminé',
                'statut_sortie' => $statutSortie
            ]);

            // 💀 Si Décès, on marque le patient comme décédé
            if ($statutSortie === 'Décès') {
                $patient = $hospitalisation->consultation->patient ?? $hospitalisation->patient;
                if ($patient) {
                    $patient->update([
                        'est_decede' => true,
                        'date_deces' => now()
                    ]);
                }
            }

            // ✅ Calcul paiement
            $montantRestant = $request->montant_total - $request->montant_recu;

            $statut = match (true) {
                $montantRestant <= 0 => 'payé',
                $request->montant_recu > 0 => 'partiel',
                default => 'en_attente',
            };

            // ✅ Création paiement
            Paiement::create([
                'hospitalisation_id' => $hospitalisation->id,
                'montant_total'      => $request->montant_total,
                'montant_recu'       => $request->montant_recu,
                'montant_restant'    => $montantRestant,
                'statut'             => $statut,
                'mode_paiement'      => $request->mode_paiement ?? 'non précisé',
                'date_paiement'      => now(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Paiement enregistré et hospitalisation clôturée ✅'
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Afficher un paiement.
     */
    public function show(Paiement $paiement)
    {
        return view('paiements.show', compact('paiement'));
    }

    /**
     * Formulaire édition.
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

        return redirect()->route('paiements.index')
            ->with('success', 'Paiement mis à jour.');
    }

    /**
     * Supprimer un paiement.
     */
    public function destroy(Paiement $paiement)
    {
        $paiement->delete();

        return redirect()->route('paiements.index')
            ->with('success', 'Paiement supprimé.');
    }

    /**
     * Générer une facture imprimable pour un paiement.
     */
    public function print(Paiement $paiement)
    {
        // Charger patient et hospitalisation
        $paiement->load('hospitalisation.patient');

        return view('paiements.print', compact('paiement'));
    }
}
