<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class DocumentMedical extends Model
{
    use AppartientEtablissement, Audite, HasUuid;

    protected $table = 'documents_medicaux';

    protected $fillable = ['etablissement_id', 'patient_id', 'consultation_id', 'modele_document_id', 'type', 'titre', 'contenu', 'destinataire', 'user_id'];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
