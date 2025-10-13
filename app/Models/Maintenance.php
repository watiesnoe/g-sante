<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    use AppartientEtablissement, Audite;

    protected $fillable = ['etablissement_id', 'equipement_id', 'type', 'description', 'statut', 'signale_par', 'intervention_realisee', 'cout', 'resolue_par', 'resolue_at'];

    protected $casts = ['resolue_at' => 'datetime', 'cout' => 'float'];

    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }

    public function signalePar()
    {
        return $this->belongsTo(User::class, 'signale_par');
    }
}
