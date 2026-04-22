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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom');
            $table->enum('genre',['M','F']);
            $table->string('telephone')->unique();
            $table->string('ethnie')->nullable();
            $table->integer('age');
            $table->string('adresse')->nullable();
            $table->string('groupe_sanguin')->nullable();
            $table->text('antecedents')->nullable();
            $table->foreignId('assurance_id')->nullable()->constrained('assurances')->nullOnDelete();
            $table->string('numero_assurance')->nullable();
            $table->date('fin_validite_assurance')->nullable();

            $table->boolean('est_decede')->default(false);
            $table->dateTime('date_deces')->nullable();
                    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');

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
