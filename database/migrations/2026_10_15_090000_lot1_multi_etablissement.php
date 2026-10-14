<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lot 1 — plusieurs établissements dans la même base (EF-01.01, EF-01.03, SEC-04) :
 * affectation des utilisateurs, unicités par établissement, numérotations.
 */
return new class extends Migration
{
    /** Unicités qui deviennent propres à chaque établissement : [table, colonne]. */
    private const UNICITES = [
        ['patients', 'telephone'],
        ['inventaires', 'reference'],
        ['lits', 'numero'],
        ['commandes', 'reference'],
        ['fournisseurs', 'contact'],
        ['medicaments', 'code_barre'],
    ];

    public function up(): void
    {
        Schema::table('etablissements', function (Blueprint $table) {
            $table->string('code', 20)->nullable()->unique()->after('uuid')->comment('Préfixe des numérotations, ex. CSREF-SEG');
            $table->string('region')->nullable()->after('adresse');
            $table->string('district')->nullable()->after('region');
            $table->boolean('actif')->default(true)->after('configure');
        });
        DB::table('etablissements')->whereNull('code')->update(['code' => DB::raw("CONCAT('ETAB', id)")]);

        Schema::create('etablissement_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['etablissement_id', 'user_id']);
        });

        // Les comptes existants travaillent tous dans l'établissement actuel
        $etablissementId = DB::table('etablissements')->orderBy('id')->value('id');
        if ($etablissementId) {
            $now = now();
            DB::table('users')->orderBy('id')->pluck('id')->chunk(500)->each(function ($ids) use ($etablissementId, $now) {
                DB::table('etablissement_user')->insert($ids->map(fn($id) => [
                    'etablissement_id' => $etablissementId, 'user_id' => $id, 'created_at' => $now, 'updated_at' => $now,
                ])->all());
            });
        }

        foreach (self::UNICITES as [$table, $colonne]) {
            Schema::table($table, function (Blueprint $t) use ($table, $colonne) {
                $t->dropUnique("{$table}_{$colonne}_unique");
                $t->unique(['etablissement_id', $colonne]);
            });
        }

        Schema::create('numerotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->unsignedSmallInteger('annee');
            $table->unsignedInteger('dernier')->default(0);
            $table->timestamps();
            $table->unique(['etablissement_id', 'type', 'annee']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('echecs_connexion')->default(0);
            $table->timestamp('verrouille_jusqua')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', fn(Blueprint $t) => $t->dropColumn(['echecs_connexion', 'verrouille_jusqua']));
        Schema::dropIfExists('numerotations');

        foreach (self::UNICITES as [$table, $colonne]) {
            Schema::table($table, function (Blueprint $t) use ($table, $colonne) {
                $t->dropUnique("{$table}_etablissement_id_{$colonne}_unique");
                $t->unique($colonne);
            });
        }

        Schema::dropIfExists('etablissement_user');
        Schema::table('etablissements', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn(['code', 'region', 'district', 'actif']);
        });
    }
};
