<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

/**
 * Réquisition interne : un service demande des produits à la pharmacie, qui la
 * sert depuis son stock (sortie de type « dotation »). Ce n'est pas un achat.
 */
class Requisition extends Model
{
    use AppartientEtablissement, Audite, HasUuid;

    protected $fillable = [
        'etablissement_id', 'numero', 'service_medical_id', 'magasin_id', 'motif', 'statut',
        'demandeur_id', 'servie_par', 'servie_at', 'motif_rejet',
    ];

    protected $casts = ['servie_at' => 'datetime'];

    public const STATUTS = [
        'soumise'  => 'À servir',
        'partielle' => 'Servie en partie',
        'servie'   => 'Servie',
        'rejetee'  => 'Rejetée',
        'annulee'  => 'Annulée',
    ];

    public const COULEURS = ['soumise' => 'warning', 'partielle' => 'info', 'servie' => 'success', 'rejetee' => 'danger', 'annulee' => 'secondary'];

    public function lignes()
    {
        return $this->hasMany(RequisitionLigne::class);
    }

    public function service()
    {
        return $this->belongsTo(ServiceMedical::class, 'service_medical_id');
    }

    public function magasin()
    {
        return $this->belongsTo(Magasin::class);
    }

    public function demandeur()
    {
        return $this->belongsTo(User::class, 'demandeur_id');
    }

    public function serveur()
    {
        return $this->belongsTo(User::class, 'servie_par');
    }
}
