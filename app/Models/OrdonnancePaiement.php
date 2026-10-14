<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;use App\Traits\HasUuid;
use App\Traits\Inalterable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdonnancePaiement extends Model
{
    use AppartientEtablissement;
    use Audite;
    use HasUuid;
    use Inalterable;

    /** Seuls champs qu'une annulation peut renseigner (RG-01). */
    protected array $champsModifiables = ['statut', 'motif_annulation', 'annule_par', 'annule_at'];
    use HasFactory;

    // Table associée
    protected $table = 'ordonnance_paiements';

    // Champs remplissables (mass assignable)
    protected $fillable = [
        'ordonnance_id',
        'medicament_id',
        'quantite',
        'prix_total',
        'assurance_id',
        'convention_id',
        'part_assurance',
        'part_gratuite',
        'regle_gratuite_id',
        'part_patient',
        'lot_id',
        'statut',
        'motif_annulation',
        'annule_par',
        'annule_at',
    ];

    /**
     * Relation avec l'ordonnance
     */
    public function ordonnance()
    {
        return $this->belongsTo(Ordonnance::class);
    }

    /**
     * Relation avec le médicament
     */
    public function medicament()
    {
        return $this->belongsTo(Medicament::class);
    }
}
