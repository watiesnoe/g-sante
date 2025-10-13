<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    use HasFactory;

    protected $fillable = [
        'hospitalisation_id',
        'prescriptions_examens_id',
        'montant_total',
        'montant_recu',
        'montant_restant',
        'statut',
        'mode_paiement',
        'date_paiement',
    ];

    // 🔗 Relations
    public function hospitalisation()
    {
        return $this->belongsTo(Hospitalisation::class);
    }

    public function analyse()
    {
        return $this->belongsTo(Analyse::class);
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'hospitalisation_id');
    }

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function service()
    {
        return $this->belongsTo(ServiceMedical::class);
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class, 'salles_id');
    }

    public function lit()
    {
        return $this->belongsTo(Lit::class);
    }

}
