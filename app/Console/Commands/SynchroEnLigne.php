<?php

namespace App\Console\Commands;

use App\Models\Etablissement;
use App\Services\EnLigne\SynchroService;
use Illuminate\Console\Command;

/** Serveur local : échange avec le serveur en ligne pour chaque établissement relié (EF-14.01). */
class SynchroEnLigne extends Command
{
    protected $signature = 'synchro:en-ligne {--etablissement= : Code ou identifiant d\'un seul établissement}';

    protected $description = 'Synchronise les établissements reliés avec le serveur en ligne';

    public function handle(SynchroService $synchro): int
    {
        if (config('gsante.mode') !== 'local') {
            $this->error('Cette commande s\'exécute sur un serveur local.');

            return self::FAILURE;
        }

        $etablissements = Etablissement::where('actif', true)->whereNotNull('en_ligne_url')
            ->when($this->option('etablissement'), fn($q, $v) => $q->where(fn($w) => $w->where('code', $v)->orWhere('id', $v)))
            ->get();

        $echecs = 0;
        foreach ($etablissements as $etablissement) {
            // Chaque établissement est traité comme l'établissement courant (cloisonnement)
            app()->instance('etablissement.courant', $etablissement);
            try {
                $journal = $synchro->synchroniser($etablissement);
            } finally {
                Etablissement::oublierCourant();
            }
            $this->line(sprintf('%s : %s — %d envoyé(s), %d reçu(s)%s', $etablissement->nom, $journal->statut, $journal->envoyes, $journal->recus,
                $journal->message ? ' — ' . $journal->message : ''));
            $echecs += $journal->statut === 'ok' ? 0 : 1;
        }

        return $echecs ? self::FAILURE : self::SUCCESS;
    }
}
