<?php

namespace App\Models;
use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceMedical extends Model
{
    use HasUuid;
    use HasFactory;

    protected $fillable = ['nom', 'description'];


    public function users()
    {
        return $this->belongsToMany(User::class, 'service_user');
    }
    public function salles()
    {
        return $this->hasMany(Salle::class, 'service_medical_id');
    }

    public function prestations()
    {
        return $this->hasMany(Prestation::class, 'service_medical_id');
    }

    public function examens()
    {
        return $this->hasMany(Examen::class);
    }

}
