<?php

namespace App\Models;
use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaiementCommande extends Model
{
    use HasUuid;
    use HasFactory;

    protected $fillable = [
        'commande_id',
        'montant',
        'mode',
        'date_paiement',
        'reference',
        'observations',
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
