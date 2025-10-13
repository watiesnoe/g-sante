<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Les rôles et permissions, communs à tous les établissements, ne se consultent plus que depuis l'Administration. */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }
        $permission = DB::table('permissions')->where('name', 'roles.view')->value('id');
        $superAdmins = DB::table('roles')->whereIn('name', ['super_admin', 'superadmin'])->pluck('id');
        if ($permission) {
            DB::table('role_has_permissions')->where('permission_id', $permission)->whereNotIn('role_id', $superAdmins)->delete();
            $comptesSuperAdmin = DB::table('model_has_roles')->whereIn('role_id', $superAdmins)->pluck('model_id');
            DB::table('model_has_permissions')->where('permission_id', $permission)->whereNotIn('model_id', $comptesSuperAdmin)->delete();
        }
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void {}
};
