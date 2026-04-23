<?php

namespace App\Models;
use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Model;

class Grossesse extends Model
{
    use HasUuid;
    protected $fillable = [
        'patient_id', 'ddr', 'dpa', 'parite', 
        'gestite', 'antecedents_particuliers', 'statut', 'issue', 'date_fin'
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
