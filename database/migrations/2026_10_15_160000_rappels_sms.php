<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** EF-03.05 : rappels de rendez-vous par SMS, avec trace de chaque envoi. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rendezvous', function (Blueprint $table) {
            $table->timestamp('rappel_envoye_at')->nullable();
        });

        Schema::create('sms_envois', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->nullable()->constrained()->nullOnDelete();
            $table->string('telephone', 30);
            $table->text('message');
            $table->string('pilote', 20);
            $table->enum('statut', ['envoye', 'echec'])->default('envoye');
            $table->string('erreur')->nullable();
            $table->nullableMorphs('source');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_envois');
        Schema::table('rendezvous', fn(Blueprint $t) => $t->dropColumn('rappel_envoye_at'));
    }
};
