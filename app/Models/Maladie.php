<?php
namespace App\Models;
use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Model;

class Maladie extends Model
{
    use HasUuid;
    protected $fillable = [
        'nom', 'chapitre', 'description', 'signes_gravite', 'examens', 'traitement', 'prevention', 'alerte_oms',
        'source', 'source_url', 'actif',
    ];

    protected $casts = ['actif' => 'boolean'];

    /** Maladies du guide en vigueur (les anciennes sont archivées, actif = false). */
    public function scopeActives($query)
    {
        return $query->where('maladies.actif', true);
    }

    /** Ordre du guide : par numéro de chapitre, puis par nom. */
    public function scopeOrdreGuide($query)
    {
        return $query->orderByRaw('CAST(chapitre AS UNSIGNED)')->orderBy('nom');
    }

    /** Signes de gravité, un par élément (le guide les sépare par « ; »). */
    public function signesGraviteListe(): array
    {
        return array_values(array_filter(array_map('trim', explode(';', (string) $this->signes_gravite))));
    }

    /** Fiches du livre Berrebi qui traitent de cette maladie. */
    public function fichesBerrebi()
    {
        return $this->belongsToMany(FicheBerrebi::class, 'fiche_berrebi_maladie')->orderBy('titre');
    }

    /** Alertes OMS distinctes (le classeur peut répéter la même). */
    public function alertesOms(): array
    {
        return array_values(array_unique(array_filter(array_map('trim', preg_split('/\s*[|\n]\s*/u', (string) $this->alerte_oms)))));
    }

    // Récupère tous les symptômes liés à cette maladie
    public function symptomes()
    {
        return $this->belongsToMany(Symptome::class, 'maladie_symptome');
    }

    // Récupère le protocole de traitement unique de cette maladie
    public function protocole()
    {
        return $this->hasOne(ProtocoleTraitement::class);
    }

    // Récupère les consultations via maladie_id (1-N)
    public function consultationsDirectes()
    {
        return $this->hasMany(Consultation::class);
    }

    // Récupère les consultations via la table pivot consultation_maladie (N-N)
    public function consultations()
    {
        return $this->belongsToMany(Consultation::class, 'consultation_maladie');
    }
}