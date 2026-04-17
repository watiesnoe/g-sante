<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('protocole_traitements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maladie_id')->constrained('maladies')->onDelete('cascade');
            $table->string('titre')->nullable();
            $table->text('signes')->nullable();
            $table->text('diagnostics')->nullable();
            $table->text('germes_nourrisson')->nullable();
            $table->text('germes_adulte')->nullable();
            $table->text('traitement_principal')->nullable();
            $table->text('posologie_principale')->nullable();
            $table->text('traitement_alternatif')->nullable();
            $table->text('posologie_alternative')->nullable();
            $table->text('remarques')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocole_traitements');
    }
};
