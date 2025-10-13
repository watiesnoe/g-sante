<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SynchroJournal extends Model
{
    protected $table = 'synchro_journal';

    public $timestamps = false;

    protected $fillable = ['etablissement_id', 'debut', 'fin', 'statut', 'envoyes', 'recus', 'message'];

    protected $casts = ['debut' => 'datetime', 'fin' => 'datetime'];
}
