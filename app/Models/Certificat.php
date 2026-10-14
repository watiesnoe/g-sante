<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Model;

class Certificat extends Model
{
    use AppartientEtablissement;
    use HasUuid;
    protected $fillable = [
        'consultation_id',
        'contenu',
        'date'];



     public function consultation() { return $this->belongsTo(Consultation::class); }
}
