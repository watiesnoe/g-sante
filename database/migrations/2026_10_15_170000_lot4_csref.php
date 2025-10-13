<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lot 4 — profil CSRéf : urgences (EF-10.03), accouchements et nouveau-nés
 * (EF-10.04), références et évacuations (EF-11), gratuités (EF-05.03),
 * magasins (EF-08.07), budget (EF-12.03), rapports périodiques (EF-13.03, RG-15).
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Urgences : un passage marqué urgence, avec décision et orientation ──
        if (!Schema::hasColumn('passages', 'urgence')) {
            Schema::table('passages', function (Blueprint $table) {
                $table->boolean('urgence')->default(false)->after('statut');
                $table->string('decision', 30)->nullable()->comment('retour_domicile, observation, hospitalisation, reference, deces');
                $table->string('orientation')->nullable();
            });
        }

        // ── Accouchements et nouveau-nés ──
        if (!Schema::hasTable('accouchements')) {
            Schema::create('accouchements', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('etablissement_id')->constrained()->restrictOnDelete();
                $table->foreignId('grossesse_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('patient_id')->constrained()->restrictOnDelete()->comment('La mère');
                $table->foreignId('hospitalisation_id')->nullable()->constrained()->nullOnDelete();
                $table->dateTime('date_heure');
                $table->string('mode', 30)->comment('voie_basse, ventouse, forceps, cesarienne');
                $table->unsignedTinyInteger('terme_sa')->nullable();
                $table->string('presentation', 30)->nullable();
                $table->string('etat_mere', 30)->default('bon')->comment('bon, complique, deces');
                $table->text('complications')->nullable();
                $table->boolean('delivrance_complete')->default(true);
                $table->unsignedSmallInteger('perte_sang_ml')->nullable();
                $table->foreignId('accoucheur_id')->constrained('users')->restrictOnDelete();
                $table->text('observations')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('nouveau_nes')) {
            Schema::create('nouveau_nes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('accouchement_id')->constrained()->cascadeOnDelete();
                $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete()->comment('Dossier ouvert pour l\'enfant');
                $table->enum('sexe', ['M', 'F', 'I']);
                $table->enum('etat', ['vivant', 'mort_ne_frais', 'mort_ne_macere', 'deces_neonatal'])->default('vivant');
                $table->unsignedSmallInteger('poids_g')->nullable();
                $table->decimal('taille_cm', 4, 1)->nullable();
                $table->decimal('perimetre_cranien_cm', 4, 1)->nullable();
                $table->unsignedTinyInteger('apgar_1')->nullable();
                $table->unsignedTinyInteger('apgar_5')->nullable();
                $table->boolean('reanimation')->default(false);
                $table->text('observations')->nullable();
                $table->timestamps();
            });
        }

        // ── Références et évacuations ──
        if (!Schema::hasTable('references')) {
            Schema::create('references', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('etablissement_id')->constrained()->restrictOnDelete();
                $table->string('numero', 40);
                $table->enum('sens', ['entrante', 'sortante']);
                $table->boolean('evacuation')->default(false)->comment('Transfert urgent avec transport médicalisé');
                $table->foreignId('patient_id')->constrained()->restrictOnDelete();
                $table->foreignId('passage_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('hospitalisation_id')->nullable()->constrained()->nullOnDelete();
                $table->string('structure_origine');
                $table->string('structure_destination');
                $table->text('motif');
                $table->text('soins_realises')->nullable();
                $table->string('transport', 30)->nullable()->comment('ambulance, vehicule_personnel, transport_public, autre');
                $table->string('statut', 30)->default('emise');
                $table->dateTime('date_reference');
                $table->dateTime('recue_at')->nullable();
                $table->text('contre_reference')->nullable();
                $table->dateTime('contre_reference_at')->nullable();
                $table->text('devenir')->nullable();
                $table->foreignId('user_id')->constrained()->restrictOnDelete();
                $table->timestamps();
                $table->unique(['etablissement_id', 'numero']);
            });
        }

        // ── Gratuités ──
        if (!Schema::hasTable('regles_gratuite')) {
            Schema::create('regles_gratuite', function (Blueprint $table) {
                $table->id();
                $table->foreignId('etablissement_id')->constrained()->restrictOnDelete();
                $table->string('nom')->comment('Ex. programme national, à saisir d\'après le texte officiel');
                $table->unsignedSmallInteger('age_max_mois')->nullable();
                $table->enum('genre', ['M', 'F'])->nullable();
                $table->boolean('grossesse_en_cours')->default(false);
                $table->enum('portee_actes', ['aucun', 'tous', 'liste'])->default('liste');
                $table->boolean('couvre_medicaments')->default(false);
                $table->date('date_effet');
                $table->date('date_fin')->nullable();
                $table->boolean('actif')->default(true);
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('regle_gratuite_prestation')) {
            Schema::create('regle_gratuite_prestation', function (Blueprint $table) {
                $table->id();
                $table->foreignId('regle_gratuite_id')->constrained('regles_gratuite')->cascadeOnDelete();
                $table->foreignId('prestation_id')->constrained()->cascadeOnDelete();
                $table->unique(['regle_gratuite_id', 'prestation_id']);
            });
        }
        if (!Schema::hasTable('regle_gratuite_medicament')) {
            Schema::create('regle_gratuite_medicament', function (Blueprint $table) {
                $table->id();
                $table->foreignId('regle_gratuite_id')->constrained('regles_gratuite')->cascadeOnDelete();
                $table->foreignId('medicament_id')->constrained()->cascadeOnDelete();
                $table->unique(['regle_gratuite_id', 'medicament_id']);
            });
        }
        if (!Schema::hasColumn('ticket_items', 'part_gratuite')) {
            Schema::table('ticket_items', function (Blueprint $table) {
                $table->decimal('part_gratuite', 12, 2)->default(0)->after('part_assurance');
                $table->foreignId('regle_gratuite_id')->nullable()->constrained('regles_gratuite')->nullOnDelete();
            });
        }
        if (!Schema::hasColumn('tickets', 'part_gratuite')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->decimal('part_gratuite', 12, 2)->default(0)->after('part_assurance');
                $table->foreignId('passage_id')->nullable()->after('patient_id')->constrained('passages')->nullOnDelete();
            });
        }
        if (!Schema::hasColumn('ordonnance_paiements', 'part_gratuite')) {
            Schema::table('ordonnance_paiements', function (Blueprint $table) {
                $table->decimal('part_gratuite', 12, 2)->default(0)->after('part_assurance');
                $table->foreignId('regle_gratuite_id')->nullable()->constrained('regles_gratuite')->nullOnDelete();
            });
        }

        // ── Magasins ──
        if (!Schema::hasTable('magasins')) {
            Schema::create('magasins', function (Blueprint $table) {
                $table->id();
                $table->foreignId('etablissement_id')->constrained()->restrictOnDelete();
                $table->string('nom');
                $table->enum('type', ['central', 'dispensation'])->default('dispensation');
                $table->boolean('par_defaut')->default(false)->comment('Magasin des réceptions et de la dispensation par défaut');
                $table->boolean('actif')->default(true);
                $table->timestamps();
                $table->unique(['etablissement_id', 'nom']);
            });
        }
        if (!Schema::hasColumn('lots', 'magasin_id')) {
            Schema::table('lots', function (Blueprint $table) {
                $table->foreignId('magasin_id')->nullable()->after('medicament_id')->constrained('magasins')->restrictOnDelete();
            });
        }
        $now = now();
        foreach (DB::table('etablissements')->pluck('id') as $etablissementId) {
            if (DB::table('magasins')->where('etablissement_id', $etablissementId)->exists()) {
                continue;
            }
            $magasinId = DB::table('magasins')->insertGetId(['etablissement_id' => $etablissementId, 'nom' => 'Pharmacie', 'type' => 'dispensation', 'par_defaut' => true, 'actif' => true, 'created_at' => $now, 'updated_at' => $now]);
            DB::table('lots')->where('etablissement_id', $etablissementId)->whereNull('magasin_id')->update(['magasin_id' => $magasinId]);
        }

        // ── Budget ──
        if (!Schema::hasTable('lignes_budgetaires')) {
            Schema::create('lignes_budgetaires', function (Blueprint $table) {
                $table->id();
                $table->foreignId('etablissement_id')->constrained()->restrictOnDelete();
                $table->unsignedSmallInteger('exercice');
                $table->string('code', 30);
                $table->string('libelle');
                $table->decimal('montant_prevu', 14, 2);
                $table->timestamps();
                $table->unique(['etablissement_id', 'exercice', 'code']);
            });
        }
        if (!Schema::hasColumn('depenses', 'ligne_budgetaire_id')) {
            Schema::table('depenses', function (Blueprint $table) {
                $table->foreignId('ligne_budgetaire_id')->nullable()->after('categorie_depense_id')->constrained('lignes_budgetaires')->restrictOnDelete();
            });
        }

        // ── Rapports périodiques ──
        if (!Schema::hasTable('rapports_periodiques')) {
            Schema::create('rapports_periodiques', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('etablissement_id')->constrained()->restrictOnDelete();
                $table->string('type', 30)->default('mensuel_activite');
                $table->date('periode_du');
                $table->date('periode_au');
                $table->unsignedSmallInteger('version')->default(1);
                $table->enum('statut', ['brouillon', 'valide', 'transmis'])->default('brouillon');
                $table->json('donnees');
                $table->foreignId('cree_par')->constrained('users')->restrictOnDelete();
                $table->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('valide_at')->nullable();
                $table->timestamp('transmis_at')->nullable();
                $table->string('motif_nouvelle_version')->nullable();
                $table->timestamps();
                $table->unique(['etablissement_id', 'type', 'periode_du', 'version'], 'rapports_periodiques_version_unique');
            });
        }

        if (!Schema::hasTable('correspondances_dhis2')) {
            Schema::create('correspondances_dhis2', function (Blueprint $table) {
                $table->id();
                $table->foreignId('etablissement_id')->constrained()->cascadeOnDelete();
                $table->string('indicateur', 100)->comment('Clé de l\'indicateur dans le rapport');
                $table->string('data_element', 50)->comment('Identifiant DHIS2 officiel, à fournir par le district');
                $table->string('category_option_combo', 50)->nullable();
                $table->timestamps();
                $table->unique(['etablissement_id', 'indicateur']);
            });
        }

        $transitions = [
            ['reference', 'emise', 'recue'], ['reference', 'recue', 'prise_en_charge'],
            ['reference', 'prise_en_charge', 'contre_reference_envoyee'], ['reference', 'emise', 'annulee'],
            ['rapport', 'brouillon', 'valide'], ['rapport', 'valide', 'transmis'], ['rapport', 'valide', 'brouillon'],
        ];
        DB::table('workflow_transitions')->insertOrIgnore(array_map(fn($t) => [
            'objet' => $t[0], 'de' => $t[1], 'vers' => $t[2], 'condition' => null, 'created_at' => $now, 'updated_at' => $now,
        ], $transitions));
    }

    public function down(): void
    {
        DB::table('workflow_transitions')->whereIn('objet', ['reference', 'rapport'])->delete();
        Schema::dropIfExists('correspondances_dhis2');
        Schema::dropIfExists('rapports_periodiques');
        Schema::table('depenses', fn(Blueprint $t) => $t->dropConstrainedForeignId('ligne_budgetaire_id'));
        Schema::dropIfExists('lignes_budgetaires');
        Schema::table('lots', fn(Blueprint $t) => $t->dropConstrainedForeignId('magasin_id'));
        Schema::dropIfExists('magasins');
        Schema::table('ordonnance_paiements', function (Blueprint $t) {
            $t->dropConstrainedForeignId('regle_gratuite_id');
            $t->dropColumn('part_gratuite');
        });
        Schema::table('tickets', function (Blueprint $t) {
            $t->dropConstrainedForeignId('passage_id');
            $t->dropColumn('part_gratuite');
        });
        Schema::table('ticket_items', function (Blueprint $t) {
            $t->dropConstrainedForeignId('regle_gratuite_id');
            $t->dropColumn('part_gratuite');
        });
        Schema::dropIfExists('regle_gratuite_medicament');
        Schema::dropIfExists('regle_gratuite_prestation');
        Schema::dropIfExists('regles_gratuite');
        Schema::dropIfExists('references');
        Schema::dropIfExists('nouveau_nes');
        Schema::dropIfExists('accouchements');
        Schema::table('passages', fn(Blueprint $t) => $t->dropColumn(['urgence', 'decision', 'orientation']));
    }
};
