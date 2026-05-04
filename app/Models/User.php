<?php

namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasUuid,HasRoles;
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'modules_access',
        'prenom',
        'nom',
        'telephone',
        'adresse',
        'service_medical_id',
        'statut',
        'photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'modules_access' => 'array',
        ];
    }

    /**
     * Scope pour les utilisateurs actifs
     */
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    /**
     * Scope pour les utilisateurs inactifs
     */
    public function scopeInactif($query)
    {
        return $query->where('statut', 'inactif');
    }

    /**
     * Scope pour un rôle spécifique
     */
    public function scopeOfRole($query, $role)
    {
        return $query->role($role);
    }

    /**
     * Vérifie si l'utilisateur est actif
     */
    public function isActif()
    {
        return $this->statut === 'actif';
    }

    /**
     * Vérifie si l'utilisateur est inactif
     */
    public function isInactif()
    {
        return $this->statut === 'inactif';
    }

    /**
     * Vérifie si l'utilisateur est suspendu
     */
    public function isSuspendu()
    {
        return $this->statut === 'suspendu';
    }


    /**
     * Vérifie si l'utilisateur a accès à un module spécifique.
     * Les super_admin et admin ont toujours accès à tout, peu importe modules_access.
     * Pour les autres rôles : si modules_access est défini, on l'utilise ; sinon fallback par rôle.
     */
    public function hasModuleAccess($module)
    {
        // Super admin et admin : accès total, toujours
        if ($this->hasRole(['super_admin', 'superadmin', 'admin'])) {
            return true;
        }

        // Pour les autres : utiliser modules_access si défini
        if (is_array($this->modules_access) && !empty($this->modules_access)) {
            return in_array($module, $this->modules_access);
        }

        // Fallback basé sur les rôles Spatie
        if ($this->hasRole('gestionnaire_stock')) {
            return true;
        }
        if ($this->hasRole('secretaire')) {
            return in_array($module, ['patient', 'ticket', 'rendezvous', 'hospitalisation']);
        }
        if ($this->hasRole('medecin')) {
            return in_array($module, ['patient', 'consultation', 'ordonnance', 'examens', 'hospitalisation', 'maternity', 'infectiologie', 'transfert', 'rendezvous']);
        }
        if ($this->hasRole('pharmacien')) {
            return in_array($module, ['ordonnance', 'hospitalisation', 'stock', 'paiements', 'caisse']);
        }
        return false;
    }

    public function service()
    {
        return $this->belongsTo(ServiceMedical::class, 'service_medical_id');
    }

    public function services()
    {
        return $this->belongsToMany(ServiceMedical::class, 'service_user');
    }

    public function prestations()
    {
        return $this->hasManyThrough(
            Prestation::class,
            ServiceMedical::class,
            'id',
            'service_medical_id',
            'id',
            'id'
        );
    }

    /**
     * Get the user's photo URL
     */
    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            return Storage::url($this->photo);
        }
        return asset('assets/media/avatars/avatar0.jpg');
    }
}
