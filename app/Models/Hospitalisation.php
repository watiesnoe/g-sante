<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Hospitalisation extends Model
{
    protected $fillable = [
        'consultation_id',
        'salles_id',
        'lit_id',
        'date_entree',
        'date_sortie',
        'motif',
        'etat',
        'service_id',
        'observations',
    ];
    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'hospitalisation_id');
    }

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class, 'salles_id');
    }

    public function lit()
    {
        return $this->belongsTo(Lit::class, 'lit_id');
    }

    public function service()
    {
        return $this->belongsTo(ServiceMedical::class);
    }
}
