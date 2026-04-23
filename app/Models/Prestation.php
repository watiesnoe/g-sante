<?php

namespace App\Models;
use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestation extends Model
{
    use HasUuid;
    use HasFactory;

    protected $fillable = ['service_medical_id', 'nom', 'description', 'prix'];

    public function serviceMedical()
    {
        return $this->belongsTo(ServiceMedical::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'prestation_user');
    }
}
