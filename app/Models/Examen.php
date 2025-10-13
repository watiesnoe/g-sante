<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Examen extends Model
{
    protected $fillable = ['nom', 'description', 'prix', 'service_medical_id'];

    public function serviceMedical()
    {
        return $this->belongsTo(ServiceMedical::class);
    }
}
