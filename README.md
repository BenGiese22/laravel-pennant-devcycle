# Laravel Pennant DevCycle

A Laravel Pennant driver powered by the [DevCycle PHP Server SDK](https://github.com/DevCycleHQ/php-server-sdk).

## Installation

```bash
composer require bengiese22/laravel-pennant-devcycle
```

The service provider is auto-discovered. If you have package discovery disabled, register the provider manually:

```php
// config/app.php
'providers' => [
    // ...
    \Bgiese\LaravelPennantDevcycle\LaravelPennantDevcycleServiceProvider::class,
],
```

## Configuration

Update your Pennant configuration to use the `devcycle` store. The driver accepts the SDK key, optional DevCycle options, and a default scope used when resolving default variable values.

```php
// config/pennant.php

'default' => 'devcycle',

'stores' => [
    'devcycle' => [
        'driver' => 'devcycle',
        'sdk_key' => env('DEVCYCLE_SDK_KEY'),
        'default_scope' => [
            'user_id' => 'system',
            'email' => 'system@example.com',
        ],
        'options' => [
            'enable_edge_db' => false,
            'bucketing_api_hostname' => env('DEVCYCLE_BUCKETING_HOST'),
            'unix_socket_path' => null,
            'http' => [
                // Guzzle request options passed to the DevCycle SDK
            ],
        ],
    ],
],
```

The `default_scope` array is converted into a `DevCycleUser` when resolving the full list of variables. When Pennant scopes implement `Laravel\Pennant\Contracts\FeatureScopeable`, the driver converts them via `toFeatureIdentifier`. Strings are treated as both the `user_id` and the `email`.

## Usage

Once configured you can use Pennant as usual:

```php
use Laravel\Pennant\Feature;

if (Feature::for($user)->active('new-dashboard')) {
    // ...
}
```

When interacting with the DevCycle dashboard, create variables that match the feature keys you check in code. Mutating operations such as `Feature::define()` or `Feature::set()` throw exceptions; feature lifecycle is owned by DevCycle.

## Testing

This package ships with Pest tests built on top of Orchestra Testbench. Run them locally with:

```bash
composer test
```

## License

The MIT License (MIT). See the [LICENSE](LICENSE) file for details.
