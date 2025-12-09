<?php

namespace App\Providers;

use App\Models\Commande;
use App\Observers\CommandeObserver;
use Illuminate\Support\ServiceProvider;
use App\Models\Consultation;
use App\Observers\ConsultationObserver;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        Consultation::observe(ConsultationObserver::class);
        Commande::observe(CommandeObserver::class);
    }
}
