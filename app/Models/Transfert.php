<?php

namespace App\Models;
use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfert extends Model
{
    use HasUuid;
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'consultation_id',
        'hospitalisation_id',
        'type',
        'source_medecin_id',
        'dest_medecin_id',
        'source_service_id',
        'dest_service_id',
        'hopital_destination',
        'motif',
        'date_transfert',
        'user_id',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function hospitalisation()
    {
        return $this->belongsTo(Hospitalisation::class);
    }

    public function sourceMedecin()
    {
        return $this->belongsTo(User::class, 'source_medecin_id');
    }

    public function destMedecin()
    {
        return $this->belongsTo(User::class, 'dest_medecin_id');
    }

    public function sourceService()
    {
        return $this->belongsTo(ServiceMedical::class, 'source_service_id');
    }

    public function destService()
    {
        return $this->belongsTo(ServiceMedical::class, 'dest_service_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
