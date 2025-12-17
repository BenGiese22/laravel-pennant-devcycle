<?php

declare(strict_types=1);

use DevCycle\Api\DevCycleClient;
use DevCycle\Model\DevCycleOptions;
use DevCycle\Model\DevCycleUser;
use Illuminate\Support\Facades\Log;

it('can call DevCycle live with provided SDK key', function () {
    $sdkKey = env('DEVCYCLE_LIVE_SDK_KEY');

    if (! $sdkKey) {
        $this->markTestSkipped('Set DEVCYCLE_LIVE_SDK_KEY to run live DevCycle calls.');
    }

    $user = new DevCycleUser([
        'user_id' => env('DEVCYCLE_LIVE_USER_ID', 'debug-user'),
    ]);

    $client = new DevCycleClient($sdkKey, new DevCycleOptions);

    $variables = $client->allVariables($user);

    Log::info('DevCycle live variables response', ['variables' => $variables]);

    expect($variables)->not()->toBeNull();
});
