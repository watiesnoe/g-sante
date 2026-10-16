<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ENF-05 : sauvegarde nocturne (RPO 24 h) et purge des anciennes sauvegardes.
// Nécessite la tâche cron : * * * * * cd /chemin/g-sante && php artisan schedule:run >> /dev/null 2>&1
Schedule::command('backup:clean')->dailyAt('01:00');
Schedule::command('backup:run')->dailyAt('01:30');
Schedule::command('backup:monitor')->dailyAt('07:00');

// EF-03.05 : rappel des rendez-vous du lendemain (passerelle définie dans config/sms.php)
Schedule::command('rendezvous:rappels')->dailyAt('18:00');

// EF-14.01 : échange avec le serveur en ligne (sans effet si aucun établissement n'est relié)
if (config('gsante.mode') === 'local') {
    Schedule::command('synchro:en-ligne')->everyFiveMinutes()->withoutOverlapping(10);
}
