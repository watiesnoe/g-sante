<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_maladie', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->constrained()->onDelete('cascade');
            $table->foreignId('maladie_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['confirmé', 'suggéré', 'écarté'])->default('suggéré');
            $table->integer('score')->nullable()->comment('Score de pertinence pour les suggestions');
            $table->string('niveau_confiance')->nullable()->comment('ex: élevé, moyen, faible');
            $table->timestamps(); // ✅ AJOUTÉ
            
            // Éviter les doublons
            $table->unique(['consultation_id', 'maladie_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_maladie');
    }
};