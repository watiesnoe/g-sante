<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Model;

// app/Models/Patient.php
class Patient extends Model {
    use AppartientEtablissement;
    use Audite;
    use HasUuid;

    protected $casts = ['date_naissance' => 'date'];

    public const PROVENANCES = [
        'domicile'   => 'Domicile',
        'cscom'      => 'Référé par un CSCom',
        'structure'  => 'Référé par une autre structure',
        'evacuation' => 'Évacuation sanitaire',
    ];

    /** EF-02.01 : identifiant de dossier unique, au format de l'établissement. */
    protected static function booted(): void
    {
        static::creating(function (Patient $patient) {
            $patient->numero_dossier ??= app(\App\Services\NumerotationService::class)->suivant('patient');
        });
    }

    /**
     * EF-02.02 : dossiers probablement identiques (même nom et prénom, ou même
     * téléphone), à signaler avant toute création.
     */
    public static function doublonsProbables(string $nom, string $prenom, ?string $telephone = null, ?int $saufId = null)
    {
        return static::query()
            ->when($saufId, fn($q) => $q->where('id', '!=', $saufId))
            ->where(function ($q) use ($nom, $prenom, $telephone) {
                $q->where(fn($n) => $n->whereRaw('LOWER(TRIM(nom)) = ?', [mb_strtolower(trim($nom))])->whereRaw('LOWER(TRIM(prenom)) = ?', [mb_strtolower(trim($prenom))]));
                if ($telephone) {
                    $q->orWhere('telephone', $telephone);
                }
            })
            ->limit(5)->get(['id', 'uuid', 'numero_dossier', 'nom', 'prenom', 'age', 'telephone']);
    }
    protected $fillable = ['numero_dossier', 'date_naissance', 'provenance', 'cscom_origine', 'allergies', 'fusionne_dans_id', 'nom',
        'prenom',
        'genre',
        'telephone',
        'ethnie',
        'age',
        'adresse',
        'groupe_sanguin',
        'assurance_id',
        'numero_assurance',
        'fin_validite_assurance',
        'est_decede',
        'date_deces',
    ];

    public function assurance() { return $this->belongsTo(Assurance::class); }

    public function consultations() { return $this->hasMany(Consultation::class); }
    public function hospitalisations() {
        return $this->hasManyThrough(
            Hospitalisation::class,
            Consultation::class,
            'patient_id',       // clé étrangère dans consultations
            'consultation_id',  // clé étrangère dans hospitalisations
            'id',               // clé locale patient
            'id'                // clé locale consultation
        );
    }

    public function rendezVous() { return $this->hasMany(RendezVous::class); }
    public function tickets() { return $this->hasMany(Ticket::class); }
    public function ordonnances() { return $this->hasManyThrough(Ordonnance::class, Consultation::class); }
    public function examens() { return $this->hasManyThrough(PrescriptionExamen::class, Consultation::class); }
    public function grossesses() { return $this->hasMany(Grossesse::class); }
    public function transferts() { return $this->hasMany(Transfert::class); }
    public function getNomCompletAttribute() {
        return "{$this->prenom} {$this->nom}";
    }

    /**
     * Scope pour filtrer les patients selon la structure / le médecin connecté.
     * Seuls le super admin et l'admin peuvent voir tous les patients.
     * Les médecins ne voient que les patients liés à leur structure (service/médecin).
     */
    public function scopeForUserStructure($query, $user = null)
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return $query;
        }

        // Seuls le super_admin et l'admin ont accès à TOUS les patients
        if ($user->hasRole(['super_admin', 'superadmin', 'admin'])) {
            return $query;
        }

        $medecinId = $user->id;

        // Collecter les services de l'utilisateur
        $serviceIds = [];
        if ($user->service_medical_id) {
            $serviceIds[] = $user->service_medical_id;
        }
        if (method_exists($user, 'services')) {
            $pivotServices = $user->services()->pluck('service_medicals.id')->toArray();
            $serviceIds = array_unique(array_merge($serviceIds, $pivotServices));
        }

        // Médecins de la même structure/service
        $medecinIdsInService = [$medecinId];
        if (!empty($serviceIds)) {
            $doctorsInService = User::where(function ($q) use ($serviceIds) {
                $q->whereIn('service_medical_id', $serviceIds)
                  ->orWhereHas('services', function ($sq) use ($serviceIds) {
                      $sq->whereIn('service_medicals.id', $serviceIds);
                  });
            })->pluck('id')->toArray();
            $medecinIdsInService = array_unique(array_merge($medecinIdsInService, $doctorsInService));
        }

        return $query->where(function ($q) use ($medecinId, $medecinIdsInService, $serviceIds) {
            $q->whereHas('consultations', function ($cq) use ($medecinIdsInService) {
                $cq->whereIn('medecin_id', $medecinIdsInService);
            })
            ->orWhereHas('rendezVous', function ($rq) use ($medecinIdsInService) {
                $rq->whereIn('medecin_id', $medecinIdsInService);
            })
            ->orWhereHas('tickets', function ($tq) use ($medecinIdsInService) {
                $tq->whereIn('medecin_id', $medecinIdsInService);
            })
            ->orWhereHas('transferts', function ($trq) use ($medecinIdsInService, $serviceIds) {
                $trq->whereIn('dest_medecin_id', $medecinIdsInService)
                    ->orWhereIn('source_medecin_id', $medecinIdsInService);
                if (!empty($serviceIds)) {
                    $trq->orWhereIn('dest_service_id', $serviceIds)
                        ->orWhereIn('source_service_id', $serviceIds);
                }
            });

            if (!empty($serviceIds)) {
                $q->orWhereHas('hospitalisations', function ($hq) use ($serviceIds) {
                    $hq->whereIn('service_id', $serviceIds);
                });
            }
        });
    }
}

