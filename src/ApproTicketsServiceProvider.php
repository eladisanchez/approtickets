<?php

namespace ApproTickets;

use Illuminate\Support\ServiceProvider;

class ApproTicketsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Carregar migracions
        $this->loadMigrationsFrom(__DIR__.'/migrations');

        // Carregar rutes
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
    }

    public function register()
    {
        // Registre serveis, etc.
    }
}