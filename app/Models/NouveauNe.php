<?php

namespace App\Models;

use App\Traits\Audite;
use Illuminate\Database\Eloquent\Model;

class NouveauNe extends Model
{
    use Audite;

    protected $table = 'nouveau_nes';

    protected $fillable = ['accouchement_id', 'patient_id', 'sexe', 'etat', 'poids_g', 'taille_cm', 'perimetre_cranien_cm', 'apgar_1', 'apgar_5', 'reanimation', 'observations'];

    protected $casts = ['reanimation' => 'boolean'];

    public const ETATS = ['vivant' => 'Né vivant', 'mort_ne_frais' => 'Mort-né frais', 'mort_ne_macere' => 'Mort-né macéré', 'deces_neonatal' => 'Décès néonatal'];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function accouchement()
    {
        return $this->belongsTo(Accouchement::class);
    }
}
