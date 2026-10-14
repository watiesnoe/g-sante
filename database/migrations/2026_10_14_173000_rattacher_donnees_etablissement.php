<?php

use App\Models\Etablissement;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * EF-01.03 (lot 0) : chaque donnée métier porte etablissement_id.
 * Les données existantes sont rattachées à l'établissement actuel. Le filtrage
 * par établissement et l'affectation des utilisateurs viennent au lot 1 (REC-09).
 *
 * Les référentiels partagés (familles, maladies, symptômes, protocoles, types
 * d'examens, assurances) et les lignes de détail (rattachées à leur document)
 * n'ont pas de colonne propre.
 */
return new class extends Migration
{
    public const TABLES = [
        'patients', 'tickets', 'consultations', 'ordonnances', 'ordonnance_paiements',
        'prescriptions_examens', 'resultats_examens', 'hospitalisations', 'paiements',
        'rendezvous', 'transferts', 'grossesses', 'consultations_prenatales', 'certificats',
        'suivi_traitements', 'caisse_sessions', 'caisse_mouvements', 'commandes',
        'paiement_commandes', 'receptions', 'inventaires', 'fournisseurs', 'medicaments',
        'unites', 'prestations', 'service_medicals', 'salles', 'lits', 'emploi_du_temps',
    ];

    public function up(): void
    {
        Schema::table('etablissements', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
            $table->boolean('configure')->default(false)->after('parametres')
                ->comment('Vrai une fois les réglages enregistrés par l\'administrateur');
        });

        // L'établissement actuel : celui déjà configuré, sinon les valeurs historiques
        $id = DB::table('etablissements')->value('id');
        if ($id) {
            DB::table('etablissements')->where('id', $id)->update(['configure' => true]);
        } else {
            $id = DB::table('etablissements')->insertGetId([
                'type' => 'clinique', 'nom' => 'G-SANTÉ', 'slogan' => 'Soins de qualité, notre priorité',
                'adresse' => 'Ségou, Mali', 'telephone' => '(+225) 07 00 00 00 00 / 05 00 00 00 00',
                'email' => 'contact@g-sante.com', 'site_web' => 'www.g-sante.com',
                'modules' => json_encode(array_keys(Etablissement::MODULES)),
                'configure' => false, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        DB::table('etablissements')->whereNull('uuid')->update(['uuid' => DB::raw('UUID()')]);

        foreach (self::TABLES as $nom) {
            Schema::table($nom, function (Blueprint $table) {
                $table->foreignId('etablissement_id')->nullable()->after('id')->constrained('etablissements')->restrictOnDelete();
            });
            DB::table($nom)->update(['etablissement_id' => $id]);
        }

        DB::table('lots')->whereNull('etablissement_id')->update(['etablissement_id' => $id]);
        DB::table('mouvements_stock')->whereNull('etablissement_id')->update(['etablissement_id' => $id]);
    }

    public function down(): void
    {
        foreach (self::TABLES as $nom) {
            Schema::table($nom, function (Blueprint $table) {
                $table->dropConstrainedForeignId('etablissement_id');
            });
        }

        Schema::table('etablissements', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'configure']);
        });
    }
};
