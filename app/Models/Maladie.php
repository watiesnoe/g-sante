<?php
namespace App\Models;

use App\Models\Consultation;
use App\Models\Symptome;
use Illuminate\Database\Eloquent\Model;

class Maladie extends Model
{
    protected $fillable = ['nom', 'description'];

    public function symptomes()
    {
        return $this->belongsToMany(Symptome::class);
    }
    
    public function consultations()
    {
        return $this->belongsToMany(Consultation::class, 'consultation_maladie', 'maladie_id', 'consultation_id');
    }

    public function protocole()
    {
        return $this->hasOne(ProtocoleTraitement::class, 'maladie_id');
    }
}
