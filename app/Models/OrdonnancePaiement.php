<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdonnancePaiement extends Model
{
    use HasFactory;

    // Table associée
    protected $table = 'ordonnance_paiements';

    // Champs remplissables (mass assignable)
    protected $fillable = [
        'ordonnance_id',
        'medicament_id',
        'quantite',
        'prix_total',
        'statut',
    ];

    /**
     * Relation avec l'ordonnance
     */
    public function ordonnance()
    {
        return $this->belongsTo(Ordonnance::class);
    }

    /**
     * Relation avec le médicament
     */
    public function medicament()
    {
        return $this->belongsTo(Medicament::class);
    }
}
