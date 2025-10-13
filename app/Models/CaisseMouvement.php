<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;
use App\Traits\Inalterable;

class CaisseMouvement extends Model
{
    use AppartientEtablissement;
    use Audite;
    use HasFactory, HasUuid, Inalterable;

    /** Seuls champs qu'une annulation peut renseigner sur l'original. */
    protected array $champsModifiables = ['statut', 'motif_annulation', 'annule_par', 'annule_at'];

    public const MODES = [
        'especes'        => 'Espèces',
        'mobile'         => 'Paiement mobile',
        'cheque'         => 'Chèque',
        'virement'       => 'Virement',
        'carte'          => 'Carte',
        'prise_en_charge'=> 'Prise en charge',
        'autre'          => 'Autre',
    ];

    protected $fillable = [
        'caisse_session_id',
        'user_id',
        'type',
        'montant',
        'motif',
        'mode',
        'statut',
        'reference_type',
        'reference_id',
        'mouvement_annule_id',
        'motif_annulation',
        'annule_par',
        'annule_at',
    ];

    protected $casts = [
        'annule_at' => 'datetime',
    ];

    public function annulePar()
    {
        return $this->belongsTo(User::class, 'annule_par');
    }

    public function mouvementAnnule()
    {
        return $this->belongsTo(self::class, 'mouvement_annule_id');
    }

    public function session()
    {
        return $this->belongsTo(CaisseSession::class, 'caisse_session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
