<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lot 3 — laboratoire et imagerie (EF-09.01 à EF-09.04) :
 * demandé → prélevé ou planifié → réalisé → saisi → validé ; demandé → annulé.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('examens', function (Blueprint $table) {
            $table->enum('type', ['laboratoire', 'imagerie'])->default('laboratoire')->after('nom');
            $table->string('unite', 30)->nullable();
            $table->decimal('valeur_min', 12, 3)->nullable();
            $table->decimal('valeur_max', 12, 3)->nullable();
        });

        DB::statement("ALTER TABLE prescriptions_examens MODIFY statut VARCHAR(20) NOT NULL DEFAULT 'demande'");
        DB::table('prescriptions_examens')->where('statut', 'en_cours')->update(['statut' => 'demande']);
        DB::table('prescriptions_examens')->where('statut', 'realise')->update(['statut' => 'saisi']);

        Schema::table('prescriptions_examens', function (Blueprint $table) {
            $table->foreignId('examen_id')->nullable()->after('consultation_id')->constrained('examens')->nullOnDelete();
            $table->enum('type', ['laboratoire', 'imagerie'])->default('laboratoire')->after('examen');
            $table->timestamp('preleve_at')->nullable();
            $table->foreignId('preleve_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('planifie_le')->nullable();
            $table->timestamp('realise_at')->nullable();
            $table->timestamp('valide_at')->nullable();
            $table->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
            $table->string('motif_annulation')->nullable();
        });

        Schema::table('resultats_examens', function (Blueprint $table) {
            $table->string('valeur', 50)->nullable()->after('resultat');
            $table->string('unite', 30)->nullable()->after('valeur');
            $table->decimal('valeur_min', 12, 3)->nullable()->after('unite');
            $table->decimal('valeur_max', 12, 3)->nullable()->after('valeur_min');
            $table->boolean('anormal')->default(false)->after('valeur_max');
            $table->foreignId('saisi_par')->nullable()->constrained('users')->nullOnDelete();
        });

        $now = now();
        $transitions = [
            ['demande', 'preleve', null], ['demande', 'planifie', null], ['preleve', 'realise', null],
            ['planifie', 'realise', null], ['realise', 'saisi', null], ['saisi', 'valide', null],
            ['demande', 'annule', null], ['preleve', 'annule', null], ['planifie', 'annule', null],
        ];
        DB::table('workflow_transitions')->insert(array_map(fn($t) => [
            'objet' => 'examen', 'de' => $t[0], 'vers' => $t[1], 'condition' => $t[2], 'created_at' => $now, 'updated_at' => $now,
        ], $transitions));
    }

    public function down(): void
    {
        DB::table('workflow_transitions')->where('objet', 'examen')->delete();
        Schema::table('resultats_examens', function (Blueprint $table) {
            $table->dropConstrainedForeignId('saisi_par');
            $table->dropColumn(['valeur', 'unite', 'valeur_min', 'valeur_max', 'anormal']);
        });
        Schema::table('prescriptions_examens', function (Blueprint $table) {
            $table->dropConstrainedForeignId('examen_id');
            $table->dropConstrainedForeignId('preleve_par');
            $table->dropConstrainedForeignId('valide_par');
            $table->dropColumn(['type', 'preleve_at', 'planifie_le', 'realise_at', 'valide_at', 'motif_annulation']);
        });
        DB::table('prescriptions_examens')->whereIn('statut', ['saisi', 'valide'])->update(['statut' => 'realise']);
        DB::table('prescriptions_examens')->whereNotIn('statut', ['realise'])->update(['statut' => 'en_cours']);
        DB::statement("ALTER TABLE prescriptions_examens MODIFY statut ENUM('en_cours','realise') NOT NULL DEFAULT 'en_cours'");
        Schema::table('examens', fn(Blueprint $t) => $t->dropColumn(['type', 'unite', 'valeur_min', 'valeur_max']));
    }
};
