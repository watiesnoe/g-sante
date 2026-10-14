<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Nomenclature codifiée des diagnostics (EF-04.02), partagée par tous les établissements. */
class Diagnostic extends Model
{
    protected $fillable = ['code', 'libelle', 'chapitre', 'actif'];

    protected $casts = ['actif' => 'boolean'];
}
