<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lot 3 — hospitalisation (EF-10.01, EF-10.02, RG-12) : admission directe
 * (urgence, référé) sans consultation préalable, historique d'occupation des lits.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hospitalisations', function (Blueprint $table) {
            $table->foreignId('patient_id')->nullable()->after('consultation_id')->constrained()->restrictOnDelete();
        });
        DB::statement('UPDATE hospitalisations h JOIN consultations c ON c.id = h.consultation_id SET h.patient_id = c.patient_id WHERE h.patient_id IS NULL');
        DB::statement('ALTER TABLE hospitalisations MODIFY consultation_id BIGINT UNSIGNED NULL');

        Schema::create('occupations_lit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospitalisation_id')->constrained()->restrictOnDelete();
            $table->foreignId('lit_id')->constrained()->restrictOnDelete();
            $table->dateTime('debut');
            $table->dateTime('fin')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->index(['lit_id', 'fin']);
        });

        foreach (DB::table('hospitalisations')->where('etat', 'en cours')->whereNotNull('lit_id')->get() as $h) {
            DB::table('occupations_lit')->insert(['hospitalisation_id' => $h->id, 'lit_id' => $h->lit_id, 'debut' => $h->date_entree]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('occupations_lit');
        Schema::table('hospitalisations', fn(Blueprint $t) => $t->dropConstrainedForeignId('patient_id'));
    }
};
