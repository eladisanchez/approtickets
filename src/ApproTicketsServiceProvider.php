<?php

namespace ApproTickets;


use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
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
use Filament\Facades\Filament;
use ApproTickets\Console\Commands\CleanCartCommand;
use ApproTickets\Console\Commands\TestMailCommand;
use ApproTickets\Console\Commands\SendMailsCommand;
use ApproTickets\Console\Commands\GeneratePdfCommand;
use Illuminate\Console\Scheduling\Schedule;
use Filament\Support\Colors\Color;
use Filament\SpatieLaravelTranslatablePlugin;

class ApproTicketsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/migrations');
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'approtickets');

        $this->publishes([
            __DIR__ . '/resources/views' => resource_path('views/vendor/approtickets'),
        ], 'views');
        $this->publishes([
            __DIR__ . '/config/approtickets.php' => config_path('approtickets.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                CleanCartCommand::class,
                TestMailCommand::class,
                SendMailsCommand::class,
                GeneratePdfCommand::class
            ]);
        }

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('approtickets:clean-cart')->everyMinute();
        });

        // Filament
        Model::unguard();

    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\\Filament\\Resources'
            )
            ->discoverResources(
                in: __DIR__ . '/Filament/Resources',
                for: 'ApproTickets\\Filament\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'App\\Filament\\Pages'
            )
            ->discoverPages(
                in: __DIR__ . '/Filament/Pages',
                for: 'ApproTickets\\Filament\\Pages'
            )
            ->pages([])
            ->discoverWidgets(
                in: app_path('Filament/Widgets'),
                for: 'App\\Filament\\Widgets'
            )
            ->discoverWidgets(
                in: __DIR__ . '/Filament/Widgets',
                for: 'ApproTickets\\Filament\\Widgets'
            )
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
            ->font(config('approtickets.admin.font'))
            ->colors([
                'primary' => Color::hex(config('approtickets.admin.colors.primary')),
            ])
            ->favicon(asset('/favicon.png'))
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->plugins([
                SpatieLaravelTranslatablePlugin::make()->defaultLocales(config('approtickets.locales')),
            ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/approtickets.php', 'approtickets');

        Filament::registerPanel(
            fn(): Panel => $this->panel(Panel::make())
        );
    }

    protected function getResources(): array
    {
        return [
            \ApproTickets\Filament\Resources\ProductResource::class,
        ];
    }

}
