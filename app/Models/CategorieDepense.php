<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use Illuminate\Database\Eloquent\Model;

class CategorieDepense extends Model
{
    use AppartientEtablissement;

    protected $table = 'categories_depense';

    protected $fillable = ['etablissement_id', 'nom'];
}
