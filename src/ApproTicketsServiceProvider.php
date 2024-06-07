<?php

namespace ApproTickets;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Filament\PanelProvider;
use Filament\Panel;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;


class ApproTicketsServiceProvider extends PanelProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/migrations');
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->publishes([
            __DIR__.'/config/tickets.php' => config_path('tickets.php'),
        ], 'config');

        // Filament
        Model::unguard();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/tickets.php', 'tickets');
    }

    protected function getResources(): array
    {
        return [
            Filament\Resources\ProductResource::class,
        ];
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->discoverResources(in: __DIR__.'/Filament/Resources', for: 'ApproTickets\\Filament\\Resources')
            ->discoverPages(in: __DIR__.'/Filament/Resources', for: 'ApproTickets\\Filament\\Pages')
            ->pages([])
            ->discoverWidgets(in: __DIR__.'/Filament/Widgets', for: 'ApproTickets\\Filament\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->favicon(asset('favicon.png'))
            ->font('Montserrat');
    }

}
