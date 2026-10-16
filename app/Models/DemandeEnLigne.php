<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;
use Illuminate\Database\Eloquent\Model;

/**
 * Demande de rendez-vous reçue du serveur en ligne. L'accueil la confirme (elle
 * devient un rendez-vous), propose un autre créneau ou la refuse (RG-17).
 */
class DemandeEnLigne extends Model
{
    use AppartientEtablissement, Audite;

    protected $table = 'demandes_en_ligne';

    protected $fillable = [
        'uuid', 'etablissement_id', 'medecin_id', 'creneau', 'motif', 'patient_nom', 'patient_prenom', 'patient_telephone',
        'patient_id', 'statut', 'creneau_propose', 'message', 'rendezvous_id', 'traite_par', 'traite_at', 'remplace_uuid',
    ];

    protected $casts = ['creneau' => 'datetime', 'creneau_propose' => 'datetime', 'traite_at' => 'datetime'];

    public const STATUTS = [
        'recue'     => 'À traiter',
        'confirmee' => 'Confirmée',
        'proposee'  => 'Autre créneau proposé',
        'refusee'   => 'Refusée',
        'annulee'   => 'Annulée par le patient',
        'conflit'   => 'Conflit : créneau déjà pris',
    ];

    public const COULEURS = ['recue' => 'warning', 'confirmee' => 'success', 'proposee' => 'info', 'refusee' => 'danger', 'annulee' => 'secondary', 'conflit' => 'dark'];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function medecin()
    {
        return $this->belongsTo(User::class, 'medecin_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function rendezVous()
    {
        return $this->belongsTo(RendezVous::class, 'rendezvous_id');
    }
}
