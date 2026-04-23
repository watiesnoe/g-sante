<?php

namespace App\Models;
use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Model;

class SuiviTraitement extends Model
{
    use HasUuid;
    protected $fillable = [
        'consultation_id', 'date_suivi', 'evolution', 
        'observations', 'recommandations', 'temperature', 'tension'
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }
}
