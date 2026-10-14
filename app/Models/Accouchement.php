<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

/** Accouchement (EF-10.04). Les champs suivent le registre usuel ; formulaire officiel à valider (section 18). */
class Accouchement extends Model
{
    use AppartientEtablissement, Audite, HasUuid;

    protected $fillable = [
        'etablissement_id', 'grossesse_id', 'patient_id', 'hospitalisation_id', 'date_heure', 'mode', 'terme_sa', 'presentation',
        'etat_mere', 'complications', 'delivrance_complete', 'perte_sang_ml', 'accoucheur_id', 'observations',
    ];

    protected $casts = ['date_heure' => 'datetime', 'delivrance_complete' => 'boolean'];

    public const MODES = ['voie_basse' => 'Voie basse', 'ventouse' => 'Ventouse', 'forceps' => 'Forceps', 'cesarienne' => 'Césarienne'];

    public const ETATS_MERE = ['bon' => 'Bon état', 'complique' => 'Complications', 'deces' => 'Décès maternel'];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function grossesse()
    {
        return $this->belongsTo(Grossesse::class);
    }

    public function nouveauNes()
    {
        return $this->hasMany(NouveauNe::class);
    }

    public function accoucheur()
    {
        return $this->belongsTo(User::class, 'accoucheur_id');
    }
}
