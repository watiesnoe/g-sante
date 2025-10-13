<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceptionLigne extends Model
{
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
}
