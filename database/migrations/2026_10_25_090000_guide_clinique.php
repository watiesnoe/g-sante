<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Guide clinique (maladies → symptômes → traitements), importé du fichier
 * « Maladies, symptômes et traitements » (guide clinique MSF + OMS, Vidal, Dorosz).
 * Les anciennes données restent en base mais sont archivées (actif = false),
 * pour ne pas casser les consultations qui y renvoient.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maladies', function (Blueprint $table) {
            $table->string('chapitre')->nullable()->after('nom');
            $table->text('signes_gravite')->nullable()->after('description');
            $table->text('examens')->nullable()->after('signes_gravite');
            $table->text('traitement')->nullable()->after('examens');
            $table->text('prevention')->nullable()->after('traitement');
            $table->string('source')->nullable()->after('prevention');
            $table->string('source_url')->nullable()->after('source');
            $table->boolean('actif')->default(true)->after('source_url');
        });

        Schema::table('symptomes', function (Blueprint $table) {
            $table->boolean('actif')->default(true)->after('description');
        });

        Schema::table('protocole_traitements', function (Blueprint $table) {
            $table->boolean('actif')->default(true)->after('remarques');
        });

        // « option » : médicament cité par le guide pour une situation précise ; jamais ajouté
        // automatiquement à l'ordonnance, le médecin le choisit
        DB::statement("ALTER TABLE protocole_medicament MODIFY type ENUM('principal','alternatif','adjuvant','relais','assos','option') NOT NULL DEFAULT 'principal'");
        DB::statement('ALTER TABLE protocole_medicament MODIFY posologie TEXT NULL');

        // Une ligne par molécule (DCI) : liste OMS, guide MSF, noms commerciaux, posologies
        Schema::create('dci_references', function (Blueprint $table) {
            $table->id();
            $table->string('dci')->unique();
            $table->string('liste_oms')->nullable();
            $table->string('guide_msf')->nullable();
            $table->text('noms_commerciaux')->nullable();
            $table->text('noms_arretes')->nullable();
            $table->string('dorosz')->nullable();
            $table->text('posologie_reference')->nullable();
            $table->text('contre_indications')->nullable();
            $table->longText('posologie_vidal')->nullable();
            $table->string('fiche_url')->nullable();
            $table->foreignId('fiche_medicament_id')->nullable()->constrained('fiches_medicaments')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('abreviations_cliniques', function (Blueprint $table) {
            $table->id();
            $table->string('abreviation')->unique();
            $table->string('signification');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abreviations_cliniques');
        Schema::dropIfExists('dci_references');
        DB::statement("UPDATE protocole_medicament SET type = 'alternatif' WHERE type = 'option'");
        DB::statement("ALTER TABLE protocole_medicament MODIFY type ENUM('principal','alternatif','adjuvant','relais','assos') NOT NULL DEFAULT 'principal'");
        DB::statement('ALTER TABLE protocole_medicament MODIFY posologie VARCHAR(255) NULL');
        Schema::table('protocole_traitements', fn(Blueprint $table) => $table->dropColumn('actif'));
        Schema::table('symptomes', fn(Blueprint $table) => $table->dropColumn('actif'));
        Schema::table('maladies', fn(Blueprint $table) => $table->dropColumn([
            'chapitre', 'signes_gravite', 'examens', 'traitement', 'prevention', 'source', 'source_url', 'actif',
        ]));
    }
};
