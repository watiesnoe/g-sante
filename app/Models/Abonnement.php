<?php

namespace App\Models;

use App\Traits\Audite;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

/** Abonnement d'un établissement à une formule ; le plus récent est l'abonnement en cours. */
class Abonnement extends Model
{
    use Audite, HasUuid;

    protected $fillable = ['etablissement_id', 'formule_id', 'debut', 'fin', 'essai', 'statut', 'dernier_rappel', 'notes'];

    protected $casts = ['debut' => 'date', 'fin' => 'date', 'essai' => 'boolean'];

    public const STATUTS = [
        'actif'     => 'Actif',
        'grace'     => 'Échu — période de grâce',
        'restreint' => 'Modules coupés (retard de paiement)',
        'termine'   => 'Terminé',
    ];

    public const COULEURS = ['actif' => 'success', 'grace' => 'warning', 'restreint' => 'danger', 'termine' => 'secondary'];

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class);
    }

    public function formule()
    {
        return $this->belongsTo(Formule::class);
    }

    public function paiements()
    {
        return $this->hasMany(PaiementAbonnement::class);
    }

    public function joursRestants(): int
    {
        return (int) today()->diffInDays($this->fin, false);
    }

    /** Fin de la période de grâce : au-delà, les modules prévus sont coupés. */
    public function finGrace(): \Illuminate\Support\Carbon
    {
        return $this->fin->copy()->addDays($this->formule->jours_grace ?? 0);
    }
}
