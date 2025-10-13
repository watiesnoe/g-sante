<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Famille extends Model
{
    protected $fillable = ['nom'];
    public function medicaments()
    {
        return $this->hasMany(Medicament::class);
    }
}
