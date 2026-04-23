<?php

namespace App\Models;
use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assurance extends Model
{
    use HasUuid;
    use HasFactory;

    protected $fillable = [
        'nom',
        'telephone',
        'adresse',
        'taux'
    ];
}
