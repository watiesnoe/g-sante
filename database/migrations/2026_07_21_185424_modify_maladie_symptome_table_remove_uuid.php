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
        if (Schema::hasColumn('maladie_symptome', 'uuid')) {
            Schema::table('maladie_symptome', function (Blueprint $table) {
                $table->dropColumn('uuid');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('maladie_symptome', 'uuid')) {
            Schema::table('maladie_symptome', function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->unique();
            });
        }
    }
};
