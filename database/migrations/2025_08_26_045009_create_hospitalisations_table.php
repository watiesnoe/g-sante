<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospitalisations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('consultation_id')->constrained()->onDelete('cascade');
            $table->foreignId('salles_id')->nullable()->constrained('salles')->onDelete('set null');
            $table->foreignId('lit_id')->nullable()->constrained('lits')->onDelete('set null');
            $table->date('date_entree');
            $table->date('date_sortie')->nullable();
            $table->text('motif')->nullable();
            $table->enum('etat', ['en cours', 'terminé'])->default('en cours');
            $table->foreignId('service_id')->constrained('service_medicals')->onDelete('cascade');
            $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospitalisations');
    }
};
