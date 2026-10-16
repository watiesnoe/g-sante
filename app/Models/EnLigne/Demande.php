<?php

namespace App\Models\EnLigne;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

/** Demande de rendez-vous faite en ligne ; elle ne devient un rendez-vous qu'après confirmation locale (RG-17). */
class Demande extends Model
{
    use HasUuid;

    protected $table = 'en_ligne_demandes';

    protected $fillable = [
        'uuid', 'site_id', 'medecin_uuid', 'creneau', 'motif', 'patient_nom', 'patient_prenom', 'patient_telephone',
        'patient_compte_id', 'statut', 'creneau_propose', 'message', 'rdv_uuid', 'remplace_uuid',
    ];

    protected $casts = ['creneau' => 'datetime', 'creneau_propose' => 'datetime'];

    public const STATUTS = [
        'en_attente' => 'En attente de confirmation',
        'confirmee'  => 'Confirmée',
        'proposee'   => 'Autre créneau proposé',
        'refusee'    => 'Refusée',
        'annulee'    => 'Annulée',
        'conflit'    => 'Créneau plus disponible',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function nomMedecin(): string
    {
        return Medecin::where('site_id', $this->site_id)->where('uuid', $this->medecin_uuid)->value('nom') ?? 'Médecin';
    }

    /** Le patient peut-il encore annuler ou déplacer (EF-14.04) ? */
    public function modifiable(): bool
    {
        return match ($this->statut) {
            'en_attente', 'proposee' => true,
            'confirmee' => now()->addHours($this->site->delai_annulation_heures ?? 24)->lte($this->creneau),
            default => false,
        };
    }
}
