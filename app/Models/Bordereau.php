<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

/** Bordereau de facturation à un organisme payeur (EF-07.04). */
class Bordereau extends Model
{
    use AppartientEtablissement, Audite, HasUuid;

    protected $table = 'bordereaux';

    protected $fillable = ['etablissement_id', 'assurance_id', 'numero', 'periode_du', 'periode_au', 'montant', 'montant_recouvre', 'statut', 'user_id'];

    protected $casts = [
        'periode_du'       => 'date',
        'periode_au'       => 'date',
        'montant'          => 'float',
        'montant_recouvre' => 'float',
    ];

    public const STATUTS = ['emis' => 'Émis', 'partiel' => 'Partiellement réglé', 'regle' => 'Réglé', 'annule' => 'Annulé'];

    public function assurance()
    {
        return $this->belongsTo(Assurance::class);
    }

    public function lignes()
    {
        return $this->hasMany(BordereauLigne::class)->orderBy('date_acte');
    }

    public function recouvrements()
    {
        return $this->hasMany(Recouvrement::class)->orderBy('date_recouvrement');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resteDu(): float
    {
        return round($this->montant - $this->montant_recouvre, 2);
    }
}
