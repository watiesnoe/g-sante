<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use Illuminate\Database\Eloquent\Model;

/** Guichet de caisse (EF-06.06). */
class Guichet extends Model
{
    use AppartientEtablissement;

    protected $fillable = ['etablissement_id', 'nom', 'actif'];

    protected $casts = ['actif' => 'boolean'];
}
