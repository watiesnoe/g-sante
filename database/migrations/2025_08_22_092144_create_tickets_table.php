<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('description')->nullable();
            $table->integer('total')->default(0);
            $table->foreignId('assurance_id')->nullable()->constrained('assurances')->nullOnDelete();
            $table->decimal('taux_couverture', 5, 2)->nullable()->comment('En pourcentage');
            $table->decimal('part_assurance', 10, 2)->default(0);
            $table->decimal('part_patient', 10, 2)->default(0);
            $table->date('date_validite')->nullable();
            $table->enum('statut', ['en_attente', 'valide', 'expire'])->default('en_attente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
