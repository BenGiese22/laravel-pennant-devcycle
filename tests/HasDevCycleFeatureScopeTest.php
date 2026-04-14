<?php

declare(strict_types=1);

use BenGiese22\LaravelPennantDevCycle\Tests\Fixtures\TraitTestUser;
use DevCycle\Model\DevCycleUser;

it('returns a DevCycleUser with user_id, email, and name for the devcycle driver', function () {
    $user = new TraitTestUser(42, 'alice@example.com', 'Alice');

    $result = $user->toFeatureIdentifier('devcycle');

    expect($result)
        ->toBeInstanceOf(DevCycleUser::class)
        ->and($result->getUserId())->toBe('42')
        ->and($result->getEmail())->toBe('alice@example.com')
        ->and($result->getName())->toBe('Alice');
});

it('returns the model key for non-devcycle drivers', function () {
    $user = new TraitTestUser(99);

    $result = $user->toFeatureIdentifier('database');

    expect($result)->toBe(99);
});

it('serializes the feature scope as a string key', function () {
    $user = new TraitTestUser(7);

    expect($user->featureScopeSerialize())->toBe('7');
});

it('omits email and name from DevCycleUser when not set on model', function () {
    $user = new TraitTestUser(10);

    $result = $user->toFeatureIdentifier('devcycle');

    expect($result)
        ->toBeInstanceOf(DevCycleUser::class)
        ->and($result->getUserId())->toBe('10')
        ->and($result->getEmail())->toBeNull()
        ->and($result->getName())->toBeNull();
});
