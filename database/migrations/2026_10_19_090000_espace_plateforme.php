<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Plateforme multi-établissements : le super-admin crée les espaces et leurs
 * propriétaires ; un propriétaire administre son seul établissement.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'doit_changer_mot_de_passe')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('doit_changer_mot_de_passe')->default(false)->comment('Mot de passe provisoire remis par la plateforme');
            });
        }
        if (!Schema::hasColumn('etablissements', 'motif_suspension')) {
            Schema::table('etablissements', function (Blueprint $table) {
                $table->string('motif_suspension')->nullable();
            });
        }

        if (Schema::hasTable('roles')) {
            DB::table('roles')->where('name', 'admin')->update(['libelle' => 'Propriétaire / administrateur de l\'établissement']);

            // La gestion des rôles et des établissements revient à la plateforme :
            // les rôles sont communs à tous les établissements
            $role = config('permission.models.role')::where('name', 'admin')->where('guard_name', 'web')->first();
            foreach (\App\Models\User::PERMISSIONS_PLATEFORME as $permission) {
                if ($role && $role->hasPermissionTo($permission)) {
                    $role->revokePermissionTo($permission);
                }
            }
            // Aucun compte hors super-admin ne garde un droit de plateforme accordé directement
            $ids = DB::table('permissions')->whereIn('name', \App\Models\User::PERMISSIONS_PLATEFORME)->pluck('id');
            $superAdmins = DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('roles.name', 'super_admin')->pluck('model_id');
            DB::table('model_has_permissions')->whereIn('permission_id', $ids)->whereNotIn('model_id', $superAdmins)->delete();

            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    public function down(): void
    {
        Schema::table('etablissements', fn(Blueprint $t) => $t->dropColumn('motif_suspension'));
        Schema::table('users', fn(Blueprint $t) => $t->dropColumn('doit_changer_mot_de_passe'));
    }
};
