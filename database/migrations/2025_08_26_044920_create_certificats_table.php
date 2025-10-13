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
        Schema::create('certificats', function (Blueprint $table) {
           $table->id();
            $table->foreignId('consultation_id')->constrained()->onDelete('cascade');
            $table->date('date')->default(now());
            $table->text('contenu'); // ex: "Inapte au travail 7 jours"
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cerficats');
    }
};
