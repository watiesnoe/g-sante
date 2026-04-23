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
        Schema::create('protocole_medicament', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('protocole_id')->constrained('protocole_traitements')->cascadeOnDelete();
            $table->foreignId('medicament_id')->constrained()->cascadeOnDelete();

            // Informations spécifiques à ce protocole
            $table->enum('type', ['principal', 'alternatif', 'adjuvant', 'relais', 'assos'])->default('principal');
            $table->string('posologie')->nullable(); // ex: "1g toutes les 8h"
            $table->string('duree')->nullable();    // ex: "10 jours"

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocole_medicament');
    }
};
