<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Modules du lot 4 actifs par défaut pour les CSRéf et hôpitaux (section 4). */
return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('etablissements')->whereIn('type', ['csref', 'hopital'])->get(['id', 'modules']) as $e) {
            $modules = json_decode($e->modules ?? '[]', true) ?: [];
            $modules = array_values(array_unique(array_merge($modules, ['urgences', 'references', 'gratuites', 'budget', 'statistiques'])));
            DB::table('etablissements')->where('id', $e->id)->update(['modules' => json_encode($modules)]);
        }
    }

    public function down(): void {}
};
