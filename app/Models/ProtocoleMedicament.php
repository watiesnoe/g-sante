<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProtocoleMedicament extends Pivot
{
    /**
     * Indique si la table pivot possède un ID auto-incrémenté.
     * Comme nous avons mis $table->id() dans la migration, on met true.
     */
    public $incrementing = true;

    /**
     * Le nom de la table pivot.
     */
    protected $table = 'protocole_medicament';

    /**
     * Les champs que l'on peut remplir.
     */
    protected $fillable = [
        'protocole_id',
        'medicament_id',
        'type',      // principal ou alternatif
        'posologie', // ex: 1g 3x/jour
        'duree'      // ex: 10 jours
    ];

    /**
     * Relation inverse vers le protocole (Optionnel mais utile)
     */
    public function protocole()
    {
        return $this->belongsTo(ProtocoleTraitement::class, 'protocole_id');
    }

    /**
     * Relation inverse vers le médicament (Optionnel mais utile)
     */
    public function medicament()
    {
        return $this->belongsTo(Medicament::class, 'medicament_id');
    }
}