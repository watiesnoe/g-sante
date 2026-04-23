<?php

namespace App\Models;
use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Model;

class ReceptionLigne extends Model
{
    use HasUuid;
    protected $fillable = [
        'reception_id',
        'medicament_id',
        'quantite_commandee',
        'quantite_recue',
        'prix_unitaire',
        'date_peremption',
        'lot'
    ];

    public function medicament()
    {
        return $this->belongsTo(Medicament::class);
    }

    public function reception()
    {
        return $this->belongsTo(Reception::class);
    }
}
