<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Stock tracé par lot (RG-08 à RG-11).
 *
 * - lots : chaque arrivage d'un médicament (numéro, péremption) avec sa quantité restante ;
 * - mouvements_stock : journal non modifiable de toutes les entrées et sorties.
 *
 * medicaments.stock et lots.quantite deviennent des totaux tenus à jour
 * uniquement par App\Services\StockService ; la vérité est la somme des mouvements.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lots', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('etablissement_id')->nullable()->constrained('etablissements')->nullOnDelete();
            $table->foreignId('medicament_id')->constrained()->restrictOnDelete();
            $table->string('numero');
            $table->date('date_peremption')->nullable();
            $table->integer('quantite')->default(0)->comment('Somme des mouvements du lot, en unité de base');
            $table->timestamps();

            $table->index(['medicament_id', 'date_peremption']);
        });

        Schema::create('mouvements_stock', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('etablissement_id')->nullable()->constrained('etablissements')->nullOnDelete();
            $table->foreignId('medicament_id')->constrained()->restrictOnDelete();
            $table->foreignId('lot_id')->constrained('lots')->restrictOnDelete();
            $table->enum('type', ['entree', 'sortie', 'retour', 'perte', 'ajustement', 'transfert']);
            $table->integer('quantite')->comment('Positive pour une entrée, négative pour une sortie (unité de base)');
            $table->integer('stock_apres')->comment('Stock total du médicament après le mouvement');
            $table->nullableMorphs('source');
            $table->string('motif')->nullable();
            $table->foreignId('mouvement_annule_id')->nullable()->constrained('mouvements_stock')->restrictOnDelete()
                ->comment('Mouvement que celui-ci annule (écriture inverse)');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['medicament_id', 'created_at']);
        });

        // Reprise de l'existant : le stock actuel devient un lot « INITIAL » sans péremption
        $now = now();
        DB::table('medicaments')->where('stock', '>', 0)->orderBy('id')->chunkById(500, function ($medicaments) use ($now) {
            foreach ($medicaments as $m) {
                $lotId = DB::table('lots')->insertGetId([
                    'uuid' => (string) Str::uuid(), 'medicament_id' => $m->id, 'numero' => 'INITIAL',
                    'date_peremption' => null, 'quantite' => $m->stock, 'created_at' => $now, 'updated_at' => $now,
                ]);
                DB::table('mouvements_stock')->insert([
                    'uuid' => (string) Str::uuid(), 'medicament_id' => $m->id, 'lot_id' => $lotId, 'type' => 'ajustement',
                    'quantite' => $m->stock, 'stock_apres' => $m->stock, 'motif' => 'Reprise du stock existant',
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mouvements_stock');
        Schema::dropIfExists('lots');
    }
};
