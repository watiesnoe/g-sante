<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Module Personnel et gardes (lot 3) : actif par défaut hors cabinet (section 4 : « Option » pour le cabinet). */
return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('etablissements')->where('type', '!=', 'cabinet')->get(['id', 'modules']) as $e) {
            $modules = json_decode($e->modules ?? '[]', true) ?: [];
            if (!in_array('rh', $modules, true)) {
                $modules[] = 'rh';
                DB::table('etablissements')->where('id', $e->id)->update(['modules' => json_encode($modules)]);
            }
        }
    }

    public function down(): void {}
};
