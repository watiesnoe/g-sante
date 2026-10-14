<?php

namespace App\Console\Commands;

use App\Models\Etablissement;
use App\Models\RendezVous;
use App\Services\SmsService;
use Illuminate\Console\Command;

/** EF-03.05 : rappel la veille des rendez-vous prévus, pour chaque établissement. */
class RappelsRendezVous extends Command
{
    protected $signature = 'rendezvous:rappels {--date= : jour des rendez-vous (par défaut : demain)}';

    protected $description = 'Envoie un SMS de rappel aux patients ayant un rendez-vous le lendemain';

    public function handle(SmsService $sms): int
    {
        $jour = $this->option('date') ? \Illuminate\Support\Carbon::parse($this->option('date')) : today()->addDay();
        $envoyes = 0;

        foreach (Etablissement::where('actif', true)->get() as $etablissement) {
            app()->instance('etablissement.courant', $etablissement);

            RendezVous::with('patient')->whereDate('date_heure', $jour)->whereIn('statut', ['prevu', 'en_attente'])
                ->whereNull('rappel_envoye_at')->get()
                ->each(function ($rdv) use ($sms, $etablissement, &$envoyes) {
                    if (!$rdv->patient?->telephone) {
                        return;
                    }
                    $message = sprintf('%s : rappel de votre rendez-vous le %s à %s. En cas d\'empêchement, merci de prévenir au %s.',
                        $etablissement->nom, \Illuminate\Support\Carbon::parse($rdv->date_heure)->format('d/m'),
                        \Illuminate\Support\Carbon::parse($rdv->date_heure)->format('H:i'), $etablissement->telephone ?? 'l\'accueil');
                    if ($sms->envoyer($rdv->patient->telephone, $message, $rdv)) {
                        $rdv->update(['rappel_envoye_at' => now()]);
                        $envoyes++;
                    }
                });

            Etablissement::oublierCourant();
        }

        $this->info("{$envoyes} rappel(s) envoyé(s) pour le {$jour->format('d/m/Y')}.");

        return self::SUCCESS;
    }
}
