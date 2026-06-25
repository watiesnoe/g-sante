<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Medicament extends Model
{
    use HasUuid;
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
     * 🎯 SCOPE : Filtrer les médicaments en stock critique
     */
    public function scopeCritique(Builder $query): Builder
    {
        return $query->whereNotNull('stock')
            ->whereNotNull('stock_min')
            ->whereColumn('stock', '<=', 'stock_min');
    }

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

    /**
     * 🔗 Protocoles de traitement liés à ce médicament
     */
    public function protocoles()
    {
        return $this->belongsToMany(
            ProtocoleTraitement::class,
            'protocole_medicament',
            'medicament_id',
            'protocole_id'
        )
            ->using(ProtocoleMedicament::class)
            ->withPivot(['type', 'posologie', 'duree']);
    }
}
