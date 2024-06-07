<?php

namespace ApproTickets;

use Illuminate\Support\ServiceProvider;

class ApproTicketsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/migrations');

        $this->loadRoutesFrom(__DIR__.'/routes/web.php');

        $this->publishes([
            __DIR__.'/../config/tickets.php' => config_path('tickets.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/approtickets.php', 'approtickets');
    }

    protected function getResources(): array
    {
        return [
            // Aquí pots registrar els recursos de Filament, com ara pàgines, widgets, etc.
            // Example: YourNamespace\Filament\Resources\YourResource::class,
        ];
    }

}
