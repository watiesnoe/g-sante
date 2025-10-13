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
            $table->foreignId('reception_id')->constrained()->onDelete('cascade');
            $table->foreignId('medicament_id')->constrained()->onDelete('cascade');
            $table->integer('quantite_commandee')->default(0);
            $table->integer('quantite_recue')->default(0);
            $table->decimal('prix_unitaire', 10, 2)->default(0);
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
