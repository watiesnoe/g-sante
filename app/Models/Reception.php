<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reception extends Model
{
    protected $fillable = [
        'commande_id',
        'fournisseur_id',
        'date_reception',
        'reference_reception',
        'statut',
        'observations',
        'user_id'
    ];

    public function lignes()
    {
        return $this->hasMany(ReceptionLigne::class);
    }

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }
}
