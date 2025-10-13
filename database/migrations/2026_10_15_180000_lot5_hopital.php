<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lot 5 — profil hôpital : service → unité → chambre → lit (section 4.1),
 * bloc opératoire (EF-10.05), maintenance des équipements (EF-12.06).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('unites_soins')) {
            Schema::create('unites_soins', function (Blueprint $table) {
                $table->id();
                $table->foreignId('etablissement_id')->constrained()->restrictOnDelete();
                $table->foreignId('service_medical_id')->constrained('service_medicals')->restrictOnDelete();
                $table->string('nom');
                $table->timestamps();
            });
        }
        if (!Schema::hasColumn('salles', 'unite_soins_id')) {
            Schema::table('salles', function (Blueprint $table) {
                $table->foreignId('unite_soins_id')->nullable()->after('service_medical_id')->constrained('unites_soins')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('interventions')) {
            Schema::create('interventions', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('etablissement_id')->constrained()->restrictOnDelete();
                $table->foreignId('patient_id')->constrained()->restrictOnDelete();
                $table->foreignId('hospitalisation_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('salle_id')->constrained('salles')->restrictOnDelete()->comment('Salle d\'opération');
                $table->foreignId('prestation_id')->nullable()->constrained()->nullOnDelete()->comment('Acte facturé');
                $table->string('libelle');
                $table->enum('urgence', ['programmee', 'urgente'])->default('programmee');
                $table->dateTime('debut_prevu');
                $table->unsignedSmallInteger('duree_prevue_min')->default(60);
                $table->dateTime('debut_reel')->nullable();
                $table->dateTime('fin_reelle')->nullable();
                $table->enum('statut', ['programmee', 'en_cours', 'terminee', 'annulee'])->default('programmee');
                $table->foreignId('chirurgien_id')->constrained('users')->restrictOnDelete();
                $table->foreignId('anesthesiste_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('type_anesthesie', 30)->nullable();
                $table->text('compte_rendu')->nullable();
                $table->string('motif_annulation')->nullable();
                $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
                $table->timestamps();
                $table->index(['salle_id', 'debut_prevu']);
            });
            Schema::create('intervention_equipe', function (Blueprint $table) {
                $table->id();
                $table->foreignId('intervention_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->restrictOnDelete();
                $table->string('role', 30)->comment('aide_operatoire, instrumentiste, infirmier_anesthesiste…');
                $table->unique(['intervention_id', 'user_id']);
            });
            Schema::create('intervention_consommables', function (Blueprint $table) {
                $table->id();
                $table->foreignId('intervention_id')->constrained()->restrictOnDelete();
                $table->foreignId('medicament_id')->constrained()->restrictOnDelete();
                $table->unsignedInteger('quantite');
                $table->decimal('prix_unitaire', 12, 2);
                $table->foreignId('user_id')->constrained()->restrictOnDelete();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('equipements')) {
            Schema::create('equipements', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('etablissement_id')->constrained()->restrictOnDelete();
                $table->string('nom');
                $table->string('categorie')->nullable();
                $table->string('numero_serie')->nullable();
                $table->foreignId('service_medical_id')->nullable()->constrained('service_medicals')->nullOnDelete();
                $table->enum('statut', ['en_service', 'en_panne', 'hors_service'])->default('en_service');
                $table->date('date_acquisition')->nullable();
                $table->unsignedSmallInteger('periodicite_jours')->nullable()->comment('Maintenance préventive');
                $table->date('prochaine_maintenance')->nullable();
                $table->timestamps();
            });
            Schema::create('maintenances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('etablissement_id')->constrained()->restrictOnDelete();
                $table->foreignId('equipement_id')->constrained()->restrictOnDelete();
                $table->enum('type', ['panne', 'preventive']);
                $table->text('description');
                $table->enum('statut', ['ouverte', 'resolue'])->default('ouverte');
                $table->foreignId('signale_par')->constrained('users')->restrictOnDelete();
                $table->text('intervention_realisee')->nullable();
                $table->decimal('cout', 12, 2)->nullable();
                $table->foreignId('resolue_par')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('resolue_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenances');
        Schema::dropIfExists('equipements');
        Schema::dropIfExists('intervention_consommables');
        Schema::dropIfExists('intervention_equipe');
        Schema::dropIfExists('interventions');
        if (Schema::hasColumn('salles', 'unite_soins_id')) {
            Schema::table('salles', fn(Blueprint $t) => $t->dropConstrainedForeignId('unite_soins_id'));
        }
        Schema::dropIfExists('unites_soins');
    }
};
