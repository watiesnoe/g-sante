<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordonnance_medicaments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('ordonnance_id')->constrained()->onDelete('cascade');
            $table->foreignId('medicament_id')->constrained('medicaments')->onDelete('cascade');
            $table->string('posologie');
            $table->integer('duree_jours')->nullable();
            $table->integer('quantite')->default(1);
            $table->integer('qte_vendu')->default(0);
            $table->enum('statut_vente', ['disponible', 'non_disponible'])->default('non_disponible');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordonnance_medicaments');
    }
};
