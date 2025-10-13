<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
         Schema::create('paiements', function (Blueprint $table) {
            $table->id();

            // Clés relationnelles
            $table->unsignedBigInteger('hospitalisation_id')->nullable();
            $table->unsignedBigInteger('prescriptions_examens_id')->nullable();

            // Champs paiement
            $table->decimal('montant_total', 12, 2)->default(0);
            $table->decimal('montant_recu', 12, 2)->default(0);
            $table->decimal('montant_restant', 12, 2)->default(0);
            $table->enum('statut', ['en_attente', 'partiel', 'payé'])->default('en_attente');
            $table->string('mode_paiement')->nullable(); // cash, carte, OM, etc.
            $table->date('date_paiement')->nullable();

            $table->timestamps();

            // Contraintes
            $table->foreign('hospitalisation_id')
                ->references('id')->on('hospitalisations')
                ->onDelete('cascade');

            $table->foreign('prescriptions_examens_id')
                ->references('id')->on('prescriptions_examens')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
