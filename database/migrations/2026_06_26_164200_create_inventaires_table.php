<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventaires', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('reference')->unique(); // ex: INV-2026-001
            $table->date('date_inventaire');
            $table->text('observations')->nullable();
            $table->enum('statut', ['brouillon', 'validé', 'annulé'])->default('brouillon');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Responsable
            $table->timestamps();
        });

        Schema::create('inventaire_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventaire_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicament_id')->constrained()->cascadeOnDelete();
            $table->integer('stock_theorique'); // Stock enregistré en base
            $table->integer('stock_reel');      // Stock compté physiquement
            $table->integer('ecart')->storedAs('stock_reel - stock_theorique');
            $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventaire_lignes');
        Schema::dropIfExists('inventaires');
    }
};
