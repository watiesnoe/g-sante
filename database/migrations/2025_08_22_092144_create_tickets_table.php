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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->text('description')->nullable();
            $table->integer('total')->default(0);

            // Nouveaux champs
            $table->date('date_validite')->nullable(); // date limite d'utilisation
            $table->enum('statut', ['en_attente','valide', 'expire'])->default('en_attente');

            $table->timestamps();
        });

//        Schema::create('consultations', function (Blueprint $table) {
//            $table->id();
//            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
//            $table->foreignId('medecin_id')->constrained()->onDelete('cascade');
//            $table->dateTime('date_consultation');
//            $table->string('type')->default('générale'); // générale, suivi, urgence…
//            $table->text('motif')->nullable();
//            $table->text('observations')->nullable();
//            $table->foreignId('maladie_id')->nullable()->constrained()->onDelete('set null');
//            $table->string('statut')->default('terminée'); // en attente, en cours, terminée
//            $table->timestamps();
//        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
