<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Sous MariaDB, la première colonne TIMESTAMP non nulle reçoit « ON UPDATE
 * CURRENT_TIMESTAMP » : clore une occupation de lit écrasait son heure de début.
 * On passe les deux colonnes en DATETIME, sans mise à jour automatique.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE occupations_lit MODIFY debut DATETIME NOT NULL, MODIFY fin DATETIME NULL');
    }

    public function down(): void {}
};
