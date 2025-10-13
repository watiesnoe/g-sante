<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Model;

class PrescriptionExamen extends Model
{
    use AppartientEtablissement;
    use HasUuid;

    protected $table = 'prescriptions_examens'; // ✅ correction

    protected $fillable = [
        'consultation_id',
        'examen_id',
        'examen',
        'type',
        'notes',
        'statut',
        'preleve_at', 'preleve_par', 'planifie_le', 'realise_at', 'valide_at', 'valide_par', 'motif_annulation',
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function resultat()
    {
        return $this->hasOne(ResultatExamen::class);
    }

    /** EF-09.02 : demandé → prélevé ou planifié → réalisé → saisi → validé. */
    public const STATUTS = [
        'demande'  => 'Demandé',
        'preleve'  => 'Prélevé',
        'planifie' => 'Planifié',
        'realise'  => 'Réalisé',
        'saisi'    => 'Résultat saisi',
        'valide'   => 'Validé',
        'annule'   => 'Annulé',
    ];

    public const COULEURS = [
        'demande' => 'secondary', 'preleve' => 'info', 'planifie' => 'info', 'realise' => 'primary',
        'saisi' => 'warning', 'valide' => 'success', 'annule' => 'dark',
    ];

    protected $casts = [
        'preleve_at'  => 'datetime',
        'planifie_le' => 'datetime',
        'realise_at'  => 'datetime',
        'valide_at'   => 'datetime',
    ];

    public function catalogue()
    {
        return $this->belongsTo(Examen::class, 'examen_id');
    }

    public function validateur()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }
}
