<?php

namespace App\Services;

use App\Models\CaisseMouvement;
use App\Models\CaisseSession;
use App\Models\Etablissement;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Seul point d'écriture de la caisse (QUA-01) : encaissements, annulations,
 * clôture et validation (RG-01 à RG-03, EF-06.01 à EF-06.05).
 */
class CaisseService
{
    /** Session ouverte de l'utilisateur connecté, verrouillée en écriture. */
    public function sessionOuverte(): CaisseSession
    {
        $session = CaisseSession::where('user_id', Auth::id())->where('statut', 'ouverte')->lockForUpdate()->first();

        if (! $session) {
            throw ValidationException::withMessages(['caisse' => 'Aucune session de caisse ouverte. Veuillez ouvrir votre caisse pour effectuer cette opération.']);
        }

        return $session;
    }

    public function enregistrer(float $montant, string $motif, string $type = 'entree', ?Model $reference = null, string $mode = 'especes'): CaisseMouvement
    {
        return DB::transaction(function () use ($montant, $motif, $type, $reference, $mode) {
            $session = $this->sessionOuverte();

            return $session->mouvements()->create([
                'user_id' => Auth::id(),
                'type' => $type,
                'montant' => $montant,
                'mode' => array_key_exists($mode, CaisseMouvement::MODES) ? $mode : 'autre',
                'motif' => $motif,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->getKey(),
            ]);
        });
    }

    /**
     * Annule un mouvement (RG-01, RG-02, RG-03) : l'original est marqué annulé avec
     * motif et auteur, et l'écriture inverse est passée dans la session ouverte de
     * celui qui annule — même si l'original appartient à une session clôturée.
     */
    public function annuler(CaisseMouvement $mouvement, string $motif): CaisseMouvement
    {
        $this->exigerMotif($motif);
        $this->exigerDroitAnnulation($mouvement);

        return DB::transaction(function () use ($mouvement, $motif) {
            $mouvement = CaisseMouvement::whereKey($mouvement->id)->lockForUpdate()->firstOrFail();
            if ($mouvement->statut !== 'valide' || $mouvement->mouvement_annule_id) {
                throw ValidationException::withMessages(['caisse' => 'Ce mouvement est déjà annulé ou est lui-même une annulation.']);
            }

            $session = $this->sessionOuverte();

            $inverse = $session->mouvements()->create([
                'user_id' => Auth::id(),
                'type' => $mouvement->type === 'entree' ? 'sortie' : 'entree',
                'montant' => $mouvement->montant,
                'mode' => $mouvement->mode,
                'motif' => 'Annulation : '.$mouvement->motif.' — '.$motif,
                'reference_type' => $mouvement->reference_type,
                'reference_id' => $mouvement->reference_id,
                'mouvement_annule_id' => $mouvement->id,
            ]);

            $mouvement->update([
                'statut' => 'annule',
                'motif_annulation' => $motif,
                'annule_par' => Auth::id(),
                'annule_at' => now(),
            ]);

            return $inverse;
        });
    }

    /**
     * Annule tous les mouvements valides rattachés à une opération (ticket, paiement…).
     *
     * @return Collection<CaisseMouvement>
     */
    public function annulerReference(Model $reference, string $motif): Collection
    {
        return CaisseMouvement::where('reference_type', get_class($reference))
            ->where('reference_id', $reference->getKey())
            ->where('statut', 'valide')
            ->whereNull('mouvement_annule_id')
            ->get()
            ->map(fn (CaisseMouvement $m) => $this->annuler($m, $motif));
    }

    /**
     * Clôture la session ouverte de l'utilisateur (EF-06.04) : solde théorique
     * recalculé, saisie du compté, justification obligatoire si l'écart n'est pas nul.
     */
    public function cloturer(float $soldeReel, ?string $justification): CaisseSession
    {
        return DB::transaction(function () use ($soldeReel, $justification) {
            $session = $this->sessionOuverte();
            $theorique = $this->soldeTheorique($session);
            $ecart = round($soldeReel - $theorique, 2);

            if ($ecart != 0 && trim((string) $justification) === '') {
                throw ValidationException::withMessages(['justification_ecart' => 'Un écart de '.number_format($ecart, 0, ',', ' ').' FCFA doit être justifié.']);
            }

            $etablissement = Etablissement::courant();
            $avecValidation = $etablissement->parametre('caisse.validation_cloture');

            $session->forceFill([
                'solde_theorique' => $theorique,
                'solde_reel' => $soldeReel,
                'ecart' => $ecart,
                'justification_ecart' => $justification,
                'closed_at' => now(),
            ]);
            $session->statut = 'fermee';
            $session->save();

            // Sans circuit de validation, la clôture vaut validation
            if (! $avecValidation) {
                $session->update(['statut' => 'validee', 'validee_par' => Auth::id(), 'validee_at' => now()]);
            }

            return $session;
        });
    }

    /** Validation d'une clôture par un responsable (EF-06.05, REC-07). */
    public function valider(CaisseSession $session): CaisseSession
    {
        $user = Auth::user();
        if (! $user->can('caisse.valider_cloture')) {
            throw new AuthorizationException('Vous n\'avez pas la permission de valider une clôture de caisse.');
        }
        if ($session->user_id === $user->id && Etablissement::courant()->parametre('securite.separation_taches')) {
            throw new AuthorizationException('Séparation des tâches : vous ne pouvez pas valider la clôture de votre propre caisse.');
        }
        if ($session->statut !== 'fermee') {
            throw ValidationException::withMessages(['caisse' => 'Seule une caisse clôturée et non encore validée peut être validée.']);
        }

        $session->update(['statut' => 'validee', 'validee_par' => $user->id, 'validee_at' => now()]);

        return $session;
    }

    public function soldeTheorique(CaisseSession $session): float
    {
        // Tous les mouvements comptent, annulés compris : l'argent d'un encaissement
        // annulé est bien entré dans cette caisse ; c'est l'écriture inverse, passée
        // dans la session de celui qui annule, qui le fait ressortir.
        $entrees = (float) $session->mouvements()->where('type', 'entree')->sum('montant');
        $sorties = (float) $session->mouvements()->where('type', 'sortie')->sum('montant');

        return round($session->solde_initial + $entrees - $sorties, 2);
    }

    private function exigerMotif(string $motif): void
    {
        if (trim($motif) === '') {
            throw ValidationException::withMessages(['motif' => 'Le motif de l\'annulation est obligatoire.']);
        }
    }

    /**
     * RG-03 : l'annulation est réservée aux responsables (permission caisse.annuler) ;
     * avec la séparation des tâches, on n'annule pas sa propre opération.
     */
    private function exigerDroitAnnulation(CaisseMouvement $mouvement): void
    {
        $user = Auth::user();
        if (! $user->can('caisse.annuler')) {
            throw new AuthorizationException('Seul un responsable peut annuler un encaissement.');
        }
        if ($mouvement->user_id === $user->id && Etablissement::courant()->parametre('securite.separation_taches')) {
            throw new AuthorizationException('Séparation des tâches : un autre responsable doit annuler cette opération.');
        }
    }
}
