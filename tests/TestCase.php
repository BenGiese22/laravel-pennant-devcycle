<?php

declare(strict_types=1);

namespace BenGiese22\LaravelPennantDevCycle\Tests;

use BenGiese22\LaravelPennantDevCycle\DevCycleServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

/**
 * @property \Illuminate\Contracts\Foundation\Application $app
 *
 * @method \Illuminate\Testing\TestResponse getJson(string $uri, array $headers = [])
 * @method \Illuminate\Testing\TestResponse patchJson(string $uri, array $data = [], array $headers = [])
 */
abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            DevCycleServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('pennant.default', 'devcycle');
        $app['config']->set('pennant.stores.devcycle', [
            'driver' => 'devcycle',
            'sdk_key' => 'test-sdk-key',
            'default' => false,
        ]);

        $app['config']->set('devcycle.register_routes', true);
    }
}
