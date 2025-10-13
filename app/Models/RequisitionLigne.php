<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequisitionLigne extends Model
{
    protected $fillable = ['requisition_id', 'medicament_id', 'quantite_demandee', 'quantite_servie'];

    public function medicament()
    {
        return $this->belongsTo(Medicament::class);
    }
}
