<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;
use Illuminate\Database\Eloquent\Model;

/** Prix d'une prestation à partir d'une date d'effet (EF-05.01). */
class Tarif extends Model
{
    use AppartientEtablissement, Audite;

    protected $fillable = ['etablissement_id', 'prestation_id', 'prix', 'date_effet', 'user_id'];

    protected $casts = [
        'prix'       => 'decimal:2',
        'date_effet' => 'date',
    ];

    public function prestation()
    {
        return $this->belongsTo(Prestation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
