<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Commande extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'fournisseur_id',
        'date_commande',
        'statut',
        'total'
    ];

    protected static function booted()
    {
        static::creating(function ($commande) {
            $commande->reference = 'CMD-' . strtoupper(Str::random(6));
        });
    }

    /**
     * 🔗 Une commande appartient à un fournisseur
     */
    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }

    /**
     * 🔗 Une commande contient plusieurs lignes (pivot)
     */
    public function lignes()
    {
        return $this->hasMany(CommandeMedicaments::class, 'commande_id');
    }

    /**
     * 🔗 Une commande contient plusieurs médicaments via la table pivot
     */
    public function medicaments()
    {
        return $this->belongsToMany(Medicament::class, 'commande_medicaments')
            ->withPivot('quantite', 'prix_unitaire', 'total')
            ->withTimestamps();
    }
}
