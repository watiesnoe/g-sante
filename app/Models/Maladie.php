<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Maladie extends Model
{
    protected $fillable = ['nom', 'description'];

    public function symptomes()
    {
        return $this->belongsToMany(Symptome::class);
    }
}
