<?php

namespace App\Models;

use App\Traits\Audite;
use App\Traits\Inalterable;
use Illuminate\Database\Eloquent\Model;

/** Complément daté et signé d'une consultation signée (RG-13). */
class ConsultationAddendum extends Model
{
    use Audite, Inalterable;

    public const UPDATED_AT = null;

    protected $table = 'consultation_addenda';

    protected $fillable = ['consultation_id', 'contenu', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
