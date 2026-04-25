<?php

namespace App\Models;
use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model {
    use HasUuid;
    use HasFactory;

    protected $fillable = [
        'patient_id','medecin_id','ticket_id','protocole_id','date_consultation',
        'motif','diagnostic','notes','poids','temperature','tension',
        'taille','imc','groupe_sanguin','adresse_patient','maladie_id',
        'grossesse_id'
    ];

    public function grossesse() {
        return $this->belongsTo(Grossesse::class);
    }

    public function protocole() {
        return $this->belongsTo(ProtocoleTraitement::class, 'protocole_id');
    }
    public function patient() {
        return $this->belongsTo(Patient::class);
    }

    public function ticket() {
        return $this->belongsTo(Ticket::class);
    }

    public function medecin() {
        return $this->belongsTo(User::class, 'medecin_id');
    }

    // ✅ RELATION AU PLURIEL
    public function ordonnances() {
        return $this->hasMany(Ordonnance::class);
    }

    public function examens() {
        return $this->hasMany(PrescriptionExamen::class);
    }

    public function rendezVous() {
        return $this->hasMany(RendezVous::class);
    }

    public function certificat() {
        return $this->hasOne(Certificat::class);
    }

    public function hospitalisation() {
        return $this->hasOne(Hospitalisation::class);
    }

    public function symptomes() {
        return $this->belongsToMany(Symptome::class, 'consultation_symptome', 'consultation_id', 'symptome_id');
    }

    public function maladie() {
        return $this->belongsTo(Maladie::class, 'maladie_id');
    }

    public function maladies() {
        return $this->belongsToMany(Maladie::class, 'consultation_maladie', 'consultation_id', 'maladie_id');
    }

    public function paiements() {
        return $this->hasMany(OrdonnancePaiement::class);
    }

    public function suivis() {
        return $this->hasMany(SuiviTraitement::class);
    }

    public function suggestions() {
        return $this->hasMany(ConsultationSuggestion::class);
    }
}
