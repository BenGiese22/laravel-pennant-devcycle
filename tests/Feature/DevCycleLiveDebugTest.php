<?php

declare(strict_types=1);

use DevCycle\Api\DevCycleClient;
use DevCycle\Model\DevCycleOptions;
use DevCycle\Model\DevCycleUser;

beforeEach(function () {
    $this->sdkKey = env('DEVCYCLE_LIVE_SDK_KEY');

    if (! $this->sdkKey) {
        $this->markTestSkipped('Set DEVCYCLE_LIVE_SDK_KEY to run live DevCycle calls.');
    }

    $this->testFlag = env('DEVCYCLE_LIVE_TEST_FLAG');

    if (! $this->testFlag) {
        $this->markTestSkipped('Set DEVCYCLE_LIVE_TEST_FLAG to run live DevCycle calls.');
    }

    $this->liveClient = new DevCycleClient($this->sdkKey, new DevCycleOptions);
    $this->liveUser = new DevCycleUser([
        'user_id' => env('DEVCYCLE_LIVE_USER_ID', 'debug-user'),
    ]);
});

it('lists all variables visible to the user', function () {
    $variables = $this->liveClient->allVariables($this->liveUser);

    $keys = array_keys((array) $variables);

    dump('Variables visible to user:', $keys);
    dump("Test flag [{$this->testFlag}] present:", in_array($this->testFlag, $keys));

    expect($keys)->toContain($this->testFlag);
});

it('returns the variable object for an existing flag', function () {
    $variable = $this->liveClient->variable($this->liveUser, $this->testFlag, false);

    dump("Flag [{$this->testFlag}] variable object:", [
        'key' => $variable->getKey(),
        'value' => $variable->getValue(),
        'type' => $variable->getType(),
        'isDefaulted' => $variable->isDefaulted(),
    ]);

    expect($variable->getKey())->toBe($this->testFlag);
    expect($variable->isDefaulted())->toBeFalse('Flag exists but SDK says it is defaulted — check targeting rules in DevCycle');
});

it('returns the real value for an existing enabled flag with matching type', function () {
    $result = $this->liveClient->variableValue($this->liveUser, $this->testFlag, false);

    dump("Existing flag [{$this->testFlag}] with boolean default (false):", $result);
    dump('If this returned false, check: is the flag serving true for all users in DevCycle?');

    // This just records the value — check the dump output to see what DevCycle returns
    expect($result)->toBeBool();
});

it('demonstrates type mismatch: string default on a boolean flag returns the string default', function () {
    // This proves the SDK requires type-matching defaults
    $result = $this->liveClient->variableValue($this->liveUser, $this->testFlag, 'WRONG_TYPE_DEFAULT');

    dump("Existing flag [{$this->testFlag}] with string default:", $result);

    // Even though the flag exists and is enabled, a string default on a boolean
    // flag causes the SDK to return the default — it can't type-match.
    expect($result)->toBe('WRONG_TYPE_DEFAULT');
});

it('returns the default for a non-existent flag', function () {
    $result = $this->liveClient->variableValue($this->liveUser, 'this-flag-does-not-exist-anywhere', false);

    dump('Non-existent flag returned:', $result);

    expect($result)->toBeFalse();
});
