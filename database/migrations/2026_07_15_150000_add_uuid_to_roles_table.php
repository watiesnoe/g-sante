<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
        });

        // Générer les UUID pour les rôles existants
        DB::table('roles')->whereNull('uuid')->get()->each(function ($role) {
            DB::table('roles')->where('id', $role->id)->update(['uuid' => Str::uuid()]);
        });

        // Rendre le champ non-nullable
        Schema::table('roles', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
