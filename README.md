# Laravel Pennant DevCycle Driver

[![Tests](https://github.com/bengiese22/laravel-pennant-devcycle/actions/workflows/tests.yml/badge.svg)](https://github.com/bengiese22/laravel-pennant-devcycle/actions/workflows/tests.yml)
[![Code Style](https://github.com/bengiese22/laravel-pennant-devcycle/actions/workflows/codestyle.yml/badge.svg)](https://github.com/bengiese22/laravel-pennant-devcycle/actions/workflows/codestyle.yml)
[![Static Analysis](https://github.com/bengiese22/laravel-pennant-devcycle/actions/workflows/phpstan.yml/badge.svg)](https://github.com/bengiese22/laravel-pennant-devcycle/actions/workflows/phpstan.yml)

A Laravel Pennant driver backed by the DevCycle PHP SDK. Pennant reads DevCycle Variables for feature evaluation; writes are intentionally unsupported (read-only).

## Installation

```bash
composer require bengiese22/laravel-pennant-devcycle
```

The package is auto-discovered via the service provider. Publish the optional config file:

```bash
php artisan vendor:publish --tag=devcycle-config
```

## Configuration

Add a `devcycle` store to `config/pennant.php`:

```php
return [
    'default' => 'devcycle',

    'stores' => [
        'devcycle' => [
            'driver' => 'devcycle',
            'sdk_key' => env('DEVCYCLE_SERVER_SDK_KEY'),
            'default' => false, // default value when a variable is not found
        ],
    ],
];
```

## Setting Up Your User Model

Scopes passed to `Feature::for()` must implement Pennant's `FeatureScopeable` and `FeatureScopeSerializeable` contracts and return a `DevCycleUser` instance.

The easiest way is to add the included trait to your User model:

```php
use BenGiese22\LaravelPennantDevCycle\Concerns\HasDevCycleFeatureScope;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Pennant\Contracts\FeatureScopeable;
use Laravel\Pennant\Contracts\FeatureScopeSerializeable;

class User extends Authenticatable implements FeatureScopeable, FeatureScopeSerializeable
{
    use HasDevCycleFeatureScope;
}
```

The trait maps `id`, `email`, and `name` to the `DevCycleUser` automatically. If you need custom data (e.g., custom data fields for targeting), override `toDevCycleUser()`:

```php
use DevCycle\Model\DevCycleUser;

protected function toDevCycleUser(): DevCycleUser
{
    return new DevCycleUser([
        'user_id' => (string) $this->id,
        'email' => $this->email,
        'name' => $this->name,
        'customData' => [
            'plan' => $this->plan,
            'organization_id' => $this->organization_id,
        ],
    ]);
}
```

## Usage

DevCycle Variable keys map directly to Pennant feature names:

```php
use Laravel\Pennant\Feature;

// Boolean check
if (Feature::for($user)->active('checkout-redesign')) {
    // new checkout flow
}

// Get the variable value (strings, numbers, JSON)
$variant = Feature::for($user)->value('onboarding-flow');
```

Writes (`define`, `set`, `activate`, `deactivate`) are not supported and will throw a `BadMethodCallException`.

### Default Values and Type Matching

The `default` value in your Pennant store config serves two purposes:

1. **Fallback** — returned when a variable doesn't exist, the flag is disabled, or the user isn't targeted
2. **Type hint** — the DevCycle SDK uses the default's type to resolve the variable. If the types don't match, the SDK silently returns your default even when the flag exists and is enabled.

```php
'devcycle' => [
    'driver' => 'devcycle',
    'sdk_key' => env('DEVCYCLE_SERVER_SDK_KEY'),
    'default' => false, // boolean — will only resolve boolean variables correctly
],
```

For example, if a DevCycle variable is a boolean but your default is a string, the SDK returns the string default — no error, no exception. The flag is invisible.

This means all three of these cases produce identical behavior:
- The flag doesn't exist in DevCycle
- The flag is disabled or the user isn't targeted
- The flag exists but its type doesn't match the default's type

**If your project uses a mix of boolean and string variables**, use `Feature::value()` with an explicit default per-call instead of relying on the store-level default:

```php
// Boolean flag — pass a boolean default
$enabled = Feature::for($user)->value('checkout-redesign') ?? false;

// String flag — pass a string default
$variant = Feature::for($user)->value('onboarding-flow') ?? 'control';
```

## Management API (optional)

The package can proxy DevCycle's Management API for listing and updating features. This is disabled by default.

Enable it in `config/devcycle.php`:

```php
'register_routes' => true,
```

### Routes

| Method  | URI                                     | Description       |
|---------|-----------------------------------------|-------------------|
| GET     | `/api/devcycle/features`                | List features     |
| GET     | `/api/devcycle/features/{featureKey}`   | Get a feature     |
| PATCH   | `/api/devcycle/features/{featureKey}`   | Update a feature  |

Override the project key per-request with `?project=other-project`.

### Route Middleware

By default, routes use the `api` middleware group. **You should add authentication middleware** before enabling routes in production:

```php
// config/devcycle.php
'routes' => [
    'middleware' => ['api', 'auth:sanctum'],
    'prefix' => 'api/devcycle',
],
```

### Credentials

Set these in `config/devcycle.php` or via environment variables:

| Variable                      | Description               |
|-------------------------------|---------------------------|
| `DEVCYCLE_MGMT_CLIENT_ID`    | OAuth client ID           |
| `DEVCYCLE_MGMT_CLIENT_SECRET`| OAuth client secret       |
| `DEVCYCLE_MGMT_PROJECT_KEY`  | Default project key       |
| `DEVCYCLE_MGMT_API_BASE`     | API base URL (optional)   |
| `DEVCYCLE_MGMT_AUTH_BASE`    | Auth base URL (optional)  |

OAuth access tokens are cached automatically using Laravel's cache driver.

## Testing

```bash
composer test        # Run Pest tests
composer analyse     # Run PHPStan (level 6)
./vendor/bin/pint --test  # Check code style
composer ci          # Run tests + analysis
```

## License

MIT. See [LICENSE.md](LICENSE.md).
