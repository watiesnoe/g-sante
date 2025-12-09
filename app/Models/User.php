<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'prenom',
        'nom',
        'telephone',
        'adresse',
        'service_medical_id',
        'statut', // Ajout du statut
        'photo',  // Ajout de la photo
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
        return $query->where('role', $role);
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

    public function hasRole($roles)
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }
        return $this->role === $roles;
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
