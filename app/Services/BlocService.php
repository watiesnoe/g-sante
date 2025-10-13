<?php

namespace App\Services;

use App\Models\Intervention;
use App\Models\Medicament;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Bloc opératoire (EF-10.05) : programmation sans conflit de salle ni de
 * chirurgien, déroulé, compte rendu et consommables sortis du stock.
 */
class BlocService
{
    public function __construct(private StockService $stock) {}

    public function programmer(array $donnees, array $equipe = []): Intervention
    {
        $debut = Carbon::parse($donnees['debut_prevu']);
        $fin = $debut->copy()->addMinutes((int) ($donnees['duree_prevue_min'] ?? 60));

        return DB::transaction(function () use ($donnees, $equipe, $debut, $fin) {
            $chevauche = fn($q) => $q->whereIn('statut', ['programmee', 'en_cours'])
                ->where('debut_prevu', '<', $fin)
                ->whereRaw('DATE_ADD(debut_prevu, INTERVAL duree_prevue_min MINUTE) > ?', [$debut]);

            if (Intervention::where('salle_id', $donnees['salle_id'])->where($chevauche)->lockForUpdate()->exists()) {
                throw ValidationException::withMessages(['debut_prevu' => 'La salle d\'opération est déjà occupée sur ce créneau.']);
            }
            if (Intervention::where('chirurgien_id', $donnees['chirurgien_id'])->where($chevauche)->exists()) {
                throw ValidationException::withMessages(['chirurgien_id' => 'Le chirurgien opère déjà sur ce créneau.']);
            }

            $intervention = Intervention::create($donnees + ['statut' => 'programmee', 'created_by' => Auth::id()]);
            foreach ($equipe as $membre) {
                $intervention->equipe()->attach($membre['user_id'], ['role' => $membre['role'] ?? 'aide_operatoire']);
            }

            return $intervention;
        });
    }

    public function demarrer(Intervention $intervention): Intervention
    {
        $this->exigerStatut($intervention, 'programmee');
        $intervention->update(['statut' => 'en_cours', 'debut_reel' => now()]);

        return $intervention;
    }

    public function terminer(Intervention $intervention, string $compteRendu): Intervention
    {
        $this->exigerStatut($intervention, 'en_cours');
        if (trim($compteRendu) === '') {
            throw ValidationException::withMessages(['compte_rendu' => 'Le compte rendu opératoire est obligatoire.']);
        }
        $intervention->update(['statut' => 'terminee', 'fin_reelle' => now(), 'compte_rendu' => $compteRendu]);

        return $intervention;
    }

    public function annuler(Intervention $intervention, string $motif): Intervention
    {
        $this->exigerStatut($intervention, 'programmee');
        if (trim($motif) === '') {
            throw ValidationException::withMessages(['motif' => 'Le motif de l\'annulation est obligatoire.']);
        }
        $intervention->update(['statut' => 'annulee', 'motif_annulation' => $motif]);

        return $intervention;
    }

    /** Consommable utilisé pendant l'intervention : sorti du stock (FEFO) et ajouté au séjour. */
    public function consommer(Intervention $intervention, Medicament $medicament, int $quantite): void
    {
        if (!in_array($intervention->statut, ['en_cours', 'terminee'], true)) {
            throw ValidationException::withMessages(['medicament_id' => 'Les consommables s\'enregistrent pendant ou après l\'intervention.']);
        }

        DB::transaction(function () use ($intervention, $medicament, $quantite) {
            $ligne = $intervention->consommables()->create([
                'medicament_id' => $medicament->id, 'quantite' => $quantite,
                'prix_unitaire' => $medicament->prix_vente, 'user_id' => Auth::id(),
            ]);
            $this->stock->sortie($medicament, $quantite, $ligne, 'Bloc : ' . $intervention->libelle);
        });
    }

    private function exigerStatut(Intervention $intervention, string $attendu): void
    {
        if ($intervention->statut !== $attendu) {
            throw ValidationException::withMessages(['statut' => 'Intervention ' . strtolower(Intervention::STATUTS[$intervention->statut]) . ' : action impossible.']);
        }
    }
}
