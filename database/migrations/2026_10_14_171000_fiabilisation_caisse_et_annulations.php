<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lot 0 du cahier des charges : annulation au lieu de suppression (RG-01 à RG-04),
 * clôture de caisse justifiée puis validée (EF-06.04, EF-06.05), paramètres
 * d'établissement (section 4.1).
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Mouvements de caisse : annulables, jamais supprimés ──────────────
        Schema::table('caisse_mouvements', function (Blueprint $table) {
            $table->string('mode', 30)->default('especes')->after('montant');
            $table->enum('statut', ['valide', 'annule'])->default('valide')->after('mode');
            $table->foreignId('mouvement_annule_id')->nullable()->after('reference_id')
                ->constrained('caisse_mouvements')->restrictOnDelete()->comment('Mouvement que celui-ci annule (écriture inverse)');
            $table->string('motif_annulation')->nullable();
            $table->foreignId('annule_par')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('annule_at')->nullable();

            // L'historique de caisse ne disparaît plus avec la session ou l'utilisateur
            $table->dropForeign(['caisse_session_id']);
            $table->dropForeign(['user_id']);
            $table->foreign('caisse_session_id')->references('id')->on('caisse_sessions')->restrictOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
        });

        // ── Sessions : ouverte → fermée (écart justifié) → validée ───────────
        DB::statement("ALTER TABLE caisse_sessions MODIFY statut ENUM('ouverte','fermee','validee') NOT NULL DEFAULT 'ouverte'");
        Schema::table('caisse_sessions', function (Blueprint $table) {
            $table->text('justification_ecart')->nullable()->after('ecart');
            $table->foreignId('validee_par')->nullable()->after('closed_at')->constrained('users')->restrictOnDelete();
            $table->timestamp('validee_at')->nullable()->after('validee_par');
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
        });

        // ── Tickets ──────────────────────────────────────────────────────────
        DB::statement("ALTER TABLE tickets MODIFY statut ENUM('en_attente','valide','expire','annule') NOT NULL DEFAULT 'en_attente'");
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('motif_remise')->nullable()->after('part_patient');
            $table->string('motif_annulation')->nullable();
            $table->foreignId('annule_par')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('annule_at')->nullable();
        });

        // ── Paiements d'ordonnance ───────────────────────────────────────────
        DB::statement("ALTER TABLE ordonnance_paiements MODIFY statut ENUM('en_attente','payé','annule') NOT NULL DEFAULT 'en_attente'");
        Schema::table('ordonnance_paiements', function (Blueprint $table) {
            $table->string('motif_annulation')->nullable();
            $table->foreignId('annule_par')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('annule_at')->nullable();
        });

        // ── Paiements fournisseurs ───────────────────────────────────────────
        Schema::table('paiement_commandes', function (Blueprint $table) {
            $table->enum('statut', ['valide', 'annule'])->default('valide')->after('mode');
            $table->string('motif_annulation')->nullable();
            $table->foreignId('annule_par')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('annule_at')->nullable();
        });

        // ── Réceptions ───────────────────────────────────────────────────────
        Schema::table('receptions', function (Blueprint $table) {
            $table->enum('statut', ['validee', 'annulee'])->default('validee')->after('date_reception');
            $table->string('motif_annulation')->nullable();
            $table->foreignId('annule_par')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('annule_at')->nullable();
        });

        // ── Paramètres d'établissement (section 4.1) ─────────────────────────
        Schema::table('etablissements', function (Blueprint $table) {
            $table->json('parametres')->nullable()->after('modules');
        });
    }

    public function down(): void
    {
        Schema::table('etablissements', fn (Blueprint $t) => $t->dropColumn('parametres'));

        foreach (['receptions', 'paiement_commandes', 'ordonnance_paiements', 'tickets'] as $nom) {
            Schema::table($nom, function (Blueprint $table) use ($nom) {
                $table->dropForeign(['annule_par']);
                $colonnes = ['motif_annulation', 'annule_par', 'annule_at'];
                if (in_array($nom, ['receptions', 'paiement_commandes'])) {
                    $colonnes[] = 'statut';
                }
                if ($nom === 'tickets') {
                    $colonnes[] = 'motif_remise';
                }
                $table->dropColumn($colonnes);
            });
        }
        DB::statement("UPDATE ordonnance_paiements SET statut = 'payé' WHERE statut = 'annule'");
        DB::statement("ALTER TABLE ordonnance_paiements MODIFY statut ENUM('en_attente','payé') NOT NULL DEFAULT 'en_attente'");
        DB::statement("UPDATE tickets SET statut = 'expire' WHERE statut = 'annule'");
        DB::statement("ALTER TABLE tickets MODIFY statut ENUM('en_attente','valide','expire') NOT NULL DEFAULT 'en_attente'");

        Schema::table('caisse_sessions', function (Blueprint $table) {
            $table->dropForeign(['validee_par']);
            $table->dropColumn(['justification_ecart', 'validee_par', 'validee_at']);
        });
        DB::statement("UPDATE caisse_sessions SET statut = 'fermee' WHERE statut = 'validee'");
        DB::statement("ALTER TABLE caisse_sessions MODIFY statut ENUM('ouverte','fermee') NOT NULL DEFAULT 'ouverte'");

        Schema::table('caisse_mouvements', function (Blueprint $table) {
            $table->dropForeign(['mouvement_annule_id']);
            $table->dropForeign(['annule_par']);
            $table->dropColumn(['mode', 'statut', 'mouvement_annule_id', 'motif_annulation', 'annule_par', 'annule_at']);
        });
    }
};
