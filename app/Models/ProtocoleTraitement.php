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
        'traitement_alternatif', 'posologie_alternative',
    ];

    public function maladie()
    {
        return $this->belongsTo(Maladie::class);
    }

    // Récupère les médicaments via la table pivot protocole_medicament
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