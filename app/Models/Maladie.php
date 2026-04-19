<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Maladie extends Model
{
    protected $fillable = ['nom', 'description'];

    // Récupère tous les symptômes liés à cette maladie
    public function symptomes()
    {
        return $this->belongsToMany(Symptome::class, 'maladie_symptome');
    }

    // Récupère le protocole de traitement unique de cette maladie
    public function protocole()
    {
        return $this->hasOne(ProtocoleTraitement::class);
    }

    // Récupère les consultations via maladie_id (1-N)
    public function consultationsDirectes()
    {
        return $this->hasMany(Consultation::class);
    }

    // Récupère les consultations via la table pivot consultation_maladie (N-N)
    public function consultations()
    {
        return $this->belongsToMany(Consultation::class, 'consultation_maladie');
    }
}