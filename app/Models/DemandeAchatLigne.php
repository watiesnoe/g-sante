<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandeAchatLigne extends Model
{
    public $timestamps = false;

    protected $fillable = ['demande_achat_id', 'medicament_id', 'quantite', 'observation', 'fournisseur_id'];

    public function medicament()
    {
        return $this->belongsTo(Medicament::class);
    }

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }
}
