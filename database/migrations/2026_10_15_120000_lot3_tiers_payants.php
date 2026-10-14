<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lot 3 — tiers payants (EF-07.01 à EF-07.05) : conventions datées par
 * établissement, parts calculées ligne par ligne et figées, bordereaux par
 * organisme et suivi des recouvrements.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->constrained()->restrictOnDelete();
            $table->foreignId('assurance_id')->constrained()->restrictOnDelete();
            $table->decimal('taux', 5, 2)->comment('Part de l\'organisme, en %');
            $table->decimal('plafond_par_facture', 12, 2)->nullable()->comment('Montant maximal pris en charge par facture');
            $table->boolean('couvre_medicaments')->default(true);
            $table->enum('couverture_actes', ['toutes', 'liste'])->default('toutes');
            $table->date('date_effet');
            $table->date('date_fin')->nullable();
            $table->timestamps();
            $table->index(['assurance_id', 'date_effet']);
        });

        Schema::create('convention_prestation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convention_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prestation_id')->constrained()->cascadeOnDelete();
            $table->unique(['convention_id', 'prestation_id']);
        });

        // Parts figées ligne par ligne (EF-07.03, EF-07.05)
        Schema::table('ticket_items', function (Blueprint $table) {
            $table->decimal('part_assurance', 12, 2)->default(0)->after('sous_total');
            $table->decimal('part_patient', 12, 2)->default(0)->after('part_assurance');
        });
        DB::statement('UPDATE ticket_items SET part_patient = sous_total WHERE part_patient = 0');

        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('convention_id')->nullable()->after('assurance_id')->constrained()->nullOnDelete();
            $table->string('numero_assure')->nullable()->after('convention_id');
        });

        Schema::table('ordonnance_paiements', function (Blueprint $table) {
            $table->foreignId('assurance_id')->nullable()->after('medicament_id')->constrained()->nullOnDelete();
            $table->foreignId('convention_id')->nullable()->after('assurance_id')->constrained()->nullOnDelete();
            $table->decimal('part_assurance', 12, 2)->default(0)->after('prix_total');
            $table->decimal('part_patient', 12, 2)->default(0)->after('part_assurance');
            $table->foreignId('lot_id')->nullable()->after('medicament_id')->constrained('lots')->nullOnDelete();
        });
        DB::statement('UPDATE ordonnance_paiements SET part_patient = prix_total');

        Schema::create('bordereaux', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('etablissement_id')->constrained()->restrictOnDelete();
            $table->foreignId('assurance_id')->constrained()->restrictOnDelete();
            $table->string('numero', 40);
            $table->date('periode_du');
            $table->date('periode_au');
            $table->decimal('montant', 12, 2);
            $table->decimal('montant_recouvre', 12, 2)->default(0);
            $table->enum('statut', ['emis', 'partiel', 'regle', 'annule'])->default('emis');
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->timestamps();
            $table->unique(['etablissement_id', 'numero']);
        });

        Schema::create('bordereau_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bordereau_id')->constrained('bordereaux')->cascadeOnDelete();
            $table->morphs('source');
            $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date_acte');
            $table->string('libelle');
            $table->decimal('montant', 12, 2);
            $table->unique(['source_type', 'source_id']);
        });

        Schema::create('recouvrements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->constrained()->restrictOnDelete();
            $table->foreignId('bordereau_id')->constrained('bordereaux')->restrictOnDelete();
            $table->decimal('montant', 12, 2);
            $table->date('date_recouvrement');
            $table->string('mode', 30)->default('virement');
            $table->string('reference')->nullable();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->timestamps();
        });

        // Les taux actuels des assurances deviennent la convention de chaque établissement
        $now = now();
        foreach (DB::table('etablissements')->pluck('id') as $etablissementId) {
            foreach (DB::table('assurances')->get(['id', 'taux']) as $assurance) {
                DB::table('conventions')->insert([
                    'etablissement_id' => $etablissementId, 'assurance_id' => $assurance->id, 'taux' => $assurance->taux,
                    'couvre_medicaments' => true, 'couverture_actes' => 'toutes', 'date_effet' => '2000-01-01',
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('recouvrements');
        Schema::dropIfExists('bordereau_lignes');
        Schema::dropIfExists('bordereaux');
        Schema::table('ordonnance_paiements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lot_id');
            $table->dropConstrainedForeignId('convention_id');
            $table->dropConstrainedForeignId('assurance_id');
            $table->dropColumn(['part_assurance', 'part_patient']);
        });
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('convention_id');
            $table->dropColumn('numero_assure');
        });
        Schema::table('ticket_items', fn(Blueprint $t) => $t->dropColumn(['part_assurance', 'part_patient']));
        Schema::dropIfExists('convention_prestation');
        Schema::dropIfExists('conventions');
    }
};
