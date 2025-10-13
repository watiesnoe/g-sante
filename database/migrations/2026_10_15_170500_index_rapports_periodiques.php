<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Index d'unicité des versions de rapport, absent si la migration du lot 4 s'était interrompue. */
return new class extends Migration
{
    public function up(): void
    {
        $existe = collect(Schema::getIndexes('rapports_periodiques'))->contains(fn($i) => $i['name'] === 'rapports_periodiques_version_unique');
        if (!$existe) {
            Schema::table('rapports_periodiques', fn(Blueprint $t) => $t->unique(['etablissement_id', 'type', 'periode_du', 'version'], 'rapports_periodiques_version_unique'));
        }
    }

    public function down(): void {}
};
