<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;
use Illuminate\Database\Eloquent\Model;

class FusionPatient extends Model
{
    use AppartientEtablissement, Audite;

    protected $table = 'fusions_patients';

    protected $fillable = ['etablissement_id', 'patient_conserve_id', 'patient_fusionne_id', 'motif', 'deplacements', 'user_id', 'annulee_at', 'annulee_par'];

    protected $casts = [
        'deplacements' => 'array',
        'annulee_at'   => 'datetime',
    ];

    public function conserve()
    {
        return $this->belongsTo(Patient::class, 'patient_conserve_id');
    }

    public function fusionne()
    {
        return $this->belongsTo(Patient::class, 'patient_fusionne_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
