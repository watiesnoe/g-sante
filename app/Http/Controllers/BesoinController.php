<?php

namespace App\Http\Controllers;

use App\Models\DemandeAchat;
use App\Models\Medicament;
use App\Services\BesoinsService;
use App\Services\NumerotationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/** État des besoins (CMM) et préparation d'une proposition de commande. */
class BesoinController extends Controller
{
    public function index(Request $request, BesoinsService $besoins)
    {
        abort_unless(Auth::user()->can('stock.besoins'), 403, 'Accès non autorisé.');

        // Sélection venue de la liste des médicaments (bouton « Commander »)
        $ids = $request->filled('ids')
            ? Medicament::whereIn('uuid', (array) $request->ids)->pluck('id')->all()
            : null;
        $filtre = $request->filtre ?: ($ids ? 'tous' : 'a_commander');

        $lignes = $besoins->etat($ids ?: null);
        if ($ids) {
            // Un produit choisi sans consommation ni seuil est tout de même proposé, à quantité libre
            $presents = $lignes->pluck('medicament.id')->all();
            foreach (Medicament::with('uniteDefault')->whereIn('id', array_diff($ids, $presents))->get() as $m) {
                $lignes->push(['medicament' => $m, 'base' => 'aucune', 'consommation' => 0, 'jours_rupture' => 0, 'cmm' => 0, 'disponible' => (int) $m->stock,
                    'en_commande' => 0, 'mois_stock' => null, 'point_commande' => 0, 'stock_max' => 0, 'propose_base' => 0, 'unite' => $m->uniteDefault,
                    'propose_unite' => 0, 'statut' => 'ok']);
            }
        }

        $compteurs = $lignes->countBy('statut');
        $lignes = match ($filtre) {
            'a_commander' => $lignes->whereIn('statut', ['rupture', 'a_commander']),
            'surstock'    => $lignes->where('statut', 'surstock'),
            default       => $lignes,
        };

        return view('application.stock.besoins', [
            'lignes'     => $lignes->values(),
            'filtre'     => $filtre,
            'compteurs'  => $compteurs,
            'parametres' => $besoins->parametres(),
            'ids'        => $request->ids,
        ]);
    }

    /** Crée une proposition de commande avec les lignes cochées (quantités en unité d'achat). */
    public function proposer(Request $request, NumerotationService $numerotation)
    {
        abort_unless(Auth::user()->can('stock.besoins') && Auth::user()->can('achats.demander'), 403, 'Accès non autorisé.');

        $validated = $request->validate([
            'motif'      => 'nullable|string|max:255',
            'choix'      => 'required|array|min:1',
            'choix.*'    => 'integer|exists:medicaments,id',
            'quantites'  => 'required|array',
            'quantites.*' => 'nullable|integer|min:0',
        ], ['choix.required' => 'Cochez au moins un produit.']);

        $lignes = collect($validated['choix'])->unique()
            ->map(fn($id) => ['medicament_id' => (int) $id, 'quantite' => (int) ($validated['quantites'][$id] ?? 0)])
            ->filter(fn($l) => $l['quantite'] > 0);
        if ($lignes->isEmpty()) {
            return back()->with('error', 'Les produits cochés ont tous une quantité nulle.');
        }

        $demande = DB::transaction(function () use ($validated, $lignes, $numerotation) {
            $demande = DemandeAchat::create([
                'numero'       => $numerotation->suivant('demande_achat'),
                'motif'        => ($validated['motif'] ?? null) ?: 'Réapprovisionnement selon l\'état des besoins',
                'statut'       => 'soumise',
                'demandeur_id' => Auth::id(),
            ]);
            foreach ($lignes as $ligne) {
                $demande->lignes()->create($ligne);
            }

            return $demande;
        });

        return redirect()->route('achats.index')->with('success', 'Proposition ' . $demande->numero . ' créée (' . $lignes->count() . ' produit(s)) : elle attend l\'approbation d\'un responsable.');
    }
}
