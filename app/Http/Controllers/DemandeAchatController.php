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
 * EF-12.04 : propositions de commande de la pharmacie → approbation → une commande
 * par fournisseur, puis réception rapprochée du stock (module Réceptions existant).
 * Les services, eux, passent par une réquisition interne (RequisitionController).
 */
class DemandeAchatController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->can('achats.demander') || Auth::user()->can('achats.approuver'), 403, 'Accès non autorisé.');

        return view('application.achat.index', [
            'demandes'     => DemandeAchat::with(['lignes.medicament.uniteDefault', 'lignes.fournisseur', 'demandeur', 'service', 'commande', 'commandes'])->latest()->get(),
            'medicaments'  => Medicament::with('uniteDefault')->orderBy('nom')->get(['id', 'nom', 'stock', 'stock_min']),
            'derniersFournisseurs' => $this->derniersFournisseurs(),
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
            'lignes.*.medicament_id'  => 'required|distinct|exists:medicaments,id',
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

        return back()->with('success', 'Proposition de commande enregistrée.');
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

        return back()->with('success', 'Proposition ' . $demande->numero . ' : ' . DemandeAchat::STATUTS[$demande->fresh()->statut] . '.');
    }

    /**
     * Transforme la proposition approuvée en commandes : une par fournisseur choisi
     * ligne à ligne, au prix d'achat de l'unité par défaut.
     */
    private function commander(Request $request, DemandeAchat $demande): void
    {
        $lignes = $demande->lignes()->with('medicament.uniteDefault')->get();
        $choix = (array) $request->input('fournisseurs', []);
        // Ancien formulaire : un seul fournisseur pour toute la demande
        foreach ($lignes as $ligne) {
            $choix[$ligne->id] = $choix[$ligne->id] ?? $request->input('fournisseur_id');
        }
        $request->merge(['fournisseurs' => $choix]);
        $request->validate(
            ['fournisseurs' => 'required|array', 'fournisseurs.*' => 'required|exists:fournisseurs,id'],
            ['fournisseurs.*.required' => 'Choisissez un fournisseur pour chaque produit.']
        );

        DB::transaction(function () use ($demande, $lignes, $choix) {
            $parFournisseur = $lignes->groupBy(fn($l) => (int) $choix[$l->id]);
            $premiere = null;
            foreach ($parFournisseur as $fournisseurId => $groupe) {
                // La référence est attribuée par le modèle Commande
                $commande = Commande::create([
                    'date_commande'    => today(),
                    'fournisseur_id'   => $fournisseurId,
                    'statut'           => 'en_cours',
                    'total'            => 0,
                    'demande_achat_id' => $demande->id,
                ]);
                $total = 0;
                foreach ($groupe as $ligne) {
                    $unite = $ligne->medicament->uniteDefault;
                    $prix = (float) ($unite->prix_achat ?? 0);
                    $commande->lignes()->create([
                        'medicament_id' => $ligne->medicament_id, 'unite_id' => $unite?->id,
                        'quantite' => $ligne->quantite, 'prix_unitaire' => $prix, 'total' => $prix * $ligne->quantite,
                    ]);
                    $ligne->update(['fournisseur_id' => $fournisseurId]);
                    $total += $prix * $ligne->quantite;
                }
                $commande->update(['total' => $total]);
                $premiere ??= $commande;
            }
            $demande->update(['statut' => 'commandee', 'commande_id' => $premiere->id]);
        });
    }

    /** Fournisseur de la dernière commande de chaque produit, proposé par défaut. */
    private function derniersFournisseurs(): array
    {
        return Etablissement::table('commandes')
            ->join('commande_medicaments as cm', 'cm.commande_id', '=', 'commandes.id')
            ->orderBy('commandes.id')
            ->get(['cm.medicament_id', 'commandes.fournisseur_id'])
            ->pluck('fournisseur_id', 'medicament_id')
            ->all();
    }
}
