<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use Illuminate\Database\Eloquent\Model;

/** Unité de soins d'un service (hôpital : service → unité → chambre → lit). */
class UniteSoins extends Model
{
    use AppartientEtablissement;

    protected $table = 'unites_soins';

    protected $fillable = ['etablissement_id', 'service_medical_id', 'nom'];

    public function service()
    {
        return $this->belongsTo(ServiceMedical::class, 'service_medical_id');
    }

    public function salles()
    {
        return $this->hasMany(Salle::class, 'unite_soins_id');
    }
}
