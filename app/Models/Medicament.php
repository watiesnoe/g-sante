<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicament extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'description',
        'stock',
        'stock_min',
        'prix_achat',
        'prix_vente',
        'unite_id',
        'famille_id',
    ];

    /**
     * 🔗 Un médicament appartient à une unité
     */
    public function unite()
    {
        return $this->belongsTo(Unite::class);
    }

    /**
     * 🔗 Un médicament appartient à une famille
     */
    public function famille()
    {
        return $this->belongsTo(Famille::class);
    }

    /**
     * 🔗 Un médicament peut apparaître dans plusieurs commandes
     */
    public function commandes()
    {
        return $this->belongsToMany(Commande::class, 'commande_medicaments')
            ->withPivot('quantite', 'prix_unitaire', 'total')
            ->withTimestamps();
    }

    /**
     * 🔗 Lignes de commande liées à ce médicament
     */
    public function lignesCommandes()
    {
        return $this->hasMany(CommandeMedicaments::class, 'medicament_id');
    }
}
