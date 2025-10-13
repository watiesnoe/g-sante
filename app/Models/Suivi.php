<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suivi extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultation_id',
        'patient_id',
        'medecin_id',
        'date_heure',
        'motif',
        'resultat',
        'statut'
    ];

    public function consultation() { return $this->belongsTo(Consultation::class); }
    public function patient() { return $this->belongsTo(Patient::class); }
    public function medecin() { return $this->belongsTo(User::class, 'medecin_id'); }
}
