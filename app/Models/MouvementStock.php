<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;
use App\Traits\HasUuid;
use App\Traits\Inalterable;
use Illuminate\Database\Eloquent\Model;

/**
 * Mouvement de stock : écriture définitive (RG-01, RG-08). On ne le modifie
 * ni ne le supprime ; une erreur se corrige par un mouvement inverse.
 */
class MouvementStock extends Model
{
    use AppartientEtablissement;
    use Audite;
    use HasUuid, Inalterable;

    protected $table = 'mouvements_stock';

    protected $fillable = [
        'etablissement_id', 'medicament_id', 'lot_id', 'type', 'quantite', 'stock_apres',
        'source_type', 'source_id', 'motif', 'mouvement_annule_id', 'user_id',
    ];

    protected $casts = [
        'quantite' => 'integer',
        'stock_apres' => 'integer',
    ];

    public const TYPES = [
        'entree' => 'Entrée',
        'sortie' => 'Sortie',
        'retour' => 'Retour',
        'perte' => 'Perte',
        'ajustement' => 'Ajustement',
        'transfert' => 'Transfert',
        'dotation' => 'Dotation service',
    ];

    public function medicament()
    {
        return $this->belongsTo(Medicament::class);
    }

    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }

    public function source()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function annulation()
    {
        return $this->hasOne(self::class, 'mouvement_annule_id');
    }
}
