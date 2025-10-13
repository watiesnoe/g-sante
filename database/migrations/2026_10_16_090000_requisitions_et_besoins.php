<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Circuit d'approvisionnement : les services demandent à la pharmacie (réquisition
 * servie par dotation), seule la pharmacie propose des commandes fournisseurs,
 * calculées sur la consommation moyenne mensuelle et validées par un responsable.
 *
 * Reprise possible après un échec partiel (DDL non transactionnel sous MariaDB).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('requisitions')) {
            Schema::create('requisitions', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('etablissement_id')->constrained()->restrictOnDelete();
                $table->string('numero', 40);
                $table->foreignId('service_medical_id')->nullable()->constrained('service_medicals')->nullOnDelete();
                $table->foreignId('magasin_id')->nullable()->constrained('magasins')->nullOnDelete()->comment('Magasin qui sert la réquisition');
                $table->string('motif')->nullable();
                $table->string('statut', 20)->default('soumise')->comment('soumise, servie, partielle, rejetee, annulee');
                $table->foreignId('demandeur_id')->constrained('users')->restrictOnDelete();
                $table->foreignId('servie_par')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('servie_at')->nullable();
                $table->string('motif_rejet')->nullable();
                $table->timestamps();
                $table->unique(['etablissement_id', 'numero']);
            });
        }

        if (!Schema::hasTable('requisition_lignes')) {
            Schema::create('requisition_lignes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('requisition_id')->constrained('requisitions')->cascadeOnDelete();
                $table->foreignId('medicament_id')->constrained()->restrictOnDelete();
                $table->unsignedInteger('quantite_demandee')->comment('En unité de base');
                $table->unsignedInteger('quantite_servie')->default(0);
                $table->timestamps();
            });
        }

        // Sortie de stock vers un service (consommation interne)
        DB::statement("ALTER TABLE mouvements_stock MODIFY type ENUM('entree','sortie','retour','perte','ajustement','transfert','dotation') NOT NULL");

        // Une proposition approuvée peut donner une commande par fournisseur
        if (!Schema::hasColumn('commandes', 'demande_achat_id')) {
            Schema::table('commandes', function (Blueprint $table) {
                $table->foreignId('demande_achat_id')->nullable()->constrained('demandes_achat')->nullOnDelete();
            });
        }
        if (!Schema::hasColumn('demande_achat_lignes', 'fournisseur_id')) {
            Schema::table('demande_achat_lignes', function (Blueprint $table) {
                $table->foreignId('fournisseur_id')->nullable()->constrained('fournisseurs')->nullOnDelete();
            });
        }

        $this->permissions();
    }

    /** Les services réquisitionnent, la pharmacie sert et calcule les besoins. */
    private function permissions(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }
        $permission = config('permission.models.permission');
        $role = config('permission.models.role');

        foreach (['stock.requisition', 'stock.servir', 'stock.besoins'] as $nom) {
            $permission::firstOrCreate(['name' => $nom, 'guard_name' => 'web'], ['module' => 'stock']);
        }

        $attributions = [
            'super_admin'        => ['stock.requisition', 'stock.servir', 'stock.besoins'],
            'admin'              => ['stock.requisition', 'stock.servir', 'stock.besoins'],
            'medecin'            => ['stock.requisition'],
            'infirmier'          => ['stock.requisition'],
            'sage_femme'         => ['stock.requisition'],
            'pharmacien'         => ['stock.servir', 'stock.besoins'],
            'gestionnaire_stock' => ['stock.servir', 'stock.besoins'],
        ];
        foreach ($attributions as $nomRole => $permissions) {
            $role::where('name', $nomRole)->where('guard_name', 'web')->first()?->givePermissionTo($permissions);
        }

        // Un service ne propose plus d'achat : il réquisitionne auprès de la pharmacie
        foreach (['medecin', 'infirmier', 'sage_femme'] as $nomRole) {
            $r = $role::where('name', $nomRole)->where('guard_name', 'web')->first();
            if ($r?->hasPermissionTo('achats.demander')) {
                $r->revokePermissionTo('achats.demander');
            }
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::table('demande_achat_lignes', fn(Blueprint $t) => $t->dropConstrainedForeignId('fournisseur_id'));
        Schema::table('commandes', fn(Blueprint $t) => $t->dropConstrainedForeignId('demande_achat_id'));
        Schema::dropIfExists('requisition_lignes');
        Schema::dropIfExists('requisitions');
    }
};
