<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultationSuggestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultation_id',
        'pathologie_id',
        'score',
        'niveau_confiance'
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function pathologie()
    {
        return $this->belongsTo(Maladie::class, 'pathologie_id');
    }
    
}
