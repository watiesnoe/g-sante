<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;use App\Traits\HasUuid;
use App\Traits\Inalterable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaiementCommande extends Model
{
    use AppartientEtablissement;
    use Audite;
    use HasUuid;
    use Inalterable;

    /** Seuls champs qu'une annulation peut renseigner (RG-01). */
    protected array $champsModifiables = ['statut', 'motif_annulation', 'annule_par', 'annule_at'];
    use HasFactory;

    protected $fillable = [
        'commande_id',
        'montant',
        'mode',
        'date_paiement',
        'reference',
        'observations',
        'statut',
        'motif_annulation',
        'annule_par',
        'annule_at',
    ];

    protected static function booted()
    {
        static::creating(function ($paiement) {
            $paiement->reference = 'PAY-' . strtoupper(Str::random(6));
        });
    }

    /**
     * 🔗 Paiement lié à une commande
     */
    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }
}
