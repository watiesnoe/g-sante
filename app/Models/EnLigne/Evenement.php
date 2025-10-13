<?php

namespace App\Models\EnLigne;

use Illuminate\Database\Eloquent\Model;

class Evenement extends Model
{
    protected $table = 'en_ligne_evenements';

    const UPDATED_AT = null;

    protected $fillable = ['site_id', 'type', 'donnees'];

    protected $casts = ['donnees' => 'array'];
}
