<?php

namespace App\Observers;

use App\Models\Commande;

class CommandeObserver
{
    /**
     * Après la création d'une commande
     */
    public function created(Commande $commande)
    {
        $this->updateStatutPaiement($commande);
    }

    /**
     * Après la mise à jour d'une commande
     */
    public function updated(Commande $commande)
    {
        // Si le total a changé, mettre à jour le statut de paiement
        if ($commande->isDirty('total')) {
            $this->updateStatutPaiement($commande);
        }
    }

    /**
     * Met à jour le statut de paiement
     */
    private function updateStatutPaiement(Commande $commande)
    {
        $montantPaye = $commande->montantPaye();
        $total = $commande->total;

        if ($montantPaye >= $total) {
            $statut = 'total';
        } elseif ($montantPaye > 0) {
            $statut = 'partielle';
        } else {
            $statut = 'en_cours';
        }

        // Éviter une boucle infinie de mise à jour
        if ($commande->StatutPaiement !== $statut) {
            $commande->update(['StatutPaiement' => $statut]);
        }
    }
}
