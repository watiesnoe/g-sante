<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * SEC-07 : le journal d'audit n'est modifiable par personne, administrateur
 * compris. Des déclencheurs refusent toute modification ou suppression directe
 * en base, en plus de l'absence d'écran d'édition.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql' && DB::getDriverName() !== 'mariadb') {
            return;
        }

        DB::unprepared("CREATE TRIGGER activity_log_pas_de_modification BEFORE UPDATE ON activity_log FOR EACH ROW
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Journal d''audit : modification interdite (SEC-07)'");
        DB::unprepared("CREATE TRIGGER activity_log_pas_de_suppression BEFORE DELETE ON activity_log FOR EACH ROW
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Journal d''audit : suppression interdite (SEC-07)'");
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS activity_log_pas_de_modification');
        DB::unprepared('DROP TRIGGER IF EXISTS activity_log_pas_de_suppression');
    }
};
