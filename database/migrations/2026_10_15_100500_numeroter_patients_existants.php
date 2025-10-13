<?php

use App\Models\Etablissement;
use App\Services\NumerotationService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** EF-02.01 : chaque patient existant reçoit son numéro de dossier. */
return new class extends Migration
{
    public function up(): void
    {
        $service = app(NumerotationService::class);
        foreach (Etablissement::all() as $etablissement) {
            DB::table('patients')->where('etablissement_id', $etablissement->id)->whereNull('numero_dossier')->orderBy('id')
                ->each(fn($p) => DB::table('patients')->where('id', $p->id)->update(['numero_dossier' => $service->suivant('patient', $etablissement)]));
        }
    }

    public function down(): void
    {
        // Les numéros attribués sont conservés : ils ont pu être imprimés.
    }
};
