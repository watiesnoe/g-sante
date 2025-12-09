<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RendezVous extends Model
{
    protected $table = 'rendezvous'; // ✅ corrige l'erreur

    protected $fillable = [
        'consultation_id',
        'patient_id',
        'medecin_id',
        'date_heure',
        'motif',
        'statut',
    ];
    protected $dates = ['date_heure'];
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function medecin()
    {
        return $this->belongsTo(User::class, 'medecin_id');
    }

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }
}
