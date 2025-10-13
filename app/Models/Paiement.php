<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;use App\Traits\HasUuid;
use App\Traits\Inalterable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    use AppartientEtablissement;
    use Audite;
    use HasUuid;
    use Inalterable;

    /** Seuls champs qu'une annulation peut renseigner (RG-01). */
    protected array $champsModifiables = [];
    use HasFactory;

    protected $fillable = [
        'hospitalisation_id',
        'prescriptions_examens_id',
        'montant_total',
        'montant_recu',
        'montant_restant',
        'statut',
        'mode_paiement',
        'date_paiement',
    ];

    // 🔗 Relations
    public function hospitalisation()
    {
        return $this->belongsTo(Hospitalisation::class);
    }

    public function prescriptionExamen()
    {
        return $this->belongsTo(PrescriptionExamen::class, 'prescriptions_examens_id');
    }
}
