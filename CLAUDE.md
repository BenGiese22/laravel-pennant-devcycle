# Laravel Pennant DevCycle Driver

## What This Is

A Laravel package that provides a read-only Pennant driver backed by the DevCycle PHP Server SDK. It lets Laravel applications use `Feature::for($user)->active('flag')` with DevCycle as the feature flag backend.

## Key Commands

```bash
composer test        # Run Pest tests
composer analyse     # PHPStan level 6
composer format      # Laravel Pint
composer ci          # test + analyse
```

## Architecture

- `src/DevCycleDriver.php` — Core Pennant `Driver` implementation. Read-only; writes throw `BadMethodCallException`.
- `src/DevCycleServiceProvider.php` — Registers the DevCycle client singleton, management client singleton, and Pennant driver extension.
- `src/Concerns/HasDevCycleFeatureScope.php` — Trait for Eloquent models to satisfy `FeatureScopeable` + `FeatureScopeSerializeable`.
- `src/Services/DevCycleManagementClient.php` — Optional Management API proxy with cached OAuth tokens.
- `src/Http/Controllers/DevCycleFeatureController.php` — Optional REST endpoints for feature management.

## Important Behavior

The DevCycle PHP SDK silently returns the default value when a flag doesn't exist or isn't enabled. There is no way to distinguish "flag off" from "flag missing." This is a DevCycle SDK limitation, not a driver bug.

## Testing Notes

- Tests use Orchestra Testbench with Mockery for the DevCycle client
- Management API tests use `Http::fake()` to stub OAuth + API responses
- Live integration test exists in `tests/Feature/DevCycleLiveDebugTest.php` (skipped unless `DEVCYCLE_LIVE_SDK_KEY` env is set)

## Package Compatibility

- PHP 8.2+
- Laravel 10, 11, 12
- `laravel/pennant` ^1.18
- `devcycle/php-server-sdk` ^2.2
