<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificat extends Model
{
    protected $fillable = [
        'consultation_id',
        'contenu',
        'date'];



     public function consultation() { return $this->belongsTo(Consultation::class); }
}
