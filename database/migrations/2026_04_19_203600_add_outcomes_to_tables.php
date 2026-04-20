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
        // Add death status to patients
        Schema::table('patients', function (Blueprint $table) {
            $table->boolean('est_decede')->default(false)->after('fin_validite_assurance');
            $table->dateTime('date_deces')->nullable()->after('est_decede');
        });

        // Add discharge status to hospitalizations
        Schema::table('hospitalisations', function (Blueprint $table) {
            $table->enum('statut_sortie', [
                'Guérison', 
                'Amélioration', 
                'Décès', 
                'Transfert', 
                'Évasion', 
                'Contre avis médical'
            ])->nullable()->after('etat');
        });

        // Add pregnancy outcome to grossesses
        Schema::table('grossesses', function (Blueprint $table) {
            $table->enum('issue', [
                'Accouchement normal', 
                'Césarienne', 
                'Fausse couche', 
                'Mort-né', 
                'Interruption Médicale de Grossesse (IMG)'
            ])->nullable()->after('statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['est_decede', 'date_deces']);
        });

        Schema::table('hospitalisations', function (Blueprint $table) {
            $table->dropColumn('statut_sortie');
        });

        Schema::table('grossesses', function (Blueprint $table) {
            $table->dropColumn('issue');
        });
    }
};
