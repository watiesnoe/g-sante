<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unite extends Model
{
    use HasFactory;

    protected $fillable = ['nom'];

    // Relation avec les médicaments si tu en as
    public function medicaments()
    {
        return $this->hasMany(Medicament::class);
    }
}
