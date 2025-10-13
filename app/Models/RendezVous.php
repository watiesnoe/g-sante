<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Model;

class RendezVous extends Model
{
    use AppartientEtablissement;
    use HasUuid;
    protected $table = 'rendezvous'; // ✅ corrige l'erreur

    protected $fillable = [
        'consultation_id',
        'patient_id',
        'medecin_id',
        'date_heure',
        'motif',
        'resultat',
        'statut',
        'rappel_envoye_at',
    ];
    protected $dates = ['date_heure'];
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function medecin()
    {
        return $this->belongsTo(User::class, 'medecin_id');
    }

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    /** EF-03.04 : statuts de l'agenda (« réalisé » correspond à « honoré »). */
    public const STATUTS = [
        'prevu'      => 'Prévu',
        'en_attente' => 'En attente',
        'realise'    => 'Honoré',
        'absent'     => 'Absent',
        'annule'     => 'Annulé',
    ];

    public const COULEURS = [
        'prevu' => 'primary', 'en_attente' => 'warning', 'realise' => 'success', 'absent' => 'dark', 'annule' => 'danger',
    ];
}
