<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Les protocoles de traitement sont communs à la plateforme : leurs médicaments
 * renvoient au guide des médicaments (commun), et non plus au stock d'un
 * établissement. Le nom du médicament est conservé tel que dans le protocole.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('protocole_medicament', function (Blueprint $table) {
            if (!Schema::hasColumn('protocole_medicament', 'nom_medicament')) {
                $table->string('nom_medicament')->nullable()->after('protocole_id');
            }
            if (!Schema::hasColumn('protocole_medicament', 'fiche_medicament_id')) {
                $table->foreignId('fiche_medicament_id')->nullable()->after('nom_medicament')->constrained('fiches_medicaments')->nullOnDelete();
            }
        });
        Schema::table('protocole_medicament', function (Blueprint $table) {
            $table->dropForeign(['medicament_id']);
        });
        Schema::table('protocole_medicament', function (Blueprint $table) {
            $table->unsignedBigInteger('medicament_id')->nullable()->change();
            $table->foreign('medicament_id')->references('id')->on('medicaments')->nullOnDelete();
        });

        // Lignes existantes : le nom du produit lié est recopié dans le protocole
        DB::statement('UPDATE protocole_medicament pm JOIN medicaments m ON m.id = pm.medicament_id SET pm.nom_medicament = m.nom WHERE pm.nom_medicament IS NULL');
    }

    public function down(): void
    {
        Schema::table('protocole_medicament', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fiche_medicament_id');
            $table->dropColumn('nom_medicament');
        });
    }
};
