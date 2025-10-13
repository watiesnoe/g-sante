<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guide clinique, 2e version du classeur : alertes OMS 2025 (par maladie et par
 * médicament d'un protocole) et fiches du livre Berrebi 2005 (démarche, traitement,
 * ordonnances dont les posologies, lues par OCR, restent « à vérifier »).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maladies', fn(Blueprint $t) => $t->text('alerte_oms')->nullable()->after('prevention'));
        Schema::table('protocole_medicament', fn(Blueprint $t) => $t->text('alerte')->nullable()->after('duree'));

        Schema::create('fiches_berrebi', function (Blueprint $t) {
            $t->id();
            $t->uuid('uuid')->unique();
            $t->string('specialite', 60);
            $t->string('titre')->unique();
            $t->string('pages', 30)->nullable();
            $t->string('pages_lisibles', 40)->nullable();
            $t->text('demarche')->nullable();
            $t->text('medicaments_cites')->nullable();
            $t->text('traitement')->nullable()->comment('Extrait du livre, lu par OCR');
            $t->timestamps();
        });

        Schema::create('fiche_berrebi_maladie', function (Blueprint $t) {
            $t->foreignId('fiche_berrebi_id')->constrained('fiches_berrebi')->cascadeOnDelete();
            $t->foreignId('maladie_id')->constrained('maladies')->cascadeOnDelete();
            $t->primary(['fiche_berrebi_id', 'maladie_id']);
        });

        Schema::create('fiches_berrebi_ordonnances', function (Blueprint $t) {
            $t->id();
            $t->foreignId('fiche_berrebi_id')->constrained('fiches_berrebi')->cascadeOnDelete();
            $t->string('dci', 120)->nullable();
            $t->string('nom_commercial', 120)->nullable();
            $t->text('posologie_ocr')->nullable()->comment('Posologie du livre (2005), lue par OCR : à vérifier');
            $t->string('nom_vidal', 150)->nullable();
            $t->string('statut_2022', 120)->nullable();
            $t->string('liste_oms', 60)->nullable();
            $t->string('guide_msf', 60)->nullable();
            $t->text('posologie_reference')->nullable();
            $t->text('statut_2025')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiches_berrebi_ordonnances');
        Schema::dropIfExists('fiche_berrebi_maladie');
        Schema::dropIfExists('fiches_berrebi');
        Schema::table('protocole_medicament', fn(Blueprint $t) => $t->dropColumn('alerte'));
        Schema::table('maladies', fn(Blueprint $t) => $t->dropColumn('alerte_oms'));
    }
};
