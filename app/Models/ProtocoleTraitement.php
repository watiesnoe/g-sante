<?php
namespace App\Models;
use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Model;

class ProtocoleTraitement extends Model
{
    use HasUuid;
    protected $fillable = [
        'maladie_id', 'titre', 'signes', 'diagnostics',
        'germes_nourrisson', 'germes_adulte', 'remarques',
        'traitement_principal', 'posologie_principale',
        'traitement_alternatif', 'posologie_alternative', 'actif',
    ];

    protected $casts = ['actif' => 'boolean'];

    public function maladie()
    {
        return $this->belongsTo(Maladie::class);
    }

    /**
     * Médicaments du protocole (commun à la plateforme) : nom, fiche du guide,
     * type, posologie, durée. Le produit de stock se résout par établissement.
     */
    public function lignes()
    {
        return $this->hasMany(ProtocoleMedicament::class, 'protocole_id')->orderByRaw("FIELD(type, 'principal', 'adjuvant', 'assos', 'relais', 'alternatif', 'option')");
    }

    // Ancienne liaison directe au stock (lignes antérieures au guide des médicaments)
    public function medicaments()
{
    return $this->belongsToMany(
        Medicament::class, 
        'protocole_medicament', // Nom de la table pivot
        'protocole_id',         // Ta colonne dans la table pivot (Correction ici)
        'medicament_id'         // L'autre colonne
    )
    ->using(ProtocoleMedicament::class)
    ->withPivot(['type', 'posologie', 'duree'])
    ->withTimestamps();
}
}