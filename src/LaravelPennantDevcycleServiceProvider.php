<?php

declare(strict_types=1);

namespace Bgiese\LaravelPennantDevcycle;

use DevCycle\Api\DevCycleClient;
use DevCycle\Model\DevCycleOptions;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use Laravel\Pennant\Feature;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelPennantDevcycleServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-pennant-devcycle')
            ->hasConfigFile('pennant-devcycle');
    }

    public function packageBooted(): void
    {
        $this->extendDriver('devcycle');
        $this->extendDriver(DevCycleDriver::class);

        $this->app->bind(DevCycleClient::class, function (Application $app, array $parameters) {
            $sdkKey = Arr::get($parameters, 'sdkKey') ?? config('services.devcycle.sdk_key');

            $options = Arr::get($parameters, 'options') ?? $this->buildOptions(config('services.devcycle.options', []));

            if ($options instanceof DevCycleOptions) {
                return new DevCycleClient($sdkKey, $options);
            }

            return new DevCycleClient($sdkKey, $this->buildOptions($options));
        });
    }

    private function extendDriver(string $name): void
    {
        Feature::extend($name, function (Application $app, array $config) {
            $client = $app->make(DevCycleClient::class, [
                'sdkKey' => Arr::get($config, 'sdk_key'),
                'options' => $this->buildOptions(Arr::get($config, 'options', [])),
            ]);

            return new DevCycleDriver($client, $config);
        });
    }

    /**
     * @param  array<string, mixed>|DevCycleOptions|null  $options
     */
    private function buildOptions(array|DevCycleOptions|null $options): DevCycleOptions
    {
        if ($options instanceof DevCycleOptions) {
            return $options;
        }

        $options ??= [];

        return new DevCycleOptions(
            (bool) ($options['enable_edge_db'] ?? false),
            $options['bucketing_api_hostname'] ?? null,
            $options['unix_socket_path'] ?? null,
            (array) ($options['http'] ?? [])
        );
    }
}
