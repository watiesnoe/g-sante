<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Le module Dépenses (lot 1) fait partie de tous les profils : on l'active partout. */
return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('etablissements')->get(['id', 'modules']) as $e) {
            $modules = json_decode($e->modules ?? '[]', true) ?: [];
            if (!in_array('finances', $modules, true)) {
                $modules[] = 'finances';
                DB::table('etablissements')->where('id', $e->id)->update(['modules' => json_encode($modules)]);
            }
        }
    }

    public function down(): void {}
};
