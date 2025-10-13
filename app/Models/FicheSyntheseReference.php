<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FicheSyntheseReference extends Model
{
    protected $table = 'fiche_synthese_references';

    public $timestamps = false;

    protected $fillable = ['fiche_synthese_id', 'ouvrage', 'localisation', 'note'];
}
