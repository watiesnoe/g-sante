<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OccupationLit extends Model
{
    public $timestamps = false;

    protected $table = 'occupations_lit';

    protected $fillable = ['hospitalisation_id', 'lit_id', 'debut', 'fin', 'user_id'];

    protected $casts = ['debut' => 'datetime', 'fin' => 'datetime'];

    public function lit()
    {
        return $this->belongsTo(Lit::class);
    }

    public function hospitalisation()
    {
        return $this->belongsTo(Hospitalisation::class);
    }
}
