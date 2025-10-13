<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

/** Référence ou évacuation, entrante ou sortante (EF-11.01 à EF-11.03). */
class Reference extends Model
{
    use AppartientEtablissement, Audite, HasUuid;

    protected $fillable = [
        'etablissement_id', 'numero', 'sens', 'evacuation', 'patient_id', 'passage_id', 'hospitalisation_id', 'structure_origine',
        'structure_destination', 'motif', 'soins_realises', 'transport', 'statut', 'date_reference', 'recue_at', 'contre_reference',
        'contre_reference_at', 'devenir', 'user_id',
    ];

    protected $casts = [
        'evacuation' => 'boolean', 'date_reference' => 'datetime', 'recue_at' => 'datetime', 'contre_reference_at' => 'datetime',
    ];

    public const STATUTS = [
        'emise' => 'Émise', 'recue' => 'Reçue', 'prise_en_charge' => 'Prise en charge',
        'contre_reference_envoyee' => 'Contre-référence envoyée', 'annulee' => 'Annulée',
    ];

    public const TRANSPORTS = ['ambulance' => 'Ambulance', 'vehicule_personnel' => 'Véhicule personnel', 'transport_public' => 'Transport public', 'autre' => 'Autre'];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
