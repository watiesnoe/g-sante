<?php

namespace App\Models;

use App\Traits\Audite;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventaireLigne extends Model
{
    use Audite;
    use HasFactory;

    protected $fillable = [
        'inventaire_id',
        'medicament_id',
        'stock_theorique',
        'stock_reel',
        'observations',
    ];



    public function inventaire()
    {
        return $this->belongsTo(Inventaire::class);
    }

    public function medicament()
    {
        return $this->belongsTo(Medicament::class);
    }


}
