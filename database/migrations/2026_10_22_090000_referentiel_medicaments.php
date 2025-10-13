<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Référentiel des médicaments, commun à toute la plateforme : fiches importées
 * d'un guide de référence (source et page conservées). Chaque établissement les
 * fait valider par son médecin ou son pharmacien ; une validation porte sur le
 * contenu relu (empreinte) et tombe si la fiche change. Le catalogue de stock de
 * chaque établissement peut renvoyer à une fiche.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('fiches_medicaments')) {
            Schema::create('fiches_medicaments', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('nom')->comment('Dénomination commune, telle que dans la source');
                $table->string('voie', 30)->comment('oral, injectable, perfusion, vaccin, externe');
                $table->string('categorie', 150)->nullable()->comment('Rubrique de la source');
                $table->boolean('deconseille')->default(false)->comment('Médicament dangereux, obsolète ou inefficace selon la source');
                $table->text('avertissement')->nullable();
                $table->string('mention')->nullable()->comment('Ex. prescription sous contrôle médical');
                $table->text('action_therapeutique')->nullable();
                $table->text('indications')->nullable();
                $table->text('presentation')->nullable();
                $table->longText('posologie')->nullable()->comment('Texte et tableaux tels que dans la source');
                $table->text('duree')->nullable();
                $table->text('precautions')->nullable()->comment('Contre-indications, effets indésirables, précautions');
                $table->text('remarques')->nullable();
                $table->text('conservation')->nullable();
                $table->string('source')->comment('Ouvrage de référence et édition');
                $table->unsignedSmallInteger('page')->nullable();
                $table->string('mise_a_jour_source', 40)->nullable();
                $table->timestamps();
                $table->unique(['source', 'nom', 'voie'], 'fiches_medicaments_source_unique');
                $table->index('nom');
            });
        }
        if (!Schema::hasTable('validations_fiches')) {
            Schema::create('validations_fiches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('fiche_medicament_id')->constrained('fiches_medicaments')->cascadeOnDelete();
                $table->foreignId('etablissement_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('empreinte', 40)->comment('Empreinte du contenu relu');
                $table->text('commentaire')->nullable();
                $table->timestamps();
                $table->unique(['fiche_medicament_id', 'etablissement_id'], 'validations_fiches_unique');
            });
        }
        if (!Schema::hasColumn('medicaments', 'fiche_medicament_id')) {
            Schema::table('medicaments', function (Blueprint $table) {
                $table->foreignId('fiche_medicament_id')->nullable()->constrained('fiches_medicaments')->nullOnDelete();
            });
        }

        if (Schema::hasTable('permissions')) {
            $permission = config('permission.models.permission');
            $role = config('permission.models.role');
            $permission::firstOrCreate(['name' => 'referentiel.view', 'guard_name' => 'web'], ['module' => 'referentiel']);
            $permission::firstOrCreate(['name' => 'referentiel.valider', 'guard_name' => 'web'], ['module' => 'referentiel']);
            foreach (['super_admin', 'admin', 'medecin', 'infirmier', 'sage_femme', 'pharmacien', 'gestionnaire_stock'] as $nom) {
                $role::where('name', $nom)->where('guard_name', 'web')->first()?->givePermissionTo('referentiel.view');
            }
            foreach (['super_admin', 'medecin', 'pharmacien'] as $nom) {
                $role::where('name', $nom)->where('guard_name', 'web')->first()?->givePermissionTo('referentiel.valider');
            }
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    public function down(): void
    {
        Schema::table('medicaments', fn(Blueprint $t) => $t->dropConstrainedForeignId('fiche_medicament_id'));
        Schema::dropIfExists('validations_fiches');
        Schema::dropIfExists('fiches_medicaments');
    }
};
