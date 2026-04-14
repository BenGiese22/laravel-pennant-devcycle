<?php

declare(strict_types=1);

use BenGiese22\LaravelPennantDevCycle\Tests\Fixtures\TestUser;
use DevCycle\Api\DevCycleClient;
use DevCycle\Model\DevCycleUser;
use Illuminate\Support\Facades\Config;
use Laravel\Pennant\Feature;
use Mockery\ExpectationInterface;
use Mockery\MockInterface;

/** @var \BenGiese22\LaravelPennantDevCycle\Tests\TestCase $this */
it('evaluates features via DevCycle variable values', function () {
    $scope = new TestUser('user-123');

    /** @var MockInterface&DevCycleClient $client */
    $client = \Mockery::mock(DevCycleClient::class);
    $this->app->instance(DevCycleClient::class, $client);

    /** @var ExpectationInterface $expectation */
    $expectation = $client->shouldReceive('variableValue');
    $expectation->once()
        ->with(
            \Mockery::on(fn (DevCycleUser $user) => $user->getUserId() === 'user-123'),
            'my-feature',
            false,
        )
        ->andReturn(true);

    expect(Feature::for($scope)->active('my-feature'))->toBeTrue();
});

it('passes configured defaults to DevCycle', function () {
    Config::set('pennant.stores.devcycle.default', true);

    $scope = new TestUser('user-abc');

    /** @var MockInterface&DevCycleClient $client */
    $client = \Mockery::mock(DevCycleClient::class);
    $this->app->instance(DevCycleClient::class, $client);

    /** @var ExpectationInterface $expectation */
    $expectation = $client->shouldReceive('variableValue');
    $expectation->once()
        ->with(
            \Mockery::on(fn (DevCycleUser $user) => $user->getUserId() === 'user-abc'),
            'beta-flag',
            true,
        )
        ->andReturn(true);

    expect(Feature::for($scope)->active('beta-flag'))->toBeTrue();
});

it('throws for scopes that are not FeatureScopeable', function () {
    Feature::for('plain-string-scope')->active('my-feature');
})->throws(\BadMethodCallException::class);

it('throws when attempting to write feature state', function () {
    $scope = new TestUser('user-123');

    Feature::for($scope)->activate('my-feature');
})->throws(BadMethodCallException::class);

it('evaluates multiple features across scopes via getAll', function () {
    $scopeA = new TestUser('user-a');
    $scopeB = new TestUser('user-b');

    /** @var MockInterface&DevCycleClient $client */
    $client = \Mockery::mock(DevCycleClient::class);
    $this->app->instance(DevCycleClient::class, $client);

    /** @var ExpectationInterface $expectA1 */
    $expectA1 = $client->shouldReceive('variableValue');
    $expectA1->once()
        ->with(\Mockery::on(fn (DevCycleUser $u) => $u->getUserId() === 'user-a'), 'feat-a', false)
        ->andReturn(true);

    /** @var ExpectationInterface $expectA2 */
    $expectA2 = $client->shouldReceive('variableValue');
    $expectA2->once()
        ->with(\Mockery::on(fn (DevCycleUser $u) => $u->getUserId() === 'user-b'), 'feat-a', false)
        ->andReturn(false);

    /** @var ExpectationInterface $expectB1 */
    $expectB1 = $client->shouldReceive('variableValue');
    $expectB1->once()
        ->with(\Mockery::on(fn (DevCycleUser $u) => $u->getUserId() === 'user-a'), 'feat-b', false)
        ->andReturn('variant-x');

    $driver = new \BenGiese22\LaravelPennantDevCycle\DevCycleDriver($client, ['default' => false]);

    $result = $driver->getAll([
        'feat-a' => [$scopeA, $scopeB],
        'feat-b' => [$scopeA],
    ]);

    expect($result)->toBe([
        'feat-a' => [true, false],
        'feat-b' => ['variant-x'],
    ]);
});

it('handles purge as a no-op without errors', function () {
    /** @var MockInterface&DevCycleClient $client */
    $client = \Mockery::mock(DevCycleClient::class);

    $driver = new \BenGiese22\LaravelPennantDevCycle\DevCycleDriver($client, []);

    $driver->purge(['some-feature']);
    $driver->purge(null);

    expect(true)->toBeTrue();
});
