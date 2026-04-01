<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reception extends Model
{
    use HasFactory;

    protected $fillable = [
        'commande_id',
        'fournisseur_id',
        'date_reception',
        'reference_reception',
        'observations',
        'user_id'
    ];

    protected $casts = [
        'date_reception' => 'date',
    ];

    // Relations
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accesseurs
    public function getTotalQuantiteAttribute()
    {
        return $this->lignes->sum('quantite_recue');
    }

    public function getStatutAttribute()
    {
        if ($this->lignes->count() > 0) {
            return 'Complète';
        }
        return 'En attente';
    }
}
