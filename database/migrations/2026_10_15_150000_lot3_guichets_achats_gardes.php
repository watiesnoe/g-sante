<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lot 3 : guichets de caisse (EF-06.06), demandes d'achat (EF-12.04),
 * gardes du personnel (EF-12.05).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guichets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->constrained()->restrictOnDelete();
            $table->string('nom');
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->unique(['etablissement_id', 'nom']);
        });

        Schema::table('caisse_sessions', function (Blueprint $table) {
            $table->foreignId('guichet_id')->nullable()->after('user_id')->constrained('guichets')->restrictOnDelete();
        });

        Schema::create('demandes_achat', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('etablissement_id')->constrained()->restrictOnDelete();
            $table->string('numero', 40);
            $table->foreignId('service_medical_id')->nullable()->constrained('service_medicals')->nullOnDelete();
            $table->string('motif')->nullable();
            $table->enum('statut', ['brouillon', 'soumise', 'approuvee', 'rejetee', 'commandee'])->default('brouillon');
            $table->foreignId('demandeur_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('approuvee_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approuvee_at')->nullable();
            $table->string('motif_rejet')->nullable();
            $table->foreignId('commande_id')->nullable()->constrained('commandes')->nullOnDelete();
            $table->timestamps();
            $table->unique(['etablissement_id', 'numero']);
        });

        Schema::create('demande_achat_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_achat_id')->constrained('demandes_achat')->cascadeOnDelete();
            $table->foreignId('medicament_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantite');
            $table->string('observation')->nullable();
        });

        Schema::create('gardes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('service_medical_id')->nullable()->constrained('service_medicals')->nullOnDelete();
            $table->enum('type', ['garde', 'astreinte', 'permanence'])->default('garde');
            $table->dateTime('debut');
            $table->dateTime('fin');
            $table->string('observation')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['etablissement_id', 'debut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gardes');
        Schema::dropIfExists('demande_achat_lignes');
        Schema::dropIfExists('demandes_achat');
        Schema::table('caisse_sessions', fn(Blueprint $t) => $t->dropConstrainedForeignId('guichet_id'));
        Schema::dropIfExists('guichets');
    }
};
