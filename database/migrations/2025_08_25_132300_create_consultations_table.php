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
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();

            // 🔹 Lien avec ticket (nullable car set null au delete)
            $table->foreignId('ticket_id')
                ->nullable() // 👈 important
                ->constrained('tickets')
                ->nullOnDelete(); // équivalent à ->onDelete('set null')
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('medecin_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('date_consultation')->useCurrent();
            $table->string('motif')->nullable();
            $table->text('diagnostic')->nullable();
            $table->text('notes')->nullable();
            $table->float('poids')->nullable();
            $table->float('temperature')->nullable();
            $table->string('tension')->nullable();
            $table->float('taille')->nullable();
            $table->float('imc')->nullable();
            $table->string('groupe_sanguin')->nullable();
            $table->string('adresse_patient')->nullable();
            $table->text('antecedents')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
