<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;
use App\Traits\Inalterable;
use Illuminate\Database\Eloquent\Model;

class Recouvrement extends Model
{
    use AppartientEtablissement, Audite, Inalterable;

    protected $fillable = ['etablissement_id', 'bordereau_id', 'montant', 'date_recouvrement', 'mode', 'reference', 'user_id'];

    protected $casts = ['date_recouvrement' => 'date', 'montant' => 'float'];
}
