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
        Schema::create('hospitalisations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->constrained()->onDelete('cascade');
            $table->foreignId('salles_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('lit_id')->nullable()->constrained()->onDelete('set null');
            $table->date('date_entree');
            $table->date('date_sortie')->nullable();
            $table->text('motif')->nullable();
            $table->enum('etat', ['en cours','terminé'])->default('en cours');
            $table->timestamps();
            $table->foreignId('service_id')
                ->constrained('service_medicals') // <- table correcte
                ->onDelete('cascade');
            $table->text('observations')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospitalisations');
    }
};
