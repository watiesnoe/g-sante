<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmploiDuTemps extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'emploi_du_temps';

    protected $fillable = [
        'medecin_id',
        'jour_semaine',
        'heure_debut',
        'heure_fin',
        'service',
        'lieu',
        'statut',
        'notes',
    ];

    /**
     * Noms des jours de la semaine (1=Lundi … 7=Dimanche)
     */
    public static array $jours = [
        1 => 'Lundi',
        2 => 'Mardi',
        3 => 'Mercredi',
        4 => 'Jeudi',
        5 => 'Vendredi',
        6 => 'Samedi',
        7 => 'Dimanche',
    ];

    /**
     * Nom du jour en français
     */
    public function getJourNomAttribute(): string
    {
        return self::$jours[$this->jour_semaine] ?? 'Inconnu';
    }

    /**
     * Retourne la plage horaire formatée
     */
    public function getPlageHoraireAttribute(): string
    {
        return substr($this->heure_debut, 0, 5) . ' – ' . substr($this->heure_fin, 0, 5);
    }

    /**
     * Relation : le médecin
     */
    public function medecin()
    {
        return $this->belongsTo(User::class, 'medecin_id');
    }

    /**
     * Scope : uniquement les plannings actifs
     */
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }
}
