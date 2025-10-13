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
        // Schema::create('protocole_medicament', function (Blueprint $table) {
        //     $table->id();
        //     $table->uuid('uuid')->unique();
        //     $table->foreignId('protocole_id')->constrained('protocole_traitements')->cascadeOnDelete();
        //     $table->foreignId('medicament_id')->constrained()->cascadeOnDelete();

        //     // Informations spécifiques à ce protocole
        //     $table->enum('type', ['principal', 'alternatif', 'adjuvant', 'relais', 'assos'])->default('principal');
        //     $table->string('posologie')->nullable(); // ex: "1g toutes les 8h"
        //     $table->string('duree')->nullable();    // ex: "10 jours"
            
        //     // Champs supplémentaires suggérés
        //     $table->string('voie_administration')->nullable(); // IV, IM, SC, per os, etc.
        //     $table->text('indications_specifiques')->nullable(); // Conditions particulières
        //     $table->text('contre_indications')->nullable(); // Contre-indications spécifiques
        //     $table->text('surveillance')->nullable(); // Examens de surveillance
        //     $table->integer('ordre_administration')->nullable(); // Pour l'ordre dans le protocole
        //     $table->boolean('est_actif')->default(true);
        //     $table->string('dci')->nullable(); // Dénomination Commune Internationale
            
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocole_medicament');
    }
};
