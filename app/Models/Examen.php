<?php

namespace App\Models;
use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Model;

class Examen extends Model
{
    use HasUuid;
    protected $fillable = ['nom', 'type', 'description', 'prix', 'service_medical_id', 'unite', 'valeur_min', 'valeur_max'];

    public function serviceMedical()
    {
        return $this->belongsTo(ServiceMedical::class);
    }
}
