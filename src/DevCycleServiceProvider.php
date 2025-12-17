<?php

declare(strict_types=1);

namespace BenGiese22\LaravelPennantDevCycle;

use DevCycle\Api\DevCycleClient;
use DevCycle\Model\DevCycleOptions;
use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;

class DevCycleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/devcycle.php', 'devcycle');

        $this->app->singleton(DevCycleClient::class, function ($app) {
            $config = $app['config']->get('pennant.stores.devcycle', []);

            $sdkKey = $config['sdk_key'] ?? env('DEVCYCLE_SERVER_SDK_KEY');

            $options = new DevCycleOptions(
                (bool) ($config['enable_edgedb'] ?? false),
                $config['bucketing_hostname'] ?? null,
                $config['unix_socket_path'] ?? null,
            );

            return new DevCycleClient((string) $sdkKey, $options);
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/config/devcycle.php' => config_path('devcycle.php'),
            ], 'devcycle-config');
        }

        if ((bool) config('devcycle.register_routes', false)) {
            $this->loadRoutesFrom(__DIR__.'/routes/api.php');
        }

        Feature::extend('devcycle', fn ($app, $config) => new DevCycleDriver(
            $app->make(DevCycleClient::class),
            $config,
        ));
    }
}
