<?php

namespace App\Models;
use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unite extends Model
{
    use HasUuid;
    use HasFactory;

    protected $fillable = ['nom'];

    // Relation avec les médicaments si tu en as
    public function medicaments()
    {
        return $this->hasMany(Medicament::class);
    }
}
