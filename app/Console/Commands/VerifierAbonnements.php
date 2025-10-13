<?php

namespace App\Console\Commands;

use App\Services\AbonnementService;
use Illuminate\Console\Command;

/** Passage quotidien : rappels d'échéance, période de grâce, coupure des modules prévus. */
class VerifierAbonnements extends Command
{
    protected $signature = 'abonnements:verifier';

    protected $description = 'Rappels d\'échéance et application des règles de retard de paiement';

    public function handle(AbonnementService $abonnements): int
    {
        $resultat = $abonnements->verifier();
        $this->info("Rappels : {$resultat['rappels']} — en période de grâce : {$resultat['grace']} — modules coupés : {$resultat['restreints']}");

        return self::SUCCESS;
    }
}
