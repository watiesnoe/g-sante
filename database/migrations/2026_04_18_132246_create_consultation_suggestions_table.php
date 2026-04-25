<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Table pour stocker les suggestions IA de diagnostic lors d'une consultation.
     */
    public function up(): void
    {
        Schema::create('consultation_suggestions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('consultation_id')->constrained()->onDelete('cascade');
            // pathologie_id fait référence à la table maladies (même entité)
            $table->foreignId('pathologie_id')->constrained('maladies')->onDelete('cascade');
            $table->float('score')->nullable()->comment('Score de confiance 0-1');
            $table->string('niveau_confiance')->nullable()->comment('élevé, moyen, faible');
            $table->timestamps();

            // Une pathologie ne peut être suggérée qu'une fois par consultation
            $table->unique(['consultation_id', 'pathologie_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultation_suggestions');
    }
};
