<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

/**
 * Proposition de commande de la pharmacie (EF-12.04) : soumise, approuvée par un
 * responsable, puis transformée en une commande par fournisseur. Les besoins des
 * services passent par une réquisition interne (voir Requisition).
 */
class DemandeAchat extends Model
{
    use AppartientEtablissement, Audite, HasUuid;

    protected $table = 'demandes_achat';

    protected $fillable = ['etablissement_id', 'numero', 'service_medical_id', 'motif', 'statut', 'demandeur_id', 'approuvee_par', 'approuvee_at', 'motif_rejet', 'commande_id'];

    protected $casts = ['approuvee_at' => 'datetime'];

    public const STATUTS = ['brouillon' => 'Brouillon', 'soumise' => 'À approuver', 'approuvee' => 'Approuvée', 'rejetee' => 'Rejetée', 'commandee' => 'Commandée'];

    public function lignes()
    {
        return $this->hasMany(DemandeAchatLigne::class);
    }

    public function demandeur()
    {
        return $this->belongsTo(User::class, 'demandeur_id');
    }

    public function service()
    {
        return $this->belongsTo(ServiceMedical::class, 'service_medical_id');
    }

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    /** Commandes issues de cette proposition (une par fournisseur). */
    public function commandes()
    {
        return $this->hasMany(Commande::class);
    }
}
