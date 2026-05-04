<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class CaisseMouvement extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'caisse_session_id',
        'user_id',
        'type',
        'montant',
        'motif',
        'reference_type',
        'reference_id'
    ];

    public function session()
    {
        return $this->belongsTo(CaisseSession::class, 'caisse_session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
