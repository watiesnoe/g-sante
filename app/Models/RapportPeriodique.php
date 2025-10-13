<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

/** Rapport mensuel d'activité (EF-13.03). Transmis, il est figé ; une correction crée une nouvelle version (RG-15). */
class RapportPeriodique extends Model
{
    use AppartientEtablissement, Audite, HasUuid;

    protected $table = 'rapports_periodiques';

    protected $fillable = ['etablissement_id', 'type', 'periode_du', 'periode_au', 'version', 'statut', 'donnees', 'cree_par', 'valide_par', 'valide_at', 'transmis_at', 'motif_nouvelle_version'];

    protected $casts = ['donnees' => 'array', 'periode_du' => 'date', 'periode_au' => 'date', 'valide_at' => 'datetime', 'transmis_at' => 'datetime'];

    public const STATUTS = ['brouillon' => 'Brouillon', 'valide' => 'Validé', 'transmis' => 'Transmis'];

    protected static function booted(): void
    {
        // RG-15 : un rapport transmis ne change plus
        static::updating(function (RapportPeriodique $rapport) {
            if ($rapport->getOriginal('statut') === 'transmis') {
                throw new \LogicException('Rapport transmis : créez une nouvelle version pour le corriger (RG-15).');
            }
        });
        static::deleting(function (RapportPeriodique $rapport) {
            if ($rapport->statut !== 'brouillon') {
                throw new \LogicException('Seul un rapport en brouillon peut être supprimé.');
            }
        });
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'cree_par');
    }

    public function validateur()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }
}
