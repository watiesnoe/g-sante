<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('suivis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('medecin_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('date_heure');
            $table->string('motif')->nullable();
            $table->text('resultat');
            $table->enum('statut', ['prevu','realise','termine','annulé'])->default('prévu');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suivis');
    }
};
