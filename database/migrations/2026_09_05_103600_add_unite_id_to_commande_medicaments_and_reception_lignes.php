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
        Schema::table('commande_medicaments', function (Blueprint $table) {
            if (!Schema::hasColumn('commande_medicaments', 'unite_id')) {
                $table->foreignId('unite_id')->nullable()->after('medicament_id')->constrained('unites')->nullOnDelete();
            }
        });

        Schema::table('reception_lignes', function (Blueprint $table) {
            if (!Schema::hasColumn('reception_lignes', 'unite_id')) {
                $table->foreignId('unite_id')->nullable()->after('medicament_id')->constrained('unites')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commande_medicaments', function (Blueprint $table) {
            if (Schema::hasColumn('commande_medicaments', 'unite_id')) {
                $table->dropForeign(['unite_id']);
                $table->dropColumn('unite_id');
            }
        });

        Schema::table('reception_lignes', function (Blueprint $table) {
            if (Schema::hasColumn('reception_lignes', 'unite_id')) {
                $table->dropForeign(['unite_id']);
                $table->dropColumn('unite_id');
            }
        });
    }
};
