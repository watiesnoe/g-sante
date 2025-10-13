<?php

namespace App\Console\Commands;

use App\Models\EnLigne\Site;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/** Serveur en ligne : enregistre un établissement et lui attribue ses identifiants (SEC-14). */
class EnLigneSite extends Command
{
    protected $signature = 'en-ligne:site {uuid : UUID de l\'établissement (Paramètres → Établissement sur son serveur local)} {nom}
        {--renouveler : Remplacer le secret d\'un établissement déjà enregistré}
        {--desactiver : Couper l\'accès de cet établissement}';

    protected $description = 'Serveur en ligne : enregistrer un établissement et générer ses identifiants de synchronisation';

    public function handle(): int
    {
        if (config('gsante.mode') !== 'en_ligne') {
            $this->error('Cette commande s\'exécute sur le serveur en ligne (GSANTE_MODE=en_ligne).');

            return self::FAILURE;
        }
        if (!Str::isUuid($this->argument('uuid'))) {
            $this->error('UUID invalide.');

            return self::FAILURE;
        }

        $site = Site::where('uuid', $this->argument('uuid'))->first();
        if ($this->option('desactiver')) {
            $site?->update(['actif' => false]);
            $this->info($site ? 'Accès coupé.' : 'Établissement inconnu.');

            return self::SUCCESS;
        }
        if ($site && !$this->option('renouveler')) {
            $this->error('Établissement déjà enregistré. Utilisez --renouveler pour générer un nouveau secret.');

            return self::FAILURE;
        }

        $secret = Str::random(64);
        $site ??= new Site(['uuid' => $this->argument('uuid'), 'cle' => 'GS-' . Str::upper(Str::random(16))]);
        $site->fill(['nom' => $this->argument('nom'), 'secret' => $secret, 'actif' => true])->save();

        $this->info('À saisir dans Paramètres → Établissement → Serveur en ligne, sur le serveur local :');
        $this->line('  Clé    : ' . $site->cle);
        $this->line('  Secret : ' . $secret);
        $this->warn('Le secret ne sera plus affiché. Transmettez-le par un canal sûr.');

        return self::SUCCESS;
    }
}
