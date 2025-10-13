<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Réglages de l'établissement qui utilise l'application (une seule ligne) :
 * identité imprimée sur les documents, type de structure et modules activés.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etablissements', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['cabinet', 'clinique', 'csref', 'hopital'])->default('clinique');
            $table->string('nom');
            $table->string('slogan')->nullable();
            $table->string('adresse')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('site_web')->nullable();
            $table->string('logo')->nullable()->comment('Chemin relatif au dossier public');
            $table->json('modules')->nullable()->comment('Modules optionnels activés');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etablissements');
    }
};
