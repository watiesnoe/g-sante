<?php

namespace App\Services;

use App\Models\Depense;
use App\Models\Etablissement;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Circuit des dépenses (EF-12.01) : brouillon → soumise → validée → payée.
 * Avec la séparation des tâches (section 5.1), celui qui crée une dépense ne la
 * valide pas. Le paiement est une sortie de la caisse de celui qui paie.
 */
class DepenseService
{
    public function __construct(private WorkflowService $workflow, private CaisseService $caisse) {}

    public function soumettre(Depense $depense): Depense
    {
        return $this->changer($depense, 'soumise');
    }

    public function valider(Depense $depense): Depense
    {
        $this->exigerPermission('depenses.valider');
        if ($depense->created_by === Auth::id() && Etablissement::courant()->parametre('securite.separation_taches')) {
            throw new AuthorizationException('Séparation des tâches : un autre responsable doit valider cette dépense.');
        }

        // EF-12.03 : dépassement de la ligne budgétaire bloqué ou signalé selon le paramètre
        if ($depense->ligneBudgetaire) {
            $disponible = $depense->ligneBudgetaire->montant_prevu - $depense->ligneBudgetaire->engage($depense->id);
            if ($depense->montant > $disponible && Etablissement::courant()->parametre('finance.blocage_depassement')) {
                throw ValidationException::withMessages(['montant' => 'Dépassement de la ligne budgétaire ' . $depense->ligneBudgetaire->code . ' : disponible ' . number_format($disponible, 0, ',', ' ') . ' F.']);
            }
        }

        return $this->changer($depense, 'validee', ['validee_par' => Auth::id(), 'validee_at' => now()]);
    }

    public function rejeter(Depense $depense, string $motif): Depense
    {
        $this->exigerPermission('depenses.valider');
        if (trim($motif) === '') {
            throw ValidationException::withMessages(['motif' => 'Le motif du rejet est obligatoire.']);
        }

        return $this->changer($depense, 'rejetee', ['motif_rejet' => $motif, 'validee_par' => Auth::id(), 'validee_at' => now()]);
    }

    public function payer(Depense $depense, string $mode = 'especes'): Depense
    {
        $this->exigerPermission('depenses.payer');

        return DB::transaction(function () use ($depense, $mode) {
            $this->workflow->exiger('depense', $depense->statut, 'payee', Depense::STATUTS);
            $this->caisse->enregistrer((float) $depense->montant, 'Dépense ' . $depense->numero . ' : ' . $depense->objet, 'sortie', $depense, $mode);

            return $this->changer($depense, 'payee', ['payee_par' => Auth::id(), 'payee_at' => now(), 'mode_paiement' => $mode]);
        });
    }

    public function annuler(Depense $depense): Depense
    {
        return $this->changer($depense, 'annulee');
    }

    private function changer(Depense $depense, string $vers, array $champs = []): Depense
    {
        $this->workflow->exiger('depense', $depense->statut, $vers, Depense::STATUTS);
        $depense->update(array_merge($champs, ['statut' => $vers]));

        return $depense;
    }

    private function exigerPermission(string $permission): void
    {
        if (!Auth::user()->can($permission)) {
            throw new AuthorizationException('Vous n\'avez pas la permission nécessaire pour cette étape de la dépense.');
        }
    }
}
