<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

/** Intervention au bloc opératoire (EF-10.05). */
class Intervention extends Model
{
    use AppartientEtablissement, Audite, HasUuid;

    protected $fillable = [
        'etablissement_id', 'patient_id', 'hospitalisation_id', 'salle_id', 'prestation_id', 'libelle', 'urgence', 'debut_prevu',
        'duree_prevue_min', 'debut_reel', 'fin_reelle', 'statut', 'chirurgien_id', 'anesthesiste_id', 'type_anesthesie',
        'compte_rendu', 'motif_annulation', 'created_by',
    ];

    protected $casts = ['debut_prevu' => 'datetime', 'debut_reel' => 'datetime', 'fin_reelle' => 'datetime'];

    public const STATUTS = ['programmee' => 'Programmée', 'en_cours' => 'En cours', 'terminee' => 'Terminée', 'annulee' => 'Annulée'];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class);
    }

    public function prestation()
    {
        return $this->belongsTo(Prestation::class);
    }

    public function chirurgien()
    {
        return $this->belongsTo(User::class, 'chirurgien_id');
    }

    public function anesthesiste()
    {
        return $this->belongsTo(User::class, 'anesthesiste_id');
    }

    public function equipe()
    {
        return $this->belongsToMany(User::class, 'intervention_equipe')->withPivot('role');
    }

    public function consommables()
    {
        return $this->hasMany(InterventionConsommable::class);
    }

    public function finPrevue(): \Illuminate\Support\Carbon
    {
        return $this->debut_prevu->copy()->addMinutes($this->duree_prevue_min);
    }
}
