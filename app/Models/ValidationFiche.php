<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;
use Illuminate\Database\Eloquent\Model;

/** Relecture d'une fiche du référentiel par un médecin ou un pharmacien de l'établissement. */
class ValidationFiche extends Model
{
    use AppartientEtablissement, Audite;

    protected $table = 'validations_fiches';

    protected $fillable = ['fiche_medicament_id', 'etablissement_id', 'user_id', 'empreinte', 'commentaire'];

    public function fiche()
    {
        return $this->belongsTo(FicheMedicament::class, 'fiche_medicament_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
