<?php

namespace App\Traits;

use App\Models\Etablissement;
use Illuminate\Database\Eloquent\Builder;

/**
 * EF-01.03, SEC-04 : toute donnée métier appartient à un établissement.
 * - à la création, elle est rattachée à l'établissement courant ;
 * - en lecture, seules les données de l'établissement courant sont visibles,
 *   y compris par l'adresse directe (la liaison de route renvoie 404).
 *
 * Pour une lecture volontairement transverse (consolidation, super-administration) :
 * Modele::sansCloisonnement()->...
 */
trait AppartientEtablissement
{
    protected static function bootAppartientEtablissement(): void
    {
        static::addGlobalScope('etablissement', function (Builder $query) {
            $id = Etablissement::courantId();
            if ($id) {
                $query->where($query->getModel()->getTable() . '.etablissement_id', $id);
            }
        });

        static::creating(function ($model) {
            if (empty($model->etablissement_id)) {
                $model->etablissement_id = Etablissement::courantId();
            }
        });
    }

    public static function sansCloisonnement(): Builder
    {
        return static::withoutGlobalScope('etablissement');
    }

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class);
    }
}
