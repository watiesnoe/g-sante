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
        Schema::create('transferts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('consultation_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('hospitalisation_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['medecin', 'service', 'hopital_externe']);
            $table->foreignId('source_medecin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('dest_medecin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('source_service_id')->nullable()->constrained('service_medicals')->onDelete('set null');
            $table->foreignId('dest_service_id')->nullable()->constrained('service_medicals')->onDelete('set null');
            $table->string('hopital_destination')->nullable();
            $table->text('motif')->nullable();
            $table->dateTime('date_transfert');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // L'utilisateur qui a fait le transfert
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transferts', function (Blueprint $table) {
            $table->dropForeign(['source_medecin_id']);
            $table->dropColumn('source_medecin_id');
            $table->dropForeign(['dest_medecin_id']);
            $table->dropColumn('dest_medecin_id');
            $table->dropForeign(['source_service_id']);
            $table->dropColumn('source_service_id');
            $table->dropForeign(['dest_service_id']);
            $table->dropColumn('dest_service_id');
        });
        Schema::dropIfExists('transferts');
    }
};
