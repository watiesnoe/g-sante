<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultatExamen extends Model
{

    protected $table = 'resultats_examens'; // ✅ correction
    protected $fillable = ['prescription_examen_id', 'resultat', 'fichier'];

    public function prescriptionExamen()
    {
        return $this->belongsTo(PrescriptionExamen::class, 'prescription_examen_id');
    }

}
