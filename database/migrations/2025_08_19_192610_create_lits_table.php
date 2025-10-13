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
        Schema::create('lits', function (Blueprint $table) {
            $table->id();

            // Numéro du lit
            $table->string('numero')->unique();

            // Relation avec la salle
            $table->foreignId('salle_id')->constrained()->onDelete('cascade');

            // Statut : Libre / Occupé / Maintenance
            $table->enum('statut', ['Libre', 'Occupé', 'Maintenance'])->default('Libre');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lits');
    }
};
