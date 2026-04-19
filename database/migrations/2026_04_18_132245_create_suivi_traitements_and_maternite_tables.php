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
        // 🔹 Table pour le suivi de l'évolution d'un traitement après consultation
        Schema::create('suivi_traitements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->constrained()->onDelete('cascade');
            $table->date('date_suivi');
            $table->enum('evolution', ['Amélioration', 'Stagnation', 'Aggravation', 'Guérison'])->default('Stagnation');
            $table->text('observations')->nullable();
            $table->text('recommandations')->nullable();
            $table->string('temperature')->nullable();
            $table->string('tension')->nullable();
            $table->timestamps();
        });

        // 🔹 Table pour le suivi des grossesses
        Schema::create('grossesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->date('ddr')->comment('Date des dernières règles');
            $table->date('dpa')->comment('Date prévue d\'accouchement');
            $table->integer('parite')->nullable();
            $table->integer('gestite')->nullable();
            $table->text('antecedents_particuliers')->nullable();
            $table->enum('statut', ['En cours', 'Terminée', 'Interrompue'])->default('En cours');
            $table->date('date_fin')->nullable();
            $table->timestamps();
        });

        // 🔹 Table pour les Consultations Prénatales (CPN)
        Schema::create('consultations_prenatales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grossesse_id')->constrained()->onDelete('cascade');
            $table->integer('numero_cpn')->comment('CPN 1, 2, 3, etc.');
            $table->date('date_cpn');
            $table->float('poids')->nullable();
            $table->string('tension')->nullable();
            $table->integer('hauteur_uterine')->nullable()->comment('En cm');
            $table->string('bcf')->nullable()->comment('Bruit du coeur foetal');
            $table->string('mouvement_foetal')->nullable();
            $table->text('oedemes')->nullable();
            $table->text('observations')->nullable();
            $table->text('traitement_recu')->nullable();
            $table->date('prochain_rdv')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations_prenatales');
        Schema::dropIfExists('grossesses');
        Schema::dropIfExists('suivi_traitements');
    }
};
