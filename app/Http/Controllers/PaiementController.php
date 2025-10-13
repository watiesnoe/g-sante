<?php

namespace App\Http\Controllers;

use App\Models\Hospitalisation;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PaiementController extends Controller
{
    /**
     * Afficher la liste des paiements avec patients.
     */
    public function index()
    {
        abort_unless(Auth::user()->can('paiements.view'), 403, 'Accès non autorisé.');

        $year = session('exercice_year', date('Y'));
        $paiements = Paiement::with(['hospitalisation.patient'])
            ->whereYear('created_at', $year)
            ->latest()
            ->paginate(10);

        return view('paiements.index', compact('paiements'));
    }

    /**
     * Formulaire de création de paiement.
     */
    public function create()
    {
        abort_unless(Auth::user()->can('paiements.create'), 403, 'Accès non autorisé.');

        // On peut récupérer les hospitalisations en cours
        $hospitalisations = Hospitalisation::where('etat', 'en_cours')->with('patient')->get();
        return view('paiements.create', compact('hospitalisations'));
    }

    /**
     * Enregistrer un paiement et mettre à jour l'hospitalisation.
     */
   public function store(Request $request)
{
    abort_unless(Auth::user()->can('paiements.create'), 403, 'Accès non autorisé.');

    $request->validate([
        'hospitalisation_id' => 'required|exists:hospitalisations,uuid',
        'date_sortie'        => 'required|date|after_or_equal:today',
        'montant_total'      => 'required|numeric|min:0',
        'montant_recu'       => 'required|numeric|min:0',
        'mode_paiement'      => 'nullable|string',
        'statut_sortie'      => 'required|string',
    ]);

    try {
        // 🔹 Vérifier si la caisse est ouverte
        if (!\App\Models\CaisseSession::hasOpenSession()) {
            return response()->json([
                'success' => false,
                'message' => 'Votre caisse est fermée. Veuillez l\'ouvrir pour encaisser le paiement.'
            ], 403);
        }

        DB::transaction(function () use ($request) {

            $hospitalisation = Hospitalisation::where('uuid', $request->hospitalisation_id)->with('salle')->firstOrFail();

            // 🔒 Vérifier si déjà terminé
            if ($hospitalisation->etat === 'terminé') {
                throw new \Exception("Cette hospitalisation est déjà clôturée.");
            }

            // 🔒 Montant minimum recalculé côté serveur : relevé complet du séjour
            // (hébergement par chambre, actes du bloc, consommables — REC-16)
            $hospitalisation->date_sortie = $request->date_sortie;
            $montantAttendu = app(\App\Services\HospitalisationService::class)->releve($hospitalisation)['total'];

            if ($request->montant_total < $montantAttendu - 0.01) {
                throw new \Exception("Le montant total ({$request->montant_total}) est inférieur au montant minimum attendu ({$montantAttendu}) d'après le relevé du séjour.");
            }

            // ✅ Sortie : séjour clos, lit libéré (RG-12), décès reporté au dossier
            app(\App\Services\HospitalisationService::class)->sortir($hospitalisation, $request->statut_sortie, $request->date_sortie);

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

            // Enregistrement dans la caisse
            if ($request->montant_recu > 0) {
                \App\Models\CaisseSession::enregistrerMouvement(
                    $request->montant_recu,
                    'Paiement Hospitalisation #' . $hospitalisation->id,
                    'entree',
                    $hospitalisation,
                    $request->mode_paiement ?: 'especes'
                );
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Paiement enregistré et hospitalisation clôturée ✅'
        ]);

    } catch (\Exception $e) {

        \Illuminate\Support\Facades\Log::error('Erreur paiement hospitalisation : ' . $e->getMessage(), ['exception' => $e]);

        $messagesMetierAffichables = [
            "Cette hospitalisation est déjà clôturée.",
        ];
        $estMessageMetier = in_array($e->getMessage(), $messagesMetierAffichables)
            || str_starts_with($e->getMessage(), 'Le montant total (');

        return response()->json([
            'success' => false,
            'error' => $estMessageMetier
                ? $e->getMessage()
                : 'Une erreur est survenue lors de l\'enregistrement du paiement.'
        ], $estMessageMetier ? 422 : 500);
    }
}

    /**
     * Afficher un paiement.
     */
    public function show(Paiement $paiement)
    {
        abort_unless(Auth::user()->can('paiements.view'), 403, 'Accès non autorisé.');

        return view('paiements.show', compact('paiement'));
    }

    /**
     * Formulaire édition.
     */
    public function edit(Paiement $paiement)
    {
        abort_unless(Auth::user()->can('paiements.edit'), 403, 'Accès non autorisé.');

        return view('paiements.edit', compact('paiement'));
    }

    /**
     * Mettre à jour un paiement.
     */
    public function update(Request $request, Paiement $paiement)
    {
        abort_unless(Auth::user()->can('paiements.edit'), 403, 'Accès non autorisé.');

        $data = $request->validate([
            'montant_total' => 'required|numeric|min:0',
            'montant_recu'  => 'required|numeric|min:0',
            'mode_paiement' => 'nullable|string',
        ]);

        $data['montant_restant'] = $data['montant_total'] - $data['montant_recu'];
        $data['statut'] = $data['montant_recu'] >= $data['montant_total'] ? 'payé' : 'partiel';

        $paiement->update($data);

        return redirect()->route('hospitalisations.index')
            ->with('success', 'Paiement mis à jour.');
    }

    /**
     * Supprimer un paiement.
     */
    public function destroy(Paiement $paiement)
    {
        abort_unless(Auth::user()->can('paiements.delete'), 403, 'Accès non autorisé.');

        $paiement->delete();

        return redirect()->route('hospitalisations.index')
            ->with('success', 'Paiement supprimé.');
    }

    /**
     * Générer une facture imprimable pour un paiement.
     */
    public function print(Paiement $paiement)
    {
        abort_unless(Auth::user()->can('paiements.view'), 403, 'Accès non autorisé.');

        // Charger patient et hospitalisation
        $paiement->load('hospitalisation.patient');

        return view('paiements.print', compact('paiement'));
    }
}
