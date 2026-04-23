<?php

namespace App\Models;
use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Model;

class ConsultationPrenatale extends Model
{
    use HasUuid;
    protected $table = 'consultations_prenatales';

    protected $fillable = [
        'grossesse_id', 'numero_cpn', 'date_cpn', 'poids', 
        'tension', 'hauteur_uterine', 'bcf', 'mouvement_foetal', 
        'oedemes', 'observations', 'traitement_recu', 'prochain_rdv'
    ];

    public function grossesse()
    {
        return $this->belongsTo(Grossesse::class);
    }
}
