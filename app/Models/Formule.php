<?php

namespace App\Models;

use App\Traits\Audite;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

/** Formule d'abonnement de la plateforme (définie par le super-admin) : modules, limites, prix. */
class Formule extends Model
{
    use Audite, HasUuid;

    protected $fillable = [
        'nom', 'description', 'prix', 'devise', 'duree_mois', 'modules', 'max_utilisateurs', 'max_lits', 'max_sms_mois',
        'modules_coupes', 'jours_grace', 'actif', 'ordre',
    ];

    protected $casts = [
        'modules' => 'array', 'modules_coupes' => 'array', 'actif' => 'boolean', 'prix' => 'decimal:2',
    ];

    /**
     * Modules jamais coupés pour retard de paiement : des patients peuvent être
     * en soins (urgence, séjour, accouchement) au moment de l'échéance.
     */
    public const MODULES_PROTEGES = ['urgences', 'hospitalisation', 'maternity'];

    public function abonnements()
    {
        return $this->hasMany(Abonnement::class);
    }
}
