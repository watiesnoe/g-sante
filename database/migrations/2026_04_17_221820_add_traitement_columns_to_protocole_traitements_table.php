<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('protocole_traitements', function (Blueprint $table) {
            $table->string('traitement_principal')->nullable()->after('germes_adulte');
            $table->text('posologie_principale')->nullable()->after('traitement_principal');
            $table->string('traitement_alternatif')->nullable()->after('posologie_principale');
            $table->text('posologie_alternative')->nullable()->after('traitement_alternatif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('protocole_traitements', function (Blueprint $table) {
            $table->dropColumn([
                'traitement_principal',
                'posologie_principale',
                'traitement_alternatif',
                'posologie_alternative',
            ]);
        });
    }
};
