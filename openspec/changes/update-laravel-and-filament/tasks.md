## 1. Update composer.json constraints

- [ ] 1.1 Bump `illuminate/support` from `^11.0` to `^12.0`
- [ ] 1.2 Bump `filament/filament` from `^3.2` to `^4.0`
- [ ] 1.3 Bump `filament/spatie-laravel-translatable-plugin` from `^3.2` to `^4.0`
- [ ] 1.4 Bump `orchestra/testbench` from `^9.1` to `^10.0`
- [ ] 1.5 Update `inertiajs/inertia-laravel` to `^2.0` (Laravel 12 compatible)
- [ ] 1.6 Verify `laravel/sanctum ^4.0` resolves under Laravel 12 (update to `^5.0` if needed)
- [ ] 1.7 Run `composer update` and resolve any remaining dependency conflicts (check shanmuga/laravel-entrust and mcamara/laravel-localization compatibility)

## 2. Fix Laravel 12 breaking changes

- [ ] 2.1 Audit `src/ApproTicketsServiceProvider.php` for removed Laravel helpers or changed boot lifecycle hooks
- [ ] 2.2 Audit `src/Models/` for deprecated attribute casting syntax and update to Laravel 12 `casts()` method if needed
- [ ] 2.3 Audit `src/Http/Controllers/` for any removed Laravel helpers or changed request/response APIs
- [ ] 2.4 Audit `src/Http/Middleware/` for changes in middleware registration or signature
- [ ] 2.5 Audit `src/Console/Commands/` for any changed Artisan/Command APIs
- [ ] 2.6 Fix any `Route::` or `config()` / `app()` usages that changed in Laravel 12

## 3. Fix Filament 4 breaking changes

- [ ] 3.1 Review the official Filament 4 upgrade guide and list all affected APIs used in this package
- [ ] 3.2 Update `src/Filament/Resources/BookingResource.php` to Filament 4 API (table columns, filters, actions, forms)
- [ ] 3.3 Update `src/Filament/Resources/OrderResource.php` to Filament 4 API
- [ ] 3.4 Update any other Filament resources or pages under `src/Filament/`
- [ ] 3.5 Update panel registration in the service provider if the Filament 4 panel builder API changed
- [ ] 3.6 Update `filament/spatie-laravel-translatable-plugin` usage to version 4 API

## 4. Fix Inertia 2 breaking changes

- [ ] 4.1 Review Inertia Laravel 2.x changelog for breaking changes
- [ ] 4.2 Update middleware registration (HandleInertiaRequests) if the signature changed
- [ ] 4.3 Verify Inertia response sharing and props API is unchanged in all controllers

## 5. Update test suite

- [ ] 5.1 Update `tests/` to use testbench 10 APIs (replace any deprecated TestCase helpers)
- [ ] 5.2 Run `./vendor/bin/pest` and capture all failures
- [ ] 5.3 Fix model/controller test failures caused by Laravel 12 changes
- [ ] 5.4 Fix Filament resource test failures caused by Filament 4 changes
- [ ] 5.5 Confirm full test suite passes with zero failures and zero errors

## 6. Verify and release

- [ ] 6.1 Run `php artisan package:discover` in a workbench Laravel 12 environment and confirm no errors
- [ ] 6.2 Manually verify Filament Booking and Order resources load in the workbench Filament panel
- [ ] 6.3 Update CHANGELOG with upgrade notes (note breaking change: Laravel 11 no longer supported)
- [ ] 6.4 Bump `composer.json` version to next major semver (e.g. 2.0.0)
