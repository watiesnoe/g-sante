<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salle extends Model
{
    use AppartientEtablissement;
    use HasUuid;
    use HasFactory;

    /** Types de salle utilisés comme salles d'opération. */
    public const TYPES_BLOC = ['Blocoperatoire', 'Chirurgie', 'bloc'];

    protected $fillable = ['unite_soins_id', 
        'nom',
        'type',
        'service_medical_id',
        'capacite',
        'prix',
    ];

    // 🔗 Relation : une salle appartient à un service médical
    public function serviceMedical()
    {
        return $this->belongsTo(ServiceMedical::class);
    }

    // 🔗 Relation : une salle peut contenir plusieurs lits
    public function lits()
    {
        return $this->hasMany(Lit::class);
    }


    public function uniteSoins()
    {
        return $this->belongsTo(UniteSoins::class, 'unite_soins_id');
    }

    public function service()
    {
        return $this->belongsTo(ServiceMedical::class, 'service_medical_id');
    }
}
