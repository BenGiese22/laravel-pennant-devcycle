<?php

declare(strict_types=1);

use Bgiese\LaravelPennantDevcycle\DevCycleDriver;
use Bgiese\LaravelPennantDevcycle\Tests\Support\Models\User;
use DevCycle\Api\DevCycleClient;
use DevCycle\Model\DevCycleUser;
use DevCycle\Model\Variable;
use Laravel\Pennant\Feature;
use Mockery;

beforeEach(function () {
    $client = Mockery::mock(DevCycleClient::class);

    $this->app->bind(DevCycleClient::class, function () use ($client) {
        return $client;
    });

    config()->set('pennant.stores.devcycle', [
        'driver' => DevCycleDriver::class,
        'sdk_key' => 'sdk-'.fake()->uuid(),
        'default_scope' => [
            'user_id' => 'system',
            'email' => 'system@example.com',
        ],
    ]);

    config()->set('pennant.default', 'devcycle');

    $this->mockClient = $client;
});

it('resolves the devcycle driver', function () {
    expect(Feature::store()->getDriver())->toBeInstanceOf(DevCycleDriver::class);
});

it('determines activation for a feature', function () {
    $user = User::query()->make([
        'id' => fake()->uuid(),
        'email' => fake()->unique()->safeEmail(),
    ]);

    $feature = fake()->asciify('flag-*****');
    $state = fake()->boolean();

    $defaultVariables = [
        $feature => new Variable([
            'key' => $feature,
            'value' => $state,
            'type' => 'boolean',
            'isDefaulted' => false,
        ]),
    ];

    $this->mockClient
        ->shouldReceive('allVariables')
        ->andReturnUsing(function (DevCycleUser $user) use ($defaultVariables) {
            return $defaultVariables;
        });

    $this->mockClient
        ->shouldReceive('variable')
        ->andReturn(new Variable([
            'key' => $feature,
            'value' => $state,
            'type' => 'boolean',
            'isDefaulted' => false,
        ]));

    $active = Feature::for($user)->active($feature);

    expect($active)->toBe($state);
});
