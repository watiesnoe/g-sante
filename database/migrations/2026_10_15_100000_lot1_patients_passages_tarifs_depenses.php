<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lot 1 : identifiant de dossier patient (EF-02.01), passages et workflows
 * (EF-03.01, EF-03.02, section 7.2), tarifs datés (EF-05.01), numéros de
 * ticket, dépenses avec circuit de validation (EF-12.01).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('numero_dossier', 40)->nullable()->after('uuid');
            $table->date('date_naissance')->nullable()->after('age');
            $table->string('provenance', 30)->nullable()->comment('domicile, cscom, structure, evacuation');
            $table->string('cscom_origine')->nullable();
            $table->unique(['etablissement_id', 'numero_dossier']);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->string('numero', 40)->nullable()->after('uuid');
            $table->unique(['etablissement_id', 'numero']);
        });

        // ── Workflows : transitions autorisées, stockées en base (section 7.2) ──
        Schema::create('workflow_transitions', function (Blueprint $table) {
            $table->id();
            $table->string('objet', 30);
            $table->string('de', 30);
            $table->string('vers', 30);
            $table->string('condition', 60)->nullable()->comment('module:xxx ou parametre:yyy requis pour cette transition');
            $table->timestamps();
            $table->unique(['objet', 'de', 'vers']);
        });

        $now = now();
        $transitions = [
            // Passage : enregistré → (trié) → en consultation → (en examen) → terminé | hospitalisé | référé | sorti
            ['passage', 'enregistre', 'trie', 'parametre:circuit.triage_actif'],
            ['passage', 'enregistre', 'en_consultation', null],
            ['passage', 'trie', 'en_consultation', null],
            ['passage', 'en_consultation', 'en_examen', 'module:examens'],
            ['passage', 'en_examen', 'en_consultation', 'module:examens'],
            ['passage', 'en_consultation', 'termine', null],
            ['passage', 'en_examen', 'termine', 'module:examens'],
            ['passage', 'en_consultation', 'hospitalise', 'module:hospitalisation'],
            ['passage', 'en_consultation', 'refere', null],
            ['passage', 'trie', 'refere', 'parametre:circuit.triage_actif'],
            ['passage', 'enregistre', 'sorti', null],
            ['passage', 'trie', 'sorti', 'parametre:circuit.triage_actif'],
            // Dépense : brouillon → soumise → validée → payée ; soumise → rejetée ; brouillon/soumise → annulée
            ['depense', 'brouillon', 'soumise', null],
            ['depense', 'soumise', 'validee', null],
            ['depense', 'soumise', 'rejetee', null],
            ['depense', 'validee', 'payee', null],
            ['depense', 'brouillon', 'annulee', null],
            ['depense', 'soumise', 'annulee', null],
        ];
        DB::table('workflow_transitions')->insert(array_map(fn($t) => [
            'objet' => $t[0], 'de' => $t[1], 'vers' => $t[2], 'condition' => $t[3], 'created_at' => $now, 'updated_at' => $now,
        ], $transitions));

        Schema::create('passages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('etablissement_id')->constrained()->restrictOnDelete();
            $table->string('numero', 40);
            $table->foreignId('patient_id')->constrained()->restrictOnDelete();
            $table->foreignId('service_medical_id')->nullable()->constrained('service_medicals')->nullOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->string('statut', 30)->default('enregistre');
            $table->unsignedTinyInteger('priorite')->nullable()->comment('Niveau de triage, 1 = le plus urgent');
            $table->string('motif')->nullable();
            $table->timestamp('arrive_at')->useCurrent();
            $table->timestamp('pris_en_charge_at')->nullable();
            $table->timestamp('termine_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->unique(['etablissement_id', 'numero']);
            $table->index(['etablissement_id', 'statut', 'arrive_at']);
        });

        Schema::create('passage_historiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('passage_id')->constrained()->cascadeOnDelete();
            $table->string('de', 30)->nullable();
            $table->string('vers', 30);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::table('consultations', function (Blueprint $table) {
            $table->foreignId('passage_id')->nullable()->after('ticket_id')->constrained('passages')->nullOnDelete();
        });

        // ── Tarifs datés (EF-05.01, RG-07) ──
        Schema::create('tarifs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->constrained()->restrictOnDelete();
            $table->foreignId('prestation_id')->constrained()->cascadeOnDelete();
            $table->decimal('prix', 12, 2);
            $table->date('date_effet');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->index(['prestation_id', 'date_effet']);
        });
        // Le prix actuel de chaque prestation devient son premier tarif
        DB::statement("INSERT INTO tarifs (etablissement_id, prestation_id, prix, date_effet, created_at, updated_at)
            SELECT etablissement_id, id, prix, DATE(created_at), NOW(), NOW() FROM prestations WHERE etablissement_id IS NOT NULL");

        // ── Dépenses (EF-12.01) ──
        Schema::create('categories_depense', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->constrained()->restrictOnDelete();
            $table->string('nom');
            $table->timestamps();
            $table->unique(['etablissement_id', 'nom']);
        });

        Schema::create('depenses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('etablissement_id')->constrained()->restrictOnDelete();
            $table->string('numero', 40);
            $table->foreignId('categorie_depense_id')->constrained('categories_depense')->restrictOnDelete();
            $table->foreignId('service_medical_id')->nullable()->constrained('service_medicals')->nullOnDelete();
            $table->string('objet');
            $table->string('beneficiaire')->nullable();
            $table->decimal('montant', 12, 2);
            $table->date('date_depense');
            $table->string('piece_justificative')->nullable();
            $table->string('statut', 20)->default('brouillon');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validee_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validee_at')->nullable();
            $table->string('motif_rejet')->nullable();
            $table->foreignId('payee_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('payee_at')->nullable();
            $table->string('mode_paiement', 30)->nullable();
            $table->timestamps();
            $table->unique(['etablissement_id', 'numero']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('depenses');
        Schema::dropIfExists('categories_depense');
        Schema::dropIfExists('tarifs');
        Schema::table('consultations', fn(Blueprint $t) => $t->dropConstrainedForeignId('passage_id'));
        Schema::dropIfExists('passage_historiques');
        Schema::dropIfExists('passages');
        Schema::dropIfExists('workflow_transitions');
        Schema::table('tickets', function (Blueprint $t) {
            $t->dropUnique(['etablissement_id', 'numero']);
            $t->dropColumn('numero');
        });
        Schema::table('patients', function (Blueprint $t) {
            $t->dropUnique(['etablissement_id', 'numero_dossier']);
            $t->dropColumn(['numero_dossier', 'date_naissance', 'provenance', 'cscom_origine']);
        });
    }
};
