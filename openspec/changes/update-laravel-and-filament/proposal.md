## Why

The package currently targets Laravel 11 and Filament 3.x. Both Laravel 12 and Filament 4.x have been released with new features, improved APIs, and ongoing security support, while Laravel 11 moves to security-only maintenance. Upgrading ensures compatibility with host applications that adopt these new major versions and keeps the package on actively maintained dependencies.

## What Changes

- Bump `illuminate/support` constraint from `^11.0` to `^12.0`
- Bump `filament/filament` constraint from `^3.2` to `^4.0`
- Bump `filament/spatie-laravel-translatable-plugin` constraint from `^3.2` to `^4.0`
- Bump `orchestra/testbench` from `^9.1` to `^10.0` (tracks Laravel 12)
- Bump `laravel/sanctum` from `^4.0` to `^4.0 || ^5.0` (ensure Laravel 12 compatibility)
- Bump `inertiajs/inertia-laravel` from `^1.3` to `^2.0` (Laravel 12 compatible release)
- Audit and fix any breaking API changes in Filament 4 (panels, resources, forms, tables)
- Audit and fix any breaking changes in Laravel 12 (model casting, middleware, routing)
- Update `pestphp/pest` and related plugins if needed for compatibility
- **BREAKING**: Minimum Laravel version raised to 12; drops support for Laravel 11 host apps

## Capabilities

### New Capabilities
<!-- None: this is a dependency upgrade, not a new feature -->

### Modified Capabilities
<!-- No spec-level behavioral changes: upgrade is internal/implementation only -->

## Impact

- **composer.json**: version constraints updated for framework and Filament dependencies
- **Filament resources** (`src/Filament/Resources/`): may require API adjustments for Filament 4 (panel builder, table/form component changes)
- **Service provider** (`src/ApproTicketsServiceProvider.php`): check for Laravel 12 deprecation removals
- **Models** (`src/Models/`): verify casting syntax, attribute definitions, and observer hooks against Laravel 12
- **Controllers / Middleware** (`src/Http/`): review any removed or changed Laravel helpers
- **Test suite** (`tests/`): update testbench usage and any deprecated Pest/PHPUnit APIs
- **CI**: PHP minimum remains 8.2; no PHP version bump needed
