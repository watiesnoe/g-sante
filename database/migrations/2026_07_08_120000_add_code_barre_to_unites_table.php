<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute le champ code_barre à la table unites.
     */
    public function up(): void
    {
        Schema::table('unites', function (Blueprint $table) {
            $table->string('code_barre')->nullable()->unique()->after('symbole');
        });
    }

    public function down(): void
    {
        Schema::table('unites', function (Blueprint $table) {
            $table->dropUnique(['code_barre']);
            $table->dropColumn('code_barre');
        });
    }
};
