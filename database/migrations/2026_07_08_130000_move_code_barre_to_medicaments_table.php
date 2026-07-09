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
        Schema::table('unites', function (Blueprint $table) {
            if (Schema::hasColumn('unites', 'code_barre')) {
                $table->dropColumn('code_barre');
            }
        });

        Schema::table('medicaments', function (Blueprint $table) {
            if (!Schema::hasColumn('medicaments', 'code_barre')) {
                $table->string('code_barre')->nullable()->unique()->after('nom');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicaments', function (Blueprint $table) {
            if (Schema::hasColumn('medicaments', 'code_barre')) {
                $table->dropColumn('code_barre');
            }
        });

        Schema::table('unites', function (Blueprint $table) {
            if (!Schema::hasColumn('unites', 'code_barre')) {
                $table->string('code_barre')->nullable()->unique()->after('symbole');
            }
        });
    }
};
