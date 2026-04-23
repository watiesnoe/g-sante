<?php

namespace App\Models;
use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Model;

class Examen extends Model
{
    use HasUuid;
    protected $fillable = ['nom', 'description', 'prix', 'service_medical_id'];

    public function serviceMedical()
    {
        return $this->belongsTo(ServiceMedical::class);
    }
}
