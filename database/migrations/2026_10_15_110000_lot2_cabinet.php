<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lot 2 — profil cabinet : agenda (EF-03.04), synthèse du dossier (EF-02.05),
 * diagnostic codifié (EF-04.02), consultation signée et addenda (EF-04.05, RG-13),
 * documents à partir de modèles (EF-04.06), fusion de dossiers (EF-02.03).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Agenda : prévu, honoré (réalisé), absent, annulé
        DB::statement("ALTER TABLE rendezvous MODIFY statut ENUM('prevu','en_attente','realise','absent','annule') NOT NULL DEFAULT 'prevu'");

        Schema::table('patients', function (Blueprint $table) {
            $table->text('allergies')->nullable()->after('antecedents');
            $table->foreignId('fusionne_dans_id')->nullable()->constrained('patients')->nullOnDelete()
                ->comment('Dossier conservé quand ce dossier a été fusionné (EF-02.03)');
        });

        // Nomenclature des diagnostics (CIM-10 ou nomenclature nationale), partagée
        Schema::create('diagnostics', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('libelle');
            $table->string('chapitre')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->index('libelle');
        });

        Schema::table('consultations', function (Blueprint $table) {
            $table->foreignId('diagnostic_id')->nullable()->after('diagnostic')->constrained('diagnostics')->nullOnDelete();
            $table->string('type_activite', 30)->default('generale')->after('motif')->comment('generale, pediatrie, cpn…');
            $table->text('conduite_a_tenir')->nullable()->after('notes');
            $table->timestamp('signee_at')->nullable();
            $table->foreignId('signee_par')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::create('consultation_addenda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->constrained()->restrictOnDelete();
            $table->text('contenu');
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('modeles_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30)->comment('certificat, lettre_orientation');
            $table->string('titre');
            $table->text('contenu')->comment('Texte avec champs {patient.nom}, {medecin.nom}…');
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        Schema::create('documents_medicaux', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('etablissement_id')->constrained()->restrictOnDelete();
            $table->foreignId('patient_id')->constrained()->restrictOnDelete();
            $table->foreignId('consultation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('modele_document_id')->nullable()->constrained('modeles_documents')->nullOnDelete();
            $table->string('type', 30);
            $table->string('titre');
            $table->text('contenu');
            $table->string('destinataire')->nullable();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('fusions_patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->constrained()->restrictOnDelete();
            $table->foreignId('patient_conserve_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('patient_fusionne_id')->constrained('patients')->restrictOnDelete();
            $table->string('motif');
            $table->json('deplacements')->comment('Enregistrements rattachés au dossier conservé, par table, pour pouvoir annuler');
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->timestamp('annulee_at')->nullable();
            $table->foreignId('annulee_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fusions_patients');
        Schema::dropIfExists('documents_medicaux');
        Schema::dropIfExists('modeles_documents');
        Schema::dropIfExists('consultation_addenda');
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('diagnostic_id');
            $table->dropConstrainedForeignId('signee_par');
            $table->dropColumn(['type_activite', 'conduite_a_tenir', 'signee_at']);
        });
        Schema::dropIfExists('diagnostics');
        Schema::table('patients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fusionne_dans_id');
            $table->dropColumn('allergies');
        });
        DB::statement("UPDATE rendezvous SET statut = 'annule' WHERE statut = 'absent'");
        DB::statement("ALTER TABLE rendezvous MODIFY statut ENUM('prevu','en_attente','realise','annule') NOT NULL DEFAULT 'prevu'");
    }
};
