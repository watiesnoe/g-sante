<?php

namespace App\Models;
use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Model;

class Symptome extends Model
{
    use HasUuid;
    protected $fillable = ['nom', 'description'];

    public function maladies()
    {
        return $this->belongsToMany(Maladie::class);
    }
    public function consultations()
    {
        return $this->belongsToMany(Consultation::class, 'consultation_symptome');
    }
}
