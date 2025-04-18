<?php

namespace ApproTickets\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use ApproTickets\ApproTicketsServiceProvider;
use Mcamara\LaravelLocalization\LaravelLocalizationServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use function Orchestra\Testbench\artisan;
use Database\Seeders\DatabaseSeeder;
use Laravel\Sanctum\SanctumServiceProvider;
use Shanmuga\LaravelEntrust\LaravelEntrustServiceProvider;
use ApproTickets\Models\User;
use ApproTickets\Models\Role;
use Livewire\LivewireServiceProvider;

class TestCase extends Orchestra
{

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['view']->addLocation(__DIR__ . '/../src/resources/views');

    }

    protected function getPackageProviders($app)
    {
        return [
            ApproTicketsServiceProvider::class,
            LaravelLocalizationServiceProvider::class,
            SanctumServiceProvider::class,
            LaravelEntrustServiceProvider::class,
            LivewireServiceProvider::class,
            \Filament\FilamentServiceProvider::class,
            \Filament\Forms\FormsServiceProvider::class,
            \Filament\Tables\TablesServiceProvider::class,
            \Filament\Actions\ActionsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.env', 'testing');
        $app['config']->set('database.default', 'mariadb');
        $app['config']->set('database.connections.mariadb', [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'laravel_testing',
            'username' => 'testing_user',
            'password' => 'testing_password',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);

        $app['config']->set('auth.guards.sanctum', [
            'driver' => 'sanctum',
            'provider' => 'users',
        ]);
        $app['config']->set('auth.providers.users', [
            'driver' => 'eloquent',
            'model' => User::class,
        ]);
        $app['config']->set('entrust.models.role', Role::class);
    }

    protected function defineDatabaseMigrations()
    {
        //$migrationsPath = realpath(__DIR__ . '/../migrations');
        //$this->loadMigrationsFrom($migrationsPath);

        $schemaPath = __DIR__ . '/../src/database/schema/mariadb-schema.sql';

        if (file_exists($schemaPath)) {
            // Llegeix el contingut del fitxer SQL
            $sql = file_get_contents($schemaPath);

            // Executa l'SQL directament
            DB::unprepared($sql);
        } else {
            throw new \Exception("El fitxer d'esquema SQL no existeix: {$schemaPath}");
        }

        $this->seed(DatabaseSeeder::class);

        $this->beforeApplicationDestroyed(function () {
            artisan($this, 'db:wipe', ['--database' => 'mariadb']);
        });
    }

    // protected function defineDatabaseMigrations()
    // {
    //     artisan($this, 'migrate', ['--database' => 'testbench']);
    //     $this->beforeApplicationDestroyed(
    //         fn() => artisan($this, 'migrate:rollback', ['--database' => 'testbench'])
    //     );
    // }
}