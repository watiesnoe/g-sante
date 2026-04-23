<?php

namespace App\Models;
use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Model;

class PrescriptionExamen extends Model
{
    use HasUuid;

    protected $table = 'prescriptions_examens'; // ✅ correction

    protected $fillable = [
        'consultation_id',
        'examen',
        'notes',
        'statut' // e.g., 'prescribed', 'completed'
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function resultat()
    {
        return $this->hasOne(ResultatExamen::class);
    }
}
