<?php

namespace App\Models;
use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Model;

class Famille extends Model
{
    use HasUuid;
    protected $fillable = ['nom'];
    public function medicaments()
    {
        return $this->hasMany(Medicament::class);
    }
}
