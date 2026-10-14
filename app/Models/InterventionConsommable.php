<?php

namespace App\Models;

use App\Traits\Inalterable;
use Illuminate\Database\Eloquent\Model;

class InterventionConsommable extends Model
{
    use Inalterable;

    public const UPDATED_AT = null;

    protected $fillable = ['intervention_id', 'medicament_id', 'quantite', 'prix_unitaire', 'user_id'];

    protected $casts = ['prix_unitaire' => 'float'];

    public function medicament()
    {
        return $this->belongsTo(Medicament::class);
    }
}
