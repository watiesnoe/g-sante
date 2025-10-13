<?php

namespace App\Traits;

use LogicException;

/**
 * RG-01 : une écriture validée (paiement, mouvement de caisse ou de stock) n'est
 * jamais supprimée ni modifiée. La correction passe par une annulation qui crée
 * une écriture inverse et conserve l'originale.
 *
 * Le modèle peut déclarer $champsModifiables pour les seules colonnes qu'une
 * annulation a le droit de renseigner (statut, auteur, date, motif).
 */
trait Inalterable
{
    protected static function bootInalterable(): void
    {
        static::deleting(function ($model) {
            throw new LogicException(class_basename($model).' : suppression interdite. Utilisez une annulation (RG-01).');
        });

        static::updating(function ($model) {
            $autorises = array_merge($model->champsModifiables ?? [], ['updated_at']);
            $modifies = array_diff(array_keys($model->getDirty()), $autorises);

            if ($modifies) {
                throw new LogicException(class_basename($model).' : modification interdite de '.implode(', ', $modifies).'. Utilisez une annulation (RG-01).');
            }
        });
    }
}
