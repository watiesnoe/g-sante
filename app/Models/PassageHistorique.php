<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PassageHistorique extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['passage_id', 'de', 'vers', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
