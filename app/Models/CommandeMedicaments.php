<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommandeMedicaments extends Model
{
    use HasFactory;

    protected $table = 'commande_medicaments';

    protected $fillable = [
        'commande_id',
        'medicament_id',
        'quantite',
        'prix_unitaire',
        'total',
        'quantiterecue'
    ];

    /**
     * 🔗 Chaque ligne appartient à une commande
     */
    public function commande()
    {
        return $this->belongsTo(Commande::class, 'commande_id');
    }

    /**
     * 🔗 Chaque ligne appartient à un médicament
     */
    public function medicament()
    {
        return $this->belongsTo(Medicament::class, 'medicament_id');
    }
}
