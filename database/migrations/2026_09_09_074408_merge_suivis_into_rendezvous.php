<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * La table "suivis" faisait double emploi avec "rendezvous" (mêmes colonnes :
 * consultation_id, patient_id, medecin_id, date_heure, motif, statut), à
 * l'exception d'un champ "resultat". On fusionne les deux concepts : un suivi
 * post-consultation est simplement un rendez-vous dont le résultat est renseigné
 * une fois réalisé. Aucune donnée n'existait dans "suivis" au moment de cette
 * migration (vérifié avant exécution).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rendezvous', function (Blueprint $table) {
            $table->text('resultat')->nullable()->after('motif');
        });

        // Corrige l'incohérence : le formulaire d'édition proposait "en_attente"
        // mais la contrainte enum ne l'autorisait pas.
        DB::statement("ALTER TABLE rendezvous MODIFY statut ENUM('prevu','en_attente','realise','annule') NOT NULL DEFAULT 'prevu'");

        Schema::dropIfExists('suivis');
    }

    public function down(): void
    {
        Schema::table('rendezvous', function (Blueprint $table) {
            $table->dropColumn('resultat');
        });

        DB::statement("ALTER TABLE rendezvous MODIFY statut ENUM('prevu','realise','annule') NOT NULL DEFAULT 'prevu'");

        Schema::create('suivis', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('consultation_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('medecin_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('date_heure');
            $table->string('motif')->nullable();
            $table->text('resultat');
            $table->enum('statut', ['prevu', 'realise', 'termine', 'annulé'])->default('prevu');
            $table->timestamps();
        });
    }
};
