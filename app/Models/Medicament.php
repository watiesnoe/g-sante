<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicament extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'uuid',
        'nom',
        'code_barre',
        'description',
        'stock',
        'stock_min',
        'famille_id',
    ];

    // Relations
    public function famille()
    {
        return $this->belongsTo(Famille::class);
    }

    /**
     * Les unités (conditionnements) disponibles pour ce médicament.
     * Un médicament peut avoir plusieurs unités avec des prix différents.
     */
    public function unites()
    {
        return $this->hasMany(Unite::class);
    }

    /**
     * L'unité par défaut (facteur = 1, ou marquée comme default).
     */
    public function uniteDefault()
    {
        return $this->hasOne(Unite::class)->where('is_default', true);
    }

    /**
     * Alias for uniteDefault for ease of access (e.g. eager loading 'unite')
     */
    public function unite()
    {
        return $this->uniteDefault();
    }

    public function commandeMedicaments()
    {
        return $this->hasMany(CommandeMedicaments::class);
    }

    public function ordonnanceMedicaments()
    {
        return $this->hasMany(OrdonnanceMedicament::class);
    }

    public function receptionLignes()
    {
        return $this->hasMany(ReceptionLigne::class);
    }

    public function protocoles()
    {
        return $this->belongsToMany(
            ProtocoleTraitement::class,
            'protocole_medicament',
            'medicament_id',
            'protocole_id'
        )
        ->using(ProtocoleMedicament::class)
        ->withPivot(['type', 'posologie', 'duree'])
        ->withTimestamps();
    }

    /**
     * Scope a query to only include medications with critical stock levels.
     */
    public function scopeCritique($query)
    {
        return $query->whereColumn('stock', '<=', 'stock_min');
    }
}
