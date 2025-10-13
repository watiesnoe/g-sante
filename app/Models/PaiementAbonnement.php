<?php

namespace App\Models;

use App\Traits\Audite;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class PaiementAbonnement extends Model
{
    use Audite, HasUuid;

    protected $table = 'paiements_abonnement';

    protected $fillable = ['numero', 'abonnement_id', 'montant', 'mode', 'reference', 'paye_le', 'periode_debut', 'periode_fin', 'user_id'];

    protected $casts = ['paye_le' => 'date', 'periode_debut' => 'date', 'periode_fin' => 'date', 'montant' => 'decimal:2'];

    public const MODES = ['especes' => 'Espèces', 'virement' => 'Virement', 'mobile' => 'Paiement mobile', 'cheque' => 'Chèque'];

    public function abonnement()
    {
        return $this->belongsTo(Abonnement::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
