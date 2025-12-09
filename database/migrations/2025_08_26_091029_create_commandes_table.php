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
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('fournisseur_id')->constrained()->cascadeOnDelete();
            $table->date('date_commande');
            $table->enum('statut', ['en_cours','valide','annuler'])->default('en_cours');
            $table->enum('StatutPaiement',['en_cours', 'partielle', 'total'])->default('en_cours');
            $table->decimal('total', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};
