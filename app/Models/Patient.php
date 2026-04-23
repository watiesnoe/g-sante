<?php

namespace App\Models;
use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Model;

// app/Models/Patient.php
class Patient extends Model {
    use HasUuid;
    protected $fillable = ['nom',
        'prenom',
        'genre',
        'telephone',
        'ethnie',
        'age',
        'adresse',
        'groupe_sanguin',
        'assurance_id',
        'numero_assurance',
        'fin_validite_assurance',
        'est_decede',
        'date_deces',
    ];

    public function assurance() { return $this->belongsTo(Assurance::class); }

    public function consultations() { return $this->hasMany(Consultation::class); }
    public function hospitalisations() {
        return $this->hasManyThrough(
            Hospitalisation::class,
            Consultation::class,
            'patient_id',       // clé étrangère dans consultations
            'consultation_id',  // clé étrangère dans hospitalisations
            'id',               // clé locale patient
            'id'                // clé locale consultation
        );
    }

    public function rendezVous() { return $this->hasMany(RendezVous::class); }
    public function tickets() { return $this->hasMany(Ticket::class); }
    public function ordonnances() { return $this->hasManyThrough(Ordonnance::class, Consultation::class); }
    public function examens() { return $this->hasManyThrough(PrescriptionExamen::class, Consultation::class); }
    public function grossesses() { return $this->hasMany(Grossesse::class); }
    public function transferts() { return $this->hasMany(Transfert::class); }
    public function getNomCompletAttribute() {
        return "{$this->prenom} {$this->nom}";
    }
}
