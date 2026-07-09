<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La table `unites` stocke maintenant les unités/conditionnements
     * d'un médicament. Chaque médicament peut avoir plusieurs unités
     * avec des facteurs de conversion et des prix différents.
     *
     * Exemple pour l'Amoxicilline :
     *  - Gélule 500mg : facteur=1, prix_achat=50, prix_vente=75
     *  - Boite 12 gélules : facteur=12, prix_achat=550, prix_vente=800
     *  - Flacon sirop : facteur=30, prix_achat=1200, prix_vente=1800
     */
    public function up(): void
    {
        Schema::create('unites', function (Blueprint $table) {
            $table->id();
            $table->string('nom');                              // Ex: "Gélule", "Comprimé", "Boite 12"
            $table->string('symbole')->nullable();              // Ex: "gél", "cp", "bt"
            $table->float('facteur')->default(1);              // Facteur de conversion (1 = unité de base)
            $table->decimal('prix_achat', 12, 2)->default(0); // Prix d'achat
            $table->decimal('prix_vente', 12, 2)->default(0); // Prix de vente
            $table->boolean('is_default')->default(false);     // Unité de référence par défaut
            $table->foreignId('medicament_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unites');
    }
};
