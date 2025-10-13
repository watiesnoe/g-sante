<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lot 7 : serveur en ligne et synchronisation (EF-14.01, RG-17, SEC-14).
 *
 * Tables « en_ligne_* » : utilisées par le serveur en ligne, qui ne garde que le
 * minimum (catalogue publié, demandes des patients). Tables « synchro_* » et
 * « demandes_en_ligne » : utilisées par le serveur local. Les deux jeux existent
 * dans chaque installation ; seul celui du mode actif se remplit.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Serveur en ligne ────────────────────────────────────────────
        if (!Schema::hasTable('en_ligne_sites')) {
            Schema::create('en_ligne_sites', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique()->comment('UUID de l\'établissement sur son serveur local');
                $table->string('nom');
                $table->string('type', 20)->nullable();
                $table->string('region')->nullable();
                $table->string('district')->nullable();
                $table->string('telephone', 40)->nullable();
                $table->string('cle', 40)->unique()->comment('Identifiant public de l\'établissement pour la synchronisation');
                $table->text('secret')->comment('Secret partagé, chiffré');
                $table->boolean('actif')->default(true);
                $table->dateTime('derniere_synchro_at')->nullable();
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('en_ligne_medecins')) {
            Schema::create('en_ligne_medecins', function (Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->constrained('en_ligne_sites')->cascadeOnDelete();
                $table->uuid('uuid');
                $table->string('nom');
                $table->string('service')->nullable();
                $table->timestamps();
                $table->unique(['site_id', 'uuid']);
            });
        }
        if (!Schema::hasTable('en_ligne_creneaux')) {
            Schema::create('en_ligne_creneaux', function (Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->constrained('en_ligne_sites')->cascadeOnDelete();
                $table->uuid('medecin_uuid');
                $table->dateTime('debut');
                $table->index(['site_id', 'medecin_uuid', 'debut']);
            });
        }
        if (!Schema::hasTable('en_ligne_demandes')) {
            Schema::create('en_ligne_demandes', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('site_id')->constrained('en_ligne_sites')->restrictOnDelete();
                $table->uuid('medecin_uuid');
                $table->dateTime('creneau');
                $table->string('motif')->nullable();
                $table->string('patient_nom');
                $table->string('patient_prenom');
                $table->string('patient_telephone', 40);
                $table->unsignedBigInteger('patient_compte_id')->nullable()->comment('Compte de l\'espace patient (lot 8)');
                $table->string('statut', 20)->default('en_attente')->comment('en_attente, confirmee, proposee, refusee, annulee, conflit');
                $table->dateTime('creneau_propose')->nullable();
                $table->string('message')->nullable();
                $table->uuid('rdv_uuid')->nullable();
                $table->timestamps();
                $table->index(['site_id', 'statut']);
            });
        }
        if (!Schema::hasTable('en_ligne_evenements')) {
            // File des événements à récupérer par chaque serveur local, dans l'ordre
            Schema::create('en_ligne_evenements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->constrained('en_ligne_sites')->cascadeOnDelete();
                $table->string('type', 40);
                $table->json('donnees');
                $table->timestamp('created_at')->nullable();
                $table->index(['site_id', 'id']);
            });
        }
        if (!Schema::hasTable('en_ligne_recus')) {
            // Événements déjà appliqués : un envoi répété après une coupure n'a pas d'effet
            Schema::create('en_ligne_recus', function (Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->constrained('en_ligne_sites')->cascadeOnDelete();
                $table->uuid('evenement_uuid');
                $table->timestamp('created_at')->nullable();
                $table->unique(['site_id', 'evenement_uuid']);
            });
        }

        // ── Serveur local ───────────────────────────────────────────────
        if (!Schema::hasColumn('etablissements', 'en_ligne_url')) {
            Schema::table('etablissements', function (Blueprint $table) {
                $table->string('en_ligne_url')->nullable()->comment('Adresse du serveur en ligne');
                $table->string('en_ligne_cle', 40)->nullable();
                $table->text('en_ligne_secret')->nullable()->comment('Chiffré');
                $table->unsignedBigInteger('en_ligne_curseur')->default(0)->comment('Dernier événement en ligne récupéré');
                $table->dateTime('en_ligne_synchro_at')->nullable();
                $table->string('en_ligne_erreur')->nullable();
            });
        }
        if (!Schema::hasTable('demandes_en_ligne')) {
            Schema::create('demandes_en_ligne', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique()->comment('Même UUID que sur le serveur en ligne');
                $table->foreignId('etablissement_id')->constrained()->restrictOnDelete();
                $table->foreignId('medecin_id')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('creneau');
                $table->string('motif')->nullable();
                $table->string('patient_nom');
                $table->string('patient_prenom');
                $table->string('patient_telephone', 40);
                $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete();
                $table->string('statut', 20)->default('recue')->comment('recue, confirmee, proposee, refusee, annulee, conflit');
                $table->dateTime('creneau_propose')->nullable();
                $table->string('message')->nullable();
                $table->foreignId('rendezvous_id')->nullable()->constrained('rendezvous')->nullOnDelete();
                $table->foreignId('traite_par')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('traite_at')->nullable();
                $table->timestamps();
                $table->index(['etablissement_id', 'statut']);
            });
        }
        if (!Schema::hasTable('synchro_sortante')) {
            // Événements locaux à envoyer ; conservés jusqu'à l'accusé du serveur en ligne
            Schema::create('synchro_sortante', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('etablissement_id')->constrained()->restrictOnDelete();
                $table->string('type', 40);
                $table->json('donnees');
                $table->dateTime('envoye_at')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->index(['etablissement_id', 'envoye_at']);
            });
        }
        if (!Schema::hasTable('synchro_journal')) {
            Schema::create('synchro_journal', function (Blueprint $table) {
                $table->id();
                $table->foreignId('etablissement_id')->constrained()->cascadeOnDelete();
                $table->dateTime('debut');
                $table->dateTime('fin')->nullable();
                $table->string('statut', 10)->comment('ok, erreur');
                $table->unsignedInteger('envoyes')->default(0);
                $table->unsignedInteger('recus')->default(0);
                $table->text('message')->nullable();
                $table->index(['etablissement_id', 'id']);
            });
        }

        $this->permissions();
    }

    private function permissions(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }
        $permission = config('permission.models.permission');
        $role = config('permission.models.role');
        $permission::firstOrCreate(['name' => 'rendezvous.en_ligne', 'guard_name' => 'web'], ['module' => 'rendezvous']);
        foreach (['super_admin', 'admin', 'secretaire'] as $nom) {
            $role::where('name', $nom)->where('guard_name', 'web')->first()?->givePermissionTo('rendezvous.en_ligne');
        }
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('synchro_journal');
        Schema::dropIfExists('synchro_sortante');
        Schema::dropIfExists('demandes_en_ligne');
        Schema::table('etablissements', fn(Blueprint $t) => $t->dropColumn(['en_ligne_url', 'en_ligne_cle', 'en_ligne_secret', 'en_ligne_curseur', 'en_ligne_synchro_at', 'en_ligne_erreur']));
        Schema::dropIfExists('en_ligne_recus');
        Schema::dropIfExists('en_ligne_evenements');
        Schema::dropIfExists('en_ligne_demandes');
        Schema::dropIfExists('en_ligne_creneaux');
        Schema::dropIfExists('en_ligne_medecins');
        Schema::dropIfExists('en_ligne_sites');
    }
};
