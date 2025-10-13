<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',       // client/admin
        'prenom',
        'nom',
        'telephone',
        'adresse',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
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
        // récupère toutes les prestations des services auxquels l'utilisateur appartient
        return $this->hasManyThrough(
            Prestation::class,
            ServiceMedical::class,
            'id',                // clé primaire ServiceMedical
            'service_medical_id',// clé étrangère Prestation
            'id',                // clé primaire User inutile ici car relation pivot
            'id'
        );
    }


}
