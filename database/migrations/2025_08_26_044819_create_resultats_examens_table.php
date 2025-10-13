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
        Schema::create('resultats_examens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_examen_id')
                ->constrained('prescriptions_examens') // CORRECT : nom exact de la table
                ->onDelete('cascade');
            $table->text('resultat')->nullable();
            $table->string('fichier')->nullable();
            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analyses');
    }
};
