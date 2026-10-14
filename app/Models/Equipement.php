<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

/** Équipement suivi en maintenance (EF-12.06). */
class Equipement extends Model
{
    use AppartientEtablissement, Audite, HasUuid;

    protected $fillable = ['etablissement_id', 'nom', 'categorie', 'numero_serie', 'service_medical_id', 'statut', 'date_acquisition', 'periodicite_jours', 'prochaine_maintenance'];

    protected $casts = ['date_acquisition' => 'date', 'prochaine_maintenance' => 'date'];

    public const STATUTS = ['en_service' => 'En service', 'en_panne' => 'En panne', 'hors_service' => 'Hors service'];

    public function service()
    {
        return $this->belongsTo(ServiceMedical::class, 'service_medical_id');
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class)->latest();
    }
}
