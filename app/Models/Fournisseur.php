<?php

namespace App\Models;
use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fournisseur extends Model
{
    use HasUuid;
    use HasFactory;

    protected $fillable = ['nom',
'contact',
'email',
'adresse'];

    public function commandes()
    {
        return $this->hasMany(Commande::class);
    }

}
