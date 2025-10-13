<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fiches de synthèse « du symptôme à la prescription », rédigées par les soignants
 * de chaque établissement : démarche clinique dans leurs mots, traitements reliés
 * aux fiches du guide des médicaments, et références aux ouvrages (page, chapitre)
 * sans reproduction de leur texte. Validées par un autre médecin que l'auteur.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('fiches_syntheses')) {
            Schema::create('fiches_syntheses', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('etablissement_id')->constrained()->restrictOnDelete();
                $table->string('titre');
                $table->string('specialite', 100)->nullable();
                $table->string('motif')->nullable()->comment('Symptôme ou motif de consultation');
                $table->foreignId('maladie_id')->nullable()->constrained('maladies')->nullOnDelete();
                $table->text('demarche')->nullable()->comment('Interrogatoire, examen clinique');
                $table->text('diagnostics')->nullable()->comment('Diagnostics à évoquer');
                $table->text('examens')->nullable()->comment('Examens complémentaires');
                $table->text('gravite')->nullable()->comment('Signes de gravité, quand référer');
                $table->text('traitement')->nullable()->comment('Stratégie thérapeutique');
                $table->text('suivi')->nullable()->comment('Surveillance, prévention, suivi');
                $table->string('statut', 15)->default('brouillon')->comment('brouillon, a_valider, validee');
                $table->foreignId('auteur_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('valide_at')->nullable();
                $table->timestamps();
                $table->index(['etablissement_id', 'statut']);
            });
        }
        if (!Schema::hasTable('fiche_synthese_medicament')) {
            Schema::create('fiche_synthese_medicament', function (Blueprint $table) {
                $table->id();
                $table->foreignId('fiche_synthese_id')->constrained('fiches_syntheses')->cascadeOnDelete();
                $table->foreignId('fiche_medicament_id')->constrained('fiches_medicaments')->cascadeOnDelete();
                $table->string('usage')->nullable()->comment('Ex. 1re intention, si allergie aux pénicillines');
                $table->unsignedSmallInteger('ordre')->default(0);
                $table->unique(['fiche_synthese_id', 'fiche_medicament_id'], 'fiche_synthese_medicament_unique');
            });
        }
        if (!Schema::hasTable('fiche_synthese_references')) {
            Schema::create('fiche_synthese_references', function (Blueprint $table) {
                $table->id();
                $table->foreignId('fiche_synthese_id')->constrained('fiches_syntheses')->cascadeOnDelete();
                $table->string('ouvrage');
                $table->string('localisation', 150)->nullable()->comment('Page, chapitre');
                $table->string('note')->nullable();
            });
        }

        if (Schema::hasTable('permissions')) {
            $permission = config('permission.models.permission');
            $role = config('permission.models.role');
            foreach (['syntheses.view', 'syntheses.rediger', 'syntheses.valider'] as $nom) {
                $permission::firstOrCreate(['name' => $nom, 'guard_name' => 'web'], ['module' => 'syntheses']);
            }
            $droits = [
                'super_admin' => ['syntheses.view', 'syntheses.rediger', 'syntheses.valider'],
                'admin'       => ['syntheses.view'],
                'medecin'     => ['syntheses.view', 'syntheses.rediger', 'syntheses.valider'],
                'sage_femme'  => ['syntheses.view', 'syntheses.rediger'],
                'pharmacien'  => ['syntheses.view', 'syntheses.rediger'],
                'infirmier'   => ['syntheses.view'],
            ];
            foreach ($droits as $nom => $liste) {
                $role::where('name', $nom)->where('guard_name', 'web')->first()?->givePermissionTo($liste);
            }
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fiche_synthese_references');
        Schema::dropIfExists('fiche_synthese_medicament');
        Schema::dropIfExists('fiches_syntheses');
    }
};
