<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->foreignId('prestation_id')->constrained('prestations')->onDelete('cascade');
            $table->string('service');
            $table->integer('prix_unitaire');
            $table->integer('quantite');
            $table->float('remise')->default(0);
            $table->integer('sous_total');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_items');
    }
};
