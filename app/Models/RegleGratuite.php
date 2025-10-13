<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;
use Illuminate\Database\Eloquent\Model;

/**
 * Règle de gratuité (EF-05.03). Les conditions et la liste des actes couverts
 * sont saisies par l'établissement d'après les textes officiels : le logiciel
 * n'en invente aucune (section 3.2).
 */
class RegleGratuite extends Model
{
    use AppartientEtablissement, Audite;

    protected $table = 'regles_gratuite';

    protected $fillable = ['etablissement_id', 'nom', 'age_max_mois', 'genre', 'grossesse_en_cours', 'portee_actes', 'couvre_medicaments', 'date_effet', 'date_fin', 'actif'];

    protected $casts = [
        'grossesse_en_cours' => 'boolean', 'couvre_medicaments' => 'boolean', 'actif' => 'boolean',
        'date_effet' => 'date', 'date_fin' => 'date',
    ];

    public function prestations()
    {
        return $this->belongsToMany(Prestation::class, 'regle_gratuite_prestation');
    }

    public function medicaments()
    {
        return $this->belongsToMany(Medicament::class, 'regle_gratuite_medicament');
    }
}
