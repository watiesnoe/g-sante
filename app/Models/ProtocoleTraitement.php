<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProtocoleTraitement extends Model
{
    use HasFactory;

    protected $fillable = [
        'maladie_id',
        'titre',
        'signes',
        'diagnostics',
        'germes_nourrisson',
        'germes_adulte',
        'traitement_principal',
        'posologie_principale',
        'traitement_alternatif',
        'posologie_alternative',
        'remarques',
    ];

    public function maladie()
    {
        return $this->belongsTo(Maladie::class);
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }
}
