<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emploi_du_temps', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->nullable();
            $table->foreignId('medecin_id')->constrained('users')->onDelete('cascade');
            $table->tinyInteger('jour_semaine'); // 1=Lundi, 2=Mardi, ..., 7=Dimanche
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->string('service', 100)->nullable(); // Consultation, Garde, etc.
            $table->string('lieu', 150)->nullable();   // Salle / Cabinet
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emploi_du_temps');
    }
};
