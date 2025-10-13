<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;
use Illuminate\Database\Eloquent\Model;

/** Convention entre l'établissement et un organisme payeur (EF-07.01). */
class Convention extends Model
{
    use AppartientEtablissement, Audite;

    protected $fillable = ['etablissement_id', 'assurance_id', 'taux', 'plafond_par_facture', 'couvre_medicaments', 'couverture_actes', 'date_effet', 'date_fin'];

    protected $casts = [
        'taux'                => 'float',
        'plafond_par_facture' => 'float',
        'couvre_medicaments'  => 'boolean',
        'date_effet'          => 'date',
        'date_fin'            => 'date',
    ];

    public function assurance()
    {
        return $this->belongsTo(Assurance::class);
    }

    public function prestations()
    {
        return $this->belongsToMany(Prestation::class);
    }

    public function couvre(int $prestationId): bool
    {
        return $this->couverture_actes === 'toutes' || $this->prestations->contains('id', $prestationId);
    }

    /** Convention en vigueur à une date pour un organisme (EF-07.01 : date d'effet). */
    public static function applicable(?int $assuranceId, $date = null): ?self
    {
        if (!$assuranceId) {
            return null;
        }
        $date = $date ? \Illuminate\Support\Carbon::parse($date)->toDateString() : today()->toDateString();

        return static::with('prestations')->where('assurance_id', $assuranceId)
            ->whereDate('date_effet', '<=', $date)
            ->where(fn($q) => $q->whereNull('date_fin')->orWhereDate('date_fin', '>=', $date))
            ->orderByDesc('date_effet')->orderByDesc('id')->first();
    }
}
