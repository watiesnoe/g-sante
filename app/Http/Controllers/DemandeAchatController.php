<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\DemandeAchat;
use App\Models\Etablissement;
use App\Models\Fournisseur;
use App\Models\Medicament;
use App\Models\ServiceMedical;
use App\Services\NumerotationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * EF-12.04 : demandes d'achat des services → approbation → commande fournisseur,
 * puis réception rapprochée du stock (module Réceptions existant).
 */
class DemandeAchatController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->can('achats.demander') || Auth::user()->can('achats.approuver'), 403, 'Accès non autorisé.');

        return view('application.achat.index', [
            'demandes'     => DemandeAchat::with(['lignes.medicament', 'demandeur', 'service', 'commande'])->latest()->get(),
            'medicaments'  => Medicament::orderBy('nom')->get(['id', 'nom', 'stock', 'stock_min']),
            'services'     => ServiceMedical::orderBy('nom')->get(['id', 'nom']),
            'fournisseurs' => Fournisseur::orderBy('nom')->get(['id', 'nom']),
        ]);
    }

    public function store(Request $request, NumerotationService $numerotation)
    {
        abort_unless(Auth::user()->can('achats.demander'), 403, 'Accès non autorisé.');

        $validated = $request->validate([
            'service_medical_id'      => 'nullable|exists:service_medicals,id',
            'motif'                   => 'nullable|string|max:255',
            'lignes'                  => 'required|array|min:1',
            'lignes.*.medicament_id'  => 'required|exists:medicaments,id',
            'lignes.*.quantite'       => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($validated, $numerotation, $request) {
            $demande = DemandeAchat::create([
                'numero'             => $numerotation->suivant('demande_achat'),
                'service_medical_id' => $validated['service_medical_id'] ?? null,
                'motif'              => $validated['motif'] ?? null,
                'statut'             => $request->boolean('soumettre') ? 'soumise' : 'brouillon',
                'demandeur_id'       => Auth::id(),
            ]);
            foreach ($validated['lignes'] as $ligne) {
                $demande->lignes()->create($ligne);
            }
        });

        return back()->with('success', 'Demande d\'achat enregistrée.');
    }

    public function action(Request $request, DemandeAchat $demande, string $action)
    {
        $user = Auth::user();
        $separation = Etablissement::courant()->parametre('securite.separation_taches');

        $erreur = match ($action) {
            'soumettre' => $demande->statut !== 'brouillon' ? 'Seul un brouillon peut être soumis.' : null,
            'approuver', 'rejeter' => !$user->can('achats.approuver') ? 'Vous n\'avez pas la permission d\'approuver.'
                : ($demande->statut !== 'soumise' ? 'Seule une demande soumise peut être approuvée ou rejetée.'
                : ($separation && $demande->demandeur_id === $user->id ? 'Séparation des tâches : un autre responsable doit approuver cette demande.' : null)),
            'commander' => !$user->can('stock.commandes') ? 'Vous n\'avez pas la permission de commander.'
                : ($demande->statut !== 'approuvee' ? 'Seule une demande approuvée peut être commandée.' : null),
            default => 'Action inconnue.',
        };
        if ($erreur) {
            return back()->with('error', $erreur);
        }

        match ($action) {
            'soumettre' => $demande->update(['statut' => 'soumise']),
            'approuver' => $demande->update(['statut' => 'approuvee', 'approuvee_par' => $user->id, 'approuvee_at' => now()]),
            'rejeter'   => $demande->update(['statut' => 'rejetee', 'motif_rejet' => $request->motif ?: 'Non précisé', 'approuvee_par' => $user->id, 'approuvee_at' => now()]),
            'commander' => $this->commander($request, $demande),
        };

        return back()->with('success', 'Demande ' . $demande->numero . ' : ' . DemandeAchat::STATUTS[$demande->fresh()->statut] . '.');
    }

    /** Transforme la demande approuvée en commande fournisseur, au prix d'achat de l'unité par défaut. */
    private function commander(Request $request, DemandeAchat $demande): void
    {
        $request->validate(['fournisseur_id' => 'required|exists:fournisseurs,id']);

        DB::transaction(function () use ($request, $demande) {
            $commande = Commande::create([
                'reference'      => 'CMD-' . $demande->numero,
                'date_commande'  => today(),
                'fournisseur_id' => $request->fournisseur_id,
                'statut'         => 'en_cours',
                'total'          => 0,
            ]);
            $total = 0;
            foreach ($demande->lignes()->with('medicament.uniteDefault')->get() as $ligne) {
                $unite = $ligne->medicament->uniteDefault;
                $prix = (float) ($unite->prix_achat ?? 0);
                $commande->lignes()->create([
                    'medicament_id' => $ligne->medicament_id, 'unite_id' => $unite?->id,
                    'quantite' => $ligne->quantite, 'prix_unitaire' => $prix, 'total' => $prix * $ligne->quantite,
                ]);
                $total += $prix * $ligne->quantite;
            }
            $commande->update(['total' => $total]);
            $demande->update(['statut' => 'commandee', 'commande_id' => $commande->id]);
        });
    }
}
