## Context

`approtickets` is a Laravel package (type: library) that ships Filament admin resources, Eloquent models, HTTP controllers, and an Inertia frontend. It currently requires `illuminate/support ^11.0` and `filament/filament ^3.2`. Laravel 12 was released in February 2025 and Filament 4.x shortly after; both introduce breaking changes that must be resolved before host applications can upgrade.

## Goals / Non-Goals

**Goals:**
- Raise minimum Laravel version to 12 and update all illuminate/* constraints to `^12.0`
- Upgrade Filament to `^4.0` and the Spatie translatable plugin to match
- Fix all API-level breaking changes so the package tests green against the new versions
- Keep the public-facing package API (service provider, facades, config keys) unchanged

**Non-Goals:**
- Maintaining backwards compatibility with Laravel 11 host apps
- Adopting new Laravel 12 or Filament 4 features beyond what is required to pass CI
- Upgrading unrelated dependencies (redsys/tpv, simplesoftwareio/simple-qrcode, etc.)

## Decisions

### D1 — Drop Laravel 11 support entirely
**Decision**: Raise `illuminate/support` to `^12.0` only; remove the `^11.0` range.  
**Rationale**: A dual `^11.0|^12.0` constraint would require all Filament 4 / Laravel 12 shims to also be backwards-compatible, adding significant complexity with little benefit—Laravel 11 host apps can pin an older release of this package.  
**Alternative considered**: `^11.0|^12.0` with conditional polyfills — rejected due to maintenance burden.

### D2 — Upgrade Filament in-place (no wrapper layer)
**Decision**: Update Filament call sites directly to the 4.x API; do not introduce an adapter or version-detection layer.  
**Rationale**: The package is not a Filament plugin itself; it uses Filament as a host-side peer dependency. A version detection layer would be dead code and obscure intent.  
**Alternative considered**: Abstract Filament behind an interface — rejected as over-engineering for a single target version.

### D3 — Update testbench to ^10.0 and keep Pest ^2
**Decision**: Bump `orchestra/testbench` to `^10.0` (required by Laravel 12) and keep Pest at `^2.x` unless a blocking incompatibility is found.  
**Rationale**: Testbench 10 is the direct Laravel 12 companion. Pest 3 is available but not required; avoid unnecessary churn.

### D4 — Incremental upgrade: composer first, then fix compile errors, then fix tests
**Decision**: Work in three passes — (1) update composer.json and run `composer update`, (2) fix PHP fatal/type errors surfaced by static analysis or class-not-found, (3) run the test suite and fix failures.  
**Rationale**: Separates dependency resolution concerns from code adaptation concerns, making each failure category easier to diagnose.

## Risks / Trade-offs

- **Filament 4 breaking changes scope is unknown until composer update runs** → Mitigation: run `composer update --dry-run` first; consult the official Filament 4 upgrade guide to enumerate affected APIs before touching code.
- **inertiajs/inertia-laravel ^2.0 may introduce middleware or response shape changes** → Mitigation: run the existing integration tests and review the Inertia changelog; update middleware registration in the service provider if needed.
- **Host apps on Laravel 11 will be broken by this release** → Mitigation: tag as a new major semver version (e.g. v2.0.0) so Composer does not auto-resolve it for existing installations.
- **laravel/sanctum compatibility** → Sanctum 4.x already targets Laravel 12; constraint `^4.0` should remain valid. Verify with `composer update` output.

## Migration Plan

1. Update `composer.json` constraints (see What Changes in proposal).
2. Run `composer update` and capture output; resolve any dependency conflicts.
3. Fix PHP-level breaking changes (namespace moves, removed helpers, changed signatures).
4. Fix Filament 4 resource/panel API changes across `src/Filament/`.
5. Run `./vendor/bin/pest` and fix failing tests.
6. Tag a new major release (semver bump) with an upgrade guide in the changelog.

**Rollback**: No database migrations are involved; rollback is reverting `composer.json` to the prior constraints and tagging a patch on the previous major version.

## Open Questions

- Does `shanmuga/laravel-entrust ^6.0` support Laravel 12? Needs verification after `composer update`.
- Does `mcamara/laravel-localization ^2.0` support Laravel 12? Check its releases.
- Are there Filament 4 changes to the panel `boot()` lifecycle that affect `ApproTicketsServiceProvider`?
