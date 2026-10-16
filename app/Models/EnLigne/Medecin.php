<?php

namespace App\Models\EnLigne;

use Illuminate\Database\Eloquent\Model;

class Medecin extends Model
{
    protected $table = 'en_ligne_medecins';

    protected $fillable = ['site_id', 'uuid', 'nom', 'service'];
}
