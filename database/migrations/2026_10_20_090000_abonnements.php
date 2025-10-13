<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Formules et abonnements de la plateforme : la formule fixe les modules et les
 * limites ; l'abonnement relie un établissement à une formule, avec ses échéances.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('formules')) {
            Schema::create('formules', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('nom', 60)->unique();
                $table->string('description')->nullable();
                $table->decimal('prix', 12, 2)->nullable()->comment('Par période ; vide = sur devis');
                $table->string('devise', 3)->default('XOF');
                $table->unsignedSmallInteger('duree_mois')->default(12)->comment('Durée d\'une période payée');
                $table->json('modules')->comment('Modules accordés');
                $table->unsignedInteger('max_utilisateurs')->nullable()->comment('Vide = illimité');
                $table->unsignedInteger('max_lits')->nullable();
                $table->unsignedInteger('max_sms_mois')->nullable();
                $table->json('modules_coupes')->nullable()->comment('Modules coupés après la période de grâce');
                $table->unsignedSmallInteger('jours_grace')->default(7);
                $table->boolean('actif')->default(true)->comment('Proposée aux nouveaux abonnements');
                $table->unsignedSmallInteger('ordre')->default(0);
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('abonnements')) {
            Schema::create('abonnements', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('etablissement_id')->constrained()->restrictOnDelete();
                $table->foreignId('formule_id')->constrained('formules')->restrictOnDelete();
                $table->date('debut');
                $table->date('fin');
                $table->boolean('essai')->default(false);
                $table->string('statut', 20)->default('actif')->comment('actif, grace, restreint, termine');
                $table->string('dernier_rappel', 10)->nullable()->comment('J-15, J-3 : rappels déjà envoyés pour cette échéance');
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index(['etablissement_id', 'statut']);
            });
        }
        if (!Schema::hasTable('paiements_abonnement')) {
            Schema::create('paiements_abonnement', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('numero', 30)->unique()->comment('Numéro du reçu');
                $table->foreignId('abonnement_id')->constrained('abonnements')->restrictOnDelete();
                $table->decimal('montant', 12, 2);
                $table->string('mode', 30)->comment('especes, virement, mobile, cheque');
                $table->string('reference')->nullable()->comment('Référence de la transaction');
                $table->date('paye_le');
                $table->date('periode_debut');
                $table->date('periode_fin');
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamps();
            });
        }
        if (!Schema::hasColumn('etablissements', 'modules_suspendus')) {
            Schema::table('etablissements', function (Blueprint $table) {
                $table->json('modules_suspendus')->nullable()->comment('Coupés pour retard de paiement : réactivés au paiement');
            });
        }
    }

    public function down(): void
    {
        Schema::table('etablissements', fn(Blueprint $t) => $t->dropColumn('modules_suspendus'));
        Schema::dropIfExists('paiements_abonnement');
        Schema::dropIfExists('abonnements');
        Schema::dropIfExists('formules');
    }
};
