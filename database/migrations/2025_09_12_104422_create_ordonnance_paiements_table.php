<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordonnance_paiements', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('ordonnance_id')->constrained()->onDelete('cascade');
            $table->foreignId('medicament_id')->constrained()->onDelete('cascade');
            $table->integer('quantite')->default(1);
            $table->decimal('prix_total', 10, 2);
            $table->enum('statut', ['en_attente', 'payé'])->default('en_attente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordonnance_paiements');
    }
};
