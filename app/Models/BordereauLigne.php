<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BordereauLigne extends Model
{
    public $timestamps = false;

    protected $fillable = ['bordereau_id', 'source_type', 'source_id', 'patient_id', 'date_acte', 'libelle', 'montant'];

    protected $casts = ['date_acte' => 'date', 'montant' => 'float'];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
