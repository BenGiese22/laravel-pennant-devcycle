# Laravel Pennant DevCycle Driver

[![Tests](https://github.com/ben-giese22/laravel-pennant-devcycle/actions/workflows/tests.yml/badge.svg)](https://github.com/ben-giese22/laravel-pennant-devcycle/actions/workflows/tests.yml)
[![Code Style](https://github.com/ben-giese22/laravel-pennant-devcycle/actions/workflows/codestyle.yml/badge.svg)](https://github.com/ben-giese22/laravel-pennant-devcycle/actions/workflows/codestyle.yml)
[![Static Analysis](https://github.com/ben-giese22/laravel-pennant-devcycle/actions/workflows/phpstan.yml/badge.svg)](https://github.com/ben-giese22/laravel-pennant-devcycle/actions/workflows/phpstan.yml)

A Laravel Pennant driver backed by the DevCycle PHP SDK. Pennant reads DevCycle Variables for feature evaluation; writes are intentionally unsupported (read-only).

## Installation

```bash
composer require ben-giese22/laravel-pennant-devcycle
```

Pennant store configuration (`config/pennant.php`):

```php
return [
    'default' => 'devcycle',

    'stores' => [
        'devcycle' => [
            'driver' => 'devcycle',
            'sdk_key' => env('DEVCYCLE_SERVER_SDK_KEY'),
            'default' => false, // optional default variable value
        ],
    ],
];
```

The package is auto-discovered via the service provider and publishes `config/devcycle.php` for advanced settings.

## Usage

DevCycle Variable keys map directly to Pennant feature names. Scopes must implement `FeatureScopeable` and return a `DevCycleUser` from `toFeatureIdentifier('devcycle')`.

```php
use Laravel\Pennant\Feature;

$enabled = Feature::for($user)->active('checkout-redesign');
```

Writes such as `define`, `set`, or `activate` are not supported by this driver.

## Management API (optional)

When `devcycle.register_routes` is enabled in `config/devcycle.php`, the package registers API routes to proxy DevCycle's Management API:

- `GET /api/devcycle/features` — list project features (override project with `?project=`).
- `GET /api/devcycle/features/{featureKey}` — fetch a feature.
- `PATCH /api/devcycle/features/{featureKey}` — update a feature payload.

Configure credentials in `config/devcycle.php` or environment variables:

- `DEVCYCLE_MGMT_CLIENT_ID`
- `DEVCYCLE_MGMT_CLIENT_SECRET`
- `DEVCYCLE_MGMT_PROJECT_KEY`
- `DEVCYCLE_MGMT_API_BASE` (optional)
- `DEVCYCLE_MGMT_AUTH_BASE` (optional)

## Testing

```bash
composer test
```

Static analysis and formatting:

```bash
composer analyse
./vendor/bin/pint --test
```
