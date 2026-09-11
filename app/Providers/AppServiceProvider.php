<?php

namespace App\Providers;

use App\Models\Commande;
use App\Observers\CommandeObserver;
use Illuminate\Support\ServiceProvider;
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
        Commande::observe(CommandeObserver::class);
    }
}
