<?php
namespace App\Models;
use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Model;

class ResultatExamen extends Model
{
    use HasUuid;

    protected $table = 'resultats_examens'; // ✅ correction
    protected $fillable = ['prescription_examen_id', 'resultat', 'fichier'];

    public function prescriptionExamen()
    {
        return $this->belongsTo(PrescriptionExamen::class, 'prescription_examen_id');
    }

}
