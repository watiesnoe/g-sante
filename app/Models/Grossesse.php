<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grossesse extends Model
{
    protected $fillable = [
        'patient_id', 'ddr', 'dpa', 'parite', 
        'gestite', 'antecedents_particuliers', 'statut', 'date_fin'
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function cpns()
    {
        return $this->hasMany(ConsultationPrenatale::class);
    }
}
