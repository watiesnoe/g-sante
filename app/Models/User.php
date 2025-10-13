<?php

namespace App\Models;

use App\Traits\Audite;use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Audite;
    use HasUuid;
    use HasRoles {
        checkPermissionTo as protected checkPermissionToSpatie;
    }
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

    /**
     * Droits de la plateforme, réservés au super-admin : les rôles et les
     * établissements sont communs à toute l'installation. Un administrateur
     * d'établissement ne peut ni les avoir ni les accorder.
     */
    public const PERMISSIONS_PLATEFORME = [
        'etablissements.gerer', 'roles.view', 'roles.create', 'roles.edit', 'roles.delete', 'roles.assign_permissions', 'parametres.backup',
        // Le journal d'audit couvre toute la plateforme (il n'est pas cloisonné par établissement)
        'audit.view',
    ];

    public function estSuperAdmin(): bool
    {
        return $this->hasRole(['super_admin', 'superadmin']);
    }

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
            'verrouille_jusqua' => 'datetime',
            'doit_changer_mot_de_passe' => 'boolean',
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
     *
     * Ordre de priorité :
     * 1. super_admin / admin → accès total
     * 2. Permissions Spatie (directes OU via rôle) → si au moins une permission
     *    dont le préfixe correspond au module, l'accès est accordé
     * 3. Champ modules_access (JSON) → liste explicite de modules autorisés
     * 4. Fallback par rôle → règles par défaut par type de rôle
     */
    public function hasModuleAccess($module)
    {
        // 0. Module désactivé pour l'établissement : masqué pour tout le monde
        if (!Etablissement::courant()->moduleActif($module)) {
            return false;
        }

        // 1. Super admin et admin : accès total, toujours
        if ($this->hasRole(['super_admin', 'superadmin', 'admin'])) {
            return true;
        }

        // Correspondance module sidebar → préfixes de permissions en base
        // (certains modules sont au singulier dans le sidebar, au pluriel dans les permissions)
        $moduleMap = [
            'patient'         => ['patients'],
            'ticket'          => ['tickets'],
            'consultation'    => ['consultations'],
            'rendezvous'      => ['rendezvous'],
            'ordonnance'      => ['ordonnances'],
            'examens'         => ['examens'],
            'hospitalisation' => ['hospitalisations'],
            'transfert'       => ['transferts'],
            'maternity'       => ['maternity'],
            'infectiologie'   => ['infectiologie'],
            'stock'           => ['stock', 'achats'],
            'paiements'       => ['paiements'],
            'caisse'          => ['caisse'],
            'parametre'       => ['parametres'],
            'dashboard'       => ['dashboard'],
            'users'           => ['users'],
            'roles'           => ['roles'],
            'finances'        => ['depenses'],
            'tiers_payants'   => ['tiers_payants'],
            'rh'              => ['rh'],
            'urgences'        => ['urgences'],
            'bloc'            => ['bloc'],
            'maintenance'     => ['maintenance'],
            'references'      => ['references'],
            'gratuites'       => ['gratuites'],
            'budget'          => ['budget'],
        ];

        // Préfixes à chercher pour ce module
        $prefixes = $moduleMap[$module] ?? [$module];

        // 2. Vérifier via les permissions Spatie (directes + héritées du rôle)
        //    C'est LA seule source de vérité — le seeder (PermissionRoleSeeder) est
        //    l'endroit où configurer les accès par rôle.
        return $this->getAllPermissions()->contains(function ($perm) use ($prefixes) {
            $prefix = explode('.', $perm->name)[0];
            return in_array($prefix, $prefixes);
        });
    }

    /**
     * Toute permission d'un module désactivé pour l'établissement est refusée,
     * quel que soit le rôle. Couvre can(), @can et les abort_unless des contrôleurs.
     */
    public function checkPermissionTo($permission, $guardName = null): bool
    {
        if (is_string($permission) && Etablissement::courant()->permissionDesactivee($permission)) {
            return false;
        }

        return $this->checkPermissionToSpatie($permission, $guardName);
    }

    public function etablissements()
    {
        return $this->belongsToMany(Etablissement::class)->withTimestamps();
    }

    /**
     * Établissements où l'utilisateur peut travailler (EF-01.03) : ceux qui lui sont
     * affectés ; le super-administrateur technique peut ouvrir tous les établissements.
     */
    public function etablissementsAccessibles()
    {
        return $this->hasRole(['super_admin', 'superadmin'])
            ? Etablissement::query()->where('actif', true)->orderBy('nom')->get()
            : $this->etablissements()->where('actif', true)->orderBy('nom')->get();
    }

    /** Utilisateurs affectés à l'établissement courant (listes de médecins, d'agents…). */
    public function scopeDeLEtablissement($query, ?int $etablissementId = null)
    {
        return $query->whereHas('etablissements', fn($q) => $q->where('etablissements.id', $etablissementId ?? Etablissement::courantId()));
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
     * Relation : emploi du temps du médecin
     */
    public function emploiDuTemps()
    {
        return $this->hasMany(EmploiDuTemps::class, 'medecin_id');
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
