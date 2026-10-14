<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Modules du lot 5 : bloc et maintenance pour les hôpitaux, maintenance pour les CSRéf (section 4). */
return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('etablissements')->whereIn('type', ['csref', 'hopital'])->get(['id', 'type', 'modules']) as $e) {
            $ajout = $e->type === 'hopital' ? ['bloc', 'maintenance'] : ['maintenance'];
            $modules = array_values(array_unique(array_merge(json_decode($e->modules ?? '[]', true) ?: [], $ajout)));
            DB::table('etablissements')->where('id', $e->id)->update(['modules' => json_encode($modules)]);
        }
    }

    public function down(): void {}
};
