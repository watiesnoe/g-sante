<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

/**
 * Passage : venue d'un patient dans l'établissement, de l'arrivée à la sortie (EF-03.01).
 */
class Passage extends Model
{
    use AppartientEtablissement, Audite, HasUuid;

    protected $fillable = [
        'etablissement_id', 'numero', 'patient_id', 'service_medical_id', 'ticket_id', 'statut', 'urgence', 'decision', 'orientation',
        'priorite', 'motif', 'arrive_at', 'pris_en_charge_at', 'termine_at', 'user_id',
    ];

    public const DECISIONS = [
        'retour_domicile' => 'Retour à domicile',
        'observation'     => 'Mise en observation',
        'hospitalisation' => 'Hospitalisation',
        'reference'       => 'Référence / évacuation',
        'deces'           => 'Décès',
    ];

    protected $casts = [
        'urgence'           => 'boolean',
        'arrive_at'         => 'datetime',
        'pris_en_charge_at' => 'datetime',
        'termine_at'        => 'datetime',
    ];

    public const STATUTS = [
        'enregistre'      => 'Enregistré',
        'trie'            => 'Trié',
        'en_consultation' => 'En consultation',
        'en_examen'       => 'En examen',
        'termine'         => 'Terminé',
        'hospitalise'     => 'Hospitalisé',
        'refere'          => 'Référé',
        'sorti'           => 'Sorti',
    ];

    /** Statuts qui ferment le passage. */
    public const FINAUX = ['termine', 'hospitalise', 'refere', 'sorti'];

    public const COULEURS = [
        'enregistre' => 'secondary', 'trie' => 'warning', 'en_consultation' => 'primary', 'en_examen' => 'info',
        'termine' => 'success', 'hospitalise' => 'dark', 'refere' => 'danger', 'sorti' => 'light',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function service()
    {
        return $this->belongsTo(ServiceMedical::class, 'service_medical_id');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }

    public function historiques()
    {
        return $this->hasMany(PassageHistorique::class)->orderBy('id');
    }

    public function scopeOuverts($query)
    {
        return $query->whereNotIn('statut', self::FINAUX);
    }

    public function estOuvert(): bool
    {
        return !in_array($this->statut, self::FINAUX, true);
    }
}
