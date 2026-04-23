<?php

namespace App\Models;
use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lit extends Model
{
    use HasUuid;
    use HasFactory;

    protected $fillable = [
        'numero',
        'salle_id',
        'statut',
    ];

    // Relation : un lit appartient à une salle
    public function salle()
    {
        return $this->belongsTo(Salle::class);
    }
}
