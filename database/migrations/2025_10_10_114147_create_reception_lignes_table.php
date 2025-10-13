<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reception_lignes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('reception_id')->constrained()->onDelete('cascade');
            $table->foreignId('medicament_id')->constrained()->onDelete('cascade');
            $table->integer('quantite_commandee');
            $table->integer('quantite_recue');
            $table->decimal('prix_unitaire', 12, 2)->nullable();
            $table->string('lot')->nullable();
            $table->date('date_peremption')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reception_lignes');
    }
};
