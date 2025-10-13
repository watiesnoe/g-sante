<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** EF-10.02 : un lit est libre, occupé, réservé ou indisponible (maintenance). */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE lits MODIFY statut ENUM('Libre','Occupé','Réservé','Maintenance') NOT NULL DEFAULT 'Libre'");
    }

    public function down(): void
    {
        DB::statement("UPDATE lits SET statut = 'Libre' WHERE statut = 'Réservé'");
        DB::statement("ALTER TABLE lits MODIFY statut ENUM('Libre','Occupé','Maintenance') NOT NULL DEFAULT 'Libre'");
    }
};
