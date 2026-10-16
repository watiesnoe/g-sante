<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Lot 8 : espace patient sur le serveur en ligne (EF-14.02 à EF-14.08, SEC-11, SEC-12). */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('en_ligne_comptes')) {
            Schema::create('en_ligne_comptes', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('telephone', 20)->unique()->comment('Chiffres seulement, sans indicatif');
                $table->string('nom')->nullable();
                $table->string('prenom')->nullable();
                $table->dateTime('verifie_at')->nullable();
                $table->dateTime('derniere_connexion_at')->nullable();
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('en_ligne_codes')) {
            // Codes de connexion à usage unique ; seule leur empreinte est conservée
            Schema::create('en_ligne_codes', function (Blueprint $table) {
                $table->id();
                $table->string('telephone', 20)->index();
                $table->string('empreinte');
                $table->dateTime('expire_at');
                $table->unsignedTinyInteger('tentatives')->default(0);
                $table->dateTime('utilise_at')->nullable();
                $table->string('ip', 45)->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('en_ligne_sites', 'delai_annulation_heures')) {
            Schema::table('en_ligne_sites', function (Blueprint $table) {
                $table->unsignedSmallInteger('delai_annulation_heures')->default(24)->comment('Annulation ou déplacement possible jusqu\'à ce délai avant le rendez-vous');
            });
        }
        if (!Schema::hasColumn('demandes_en_ligne', 'remplace_uuid')) {
            Schema::table('demandes_en_ligne', function (Blueprint $table) {
                $table->uuid('remplace_uuid')->nullable()->comment('Demande remplacée lors d\'un déplacement par le patient');
            });
        }
        if (!Schema::hasColumn('en_ligne_demandes', 'remplace_uuid')) {
            Schema::table('en_ligne_demandes', function (Blueprint $table) {
                $table->uuid('remplace_uuid')->nullable();
                $table->index('patient_compte_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('en_ligne_demandes', function (Blueprint $t) {
            $t->dropIndex(['patient_compte_id']);
            $t->dropColumn('remplace_uuid');
        });
        Schema::table('demandes_en_ligne', fn(Blueprint $t) => $t->dropColumn('remplace_uuid'));
        Schema::table('en_ligne_sites', fn(Blueprint $t) => $t->dropColumn('delai_annulation_heures'));
        Schema::dropIfExists('en_ligne_codes');
        Schema::dropIfExists('en_ligne_comptes');
    }
};
