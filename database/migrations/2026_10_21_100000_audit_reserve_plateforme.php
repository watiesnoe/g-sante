<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Le journal d'audit couvre toute la plateforme : il n'est plus consultable que par le super-admin. */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }
        $permission = DB::table('permissions')->where('name', 'audit.view')->value('id');
        $superAdmins = DB::table('roles')->whereIn('name', ['super_admin', 'superadmin'])->pluck('id');
        if ($permission) {
            DB::table('role_has_permissions')->where('permission_id', $permission)->whereNotIn('role_id', $superAdmins)->delete();
            $comptes = DB::table('model_has_roles')->whereIn('role_id', $superAdmins)->pluck('model_id');
            DB::table('model_has_permissions')->where('permission_id', $permission)->whereNotIn('model_id', $comptes)->delete();
        }
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void {}
};
