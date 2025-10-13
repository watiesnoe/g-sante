<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use Illuminate\Database\Eloquent\Model;

/** Magasin de stockage (EF-08.07) : pharmacie centrale, dépôts de dispensation. */
class Magasin extends Model
{
    use AppartientEtablissement;

    protected $fillable = ['etablissement_id', 'nom', 'type', 'par_defaut', 'actif'];

    protected $casts = ['par_defaut' => 'boolean', 'actif' => 'boolean'];

    public static function parDefaut(): self
    {
        return static::where('par_defaut', true)->first()
            ?? static::firstOrCreate(['nom' => 'Pharmacie'], ['type' => 'dispensation', 'par_defaut' => true, 'actif' => true]);
    }
}
