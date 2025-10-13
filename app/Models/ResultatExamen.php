<?php
namespace App\Models;

use App\Traits\AppartientEtablissement;use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Model;

class ResultatExamen extends Model
{
    use AppartientEtablissement;
    use HasUuid;

    protected $table = 'resultats_examens'; // ✅ correction
    protected $fillable = ['prescription_examen_id', 'resultat', 'fichier', 'valeur', 'unite', 'valeur_min', 'valeur_max', 'anormal', 'saisi_par'];

    protected $casts = ['anormal' => 'boolean'];

    public function saisiPar()
    {
        return $this->belongsTo(User::class, 'saisi_par');
    }

    public function prescriptionExamen()
    {
        return $this->belongsTo(PrescriptionExamen::class, 'prescription_examen_id');
    }

}
