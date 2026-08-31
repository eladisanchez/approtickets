## ADDED Requirements

### Requirement: Package resolves under Laravel 12
The package SHALL declare `illuminate/support ^12.0` and all transitive illuminate/* constraints SHALL target Laravel 12, allowing host applications running Laravel 12 to install the package without dependency conflicts.

#### Scenario: Clean install on Laravel 12
- **WHEN** a host application running Laravel 12 runs `composer require approtickets/approtickets`
- **THEN** Composer resolves all dependencies without conflict and the package installs successfully

#### Scenario: No Laravel 11 constraint remains
- **WHEN** the resolved composer.lock is inspected
- **THEN** no illuminate/* package resolves to a version in the 11.x range

### Requirement: Package resolves with Filament 4
The package SHALL declare `filament/filament ^4.0` and `filament/spatie-laravel-translatable-plugin ^4.0`, and all Filament resources, panels, and form/table components SHALL use the Filament 4 API.

#### Scenario: Filament admin panel loads without errors
- **WHEN** a host application boots the Filament panel registered by this package
- **THEN** no class-not-found, method-not-found, or deprecation-fatal errors are thrown

#### Scenario: Booking resource renders in Filament 4
- **WHEN** an admin navigates to the Bookings list in the Filament panel
- **THEN** the table renders with all columns and filters intact, with no PHP errors

#### Scenario: Order resource renders in Filament 4
- **WHEN** an admin navigates to the Orders list in the Filament panel
- **THEN** the table renders with all columns, filters, and actions intact, with no PHP errors

### Requirement: Test suite passes on Laravel 12
The package test suite (Pest) SHALL pass without failures or errors when run against Laravel 12 via `orchestra/testbench ^10.0`.

#### Scenario: All unit tests pass
- **WHEN** `./vendor/bin/pest` is executed in a Laravel 12 environment
- **THEN** all tests pass with zero failures and zero errors

#### Scenario: No deprecated API usage causes test errors
- **WHEN** the test suite runs with deprecation-as-error mode enabled
- **THEN** no test fails due to calling a Laravel 12–removed or Filament 4–removed API

### Requirement: Service provider boots without errors on Laravel 12
The `ApproTicketsServiceProvider` SHALL register all routes, views, migrations, and Filament resources without throwing exceptions on Laravel 12.

#### Scenario: Provider boot on fresh Laravel 12 app
- **WHEN** the package service provider is loaded in a Laravel 12 application
- **THEN** `php artisan package:discover` completes without errors and all registered routes resolve correctly
