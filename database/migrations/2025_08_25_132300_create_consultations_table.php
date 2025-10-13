<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('medecin_id')->constrained('users')->onDelete('cascade');
            // protocole_id ajouté : FK vers protocole_traitements (créée avant)
            $table->unsignedBigInteger('protocole_id')->nullable();
            // maladie_id ajouté : FK dénormalisée (maladie principale de la consultation)
            $table->unsignedBigInteger('maladie_id')->nullable();
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
            // grossesse_id déclaré sans FK ici — la contrainte est ajoutée dans la migration maternité
            // qui crée la table grossesses en premier
            $table->unsignedBigInteger('grossesse_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};