<?php

namespace App\Models\EnLigne;

use Illuminate\Database\Eloquent\Model;

/** Créneau libre publié par le serveur local ; remplacé à chaque synchronisation. */
class Creneau extends Model
{
    protected $table = 'en_ligne_creneaux';

    public $timestamps = false;

    protected $fillable = ['site_id', 'medecin_uuid', 'debut'];

    protected $casts = ['debut' => 'datetime'];
}
