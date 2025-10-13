<?php

namespace App\Models;

use Illuminate\Support\Str;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    /**
     * Le champ utilisé pour le route model binding est l'UUID.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Générer automatiquement un UUID à la création.
     */
    protected static function booted(): void
    {
        static::creating(function (self $role) {
            if (empty($role->uuid)) {
                $role->uuid = (string) Str::uuid();
            }
        });
    }
}
