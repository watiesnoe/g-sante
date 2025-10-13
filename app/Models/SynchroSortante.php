<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** Événement local en attente d'envoi au serveur en ligne (conservé jusqu'à l'accusé de réception). */
class SynchroSortante extends Model
{
    protected $table = 'synchro_sortante';

    const UPDATED_AT = null;

    protected $fillable = ['uuid', 'etablissement_id', 'type', 'donnees', 'envoye_at'];

    protected $casts = ['donnees' => 'array', 'envoye_at' => 'datetime'];

    public static function ajouter(int $etablissementId, string $type, array $donnees): self
    {
        return static::create(['uuid' => (string) Str::uuid(), 'etablissement_id' => $etablissementId, 'type' => $type, 'donnees' => $donnees]);
    }
}
