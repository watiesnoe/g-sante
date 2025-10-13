<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;
use Illuminate\Database\Eloquent\Model;

/** Garde, astreinte ou permanence d'un membre du personnel (EF-12.05). */
class Garde extends Model
{
    use AppartientEtablissement, Audite;

    protected $fillable = ['etablissement_id', 'user_id', 'service_medical_id', 'type', 'debut', 'fin', 'observation', 'created_by'];

    protected $casts = ['debut' => 'datetime', 'fin' => 'datetime'];

    public const TYPES = ['garde' => 'Garde', 'astreinte' => 'Astreinte', 'permanence' => 'Permanence'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(ServiceMedical::class, 'service_medical_id');
    }
}
