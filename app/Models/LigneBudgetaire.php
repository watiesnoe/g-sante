<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;
use Illuminate\Database\Eloquent\Model;

/** Ligne budgétaire (EF-12.03) : prévu ; engagé = dépenses validées ; réalisé = dépenses payées. */
class LigneBudgetaire extends Model
{
    use AppartientEtablissement, Audite;

    protected $table = 'lignes_budgetaires';

    protected $fillable = ['etablissement_id', 'exercice', 'code', 'libelle', 'montant_prevu'];

    protected $casts = ['montant_prevu' => 'float'];

    public function depenses()
    {
        return $this->hasMany(Depense::class);
    }

    /** Engagé : dépenses validées (payées ou non). */
    public function engage(?int $saufDepenseId = null): float
    {
        return (float) $this->depenses()->whereIn('statut', ['validee', 'payee'])
            ->when($saufDepenseId, fn($q) => $q->where('id', '!=', $saufDepenseId))->sum('montant');
    }

    public function realise(): float
    {
        return (float) $this->depenses()->where('statut', 'payee')->sum('montant');
    }
}
