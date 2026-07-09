<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unite extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'symbole',
        'facteur',
        'prix_achat',
        'prix_vente',
        'medicament_id',
        'is_default',
    ];

    protected $casts = [
        'facteur'    => 'float',
        'prix_achat' => 'float',
        'prix_vente' => 'float',
        'is_default' => 'boolean',
    ];

    // Relations
    public function medicament()
    {
        return $this->belongsTo(Medicament::class);
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
}
