<?php

declare(strict_types=1);

namespace Bgiese\LaravelPennantDevcycle\Tests\Support\Models;

use DevCycle\Model\DevCycleUser;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Pennant\Contracts\FeatureScopeable;

class User extends Authenticatable implements FeatureScopeable
{
    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    public function toFeatureIdentifier(string $driver): DevCycleUser
    {
        return new DevCycleUser([
            'user_id' => (string) $this->getKey(),
            'email' => $this->email ?? (string) $this->getKey(),
        ]);
    }
}
