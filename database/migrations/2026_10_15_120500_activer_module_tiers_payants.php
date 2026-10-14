<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Le module Tiers payants (lot 3) est actif par défaut hors cabinet (section 4). */
return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('etablissements')->where('type', '!=', 'cabinet')->get(['id', 'modules']) as $e) {
            $modules = json_decode($e->modules ?? '[]', true) ?: [];
            if (!in_array('tiers_payants', $modules, true)) {
                $modules[] = 'tiers_payants';
                DB::table('etablissements')->where('id', $e->id)->update(['modules' => json_encode($modules)]);
            }
        }
    }

    public function down(): void {}
};
