<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdonnanceMedicament extends Model
{
    protected $fillable = [
        'ordonnance_id', 'medicament_id', 'posologie', 'duree_jours','quantite','qte_vendu','statut_vente'
    ];

    public function ordonnance()
    {
        return $this->belongsTo(Ordonnance::class);
    }

    public function medicament()
    {
        return $this->belongsTo(Medicament::class);
    }
}
