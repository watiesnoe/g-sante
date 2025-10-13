<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/Patient.php
class Patient extends Model {
    protected $fillable = ['nom',
        'prenom',
        'genre',
        'telephone',
        'ethnie',
        'age',
        'adresse',
        'groupe_sanguin'];

    public function consultations() { return $this->hasMany(Consultation::class); }
    public function hospitalisations() { return $this->hasMany(Hospitalisation::class); }
    public function rendezVous() { return $this->hasMany(RendezVous::class); }
}
