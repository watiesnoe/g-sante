<?php

namespace App\Models;
use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salle extends Model
{
    use HasUuid;
    use HasFactory;

    protected $fillable = [
        'nom',
        'type',
        'service_medical_id',
        'capacite',
        'prix',
    ];

    // 🔗 Relation : une salle appartient à un service médical
    public function serviceMedical()
    {
        return $this->belongsTo(ServiceMedical::class);
    }

    // 🔗 Relation : une salle peut contenir plusieurs lits
    public function lits()
    {
        return $this->hasMany(Lit::class);
    }

}
