<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuiviTraitement extends Model
{
    protected $fillable = [
        'consultation_id', 'date_suivi', 'evolution', 
        'observations', 'recommandations', 'temperature', 'tension'
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }
}
