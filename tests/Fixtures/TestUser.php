<?php

declare(strict_types=1);

namespace BenGiese22\LaravelPennantDevCycle\Tests\Fixtures;

use DevCycle\Model\DevCycleUser;
use Laravel\Pennant\Contracts\FeatureScopeable;
use Laravel\Pennant\Contracts\FeatureScopeSerializeable;

class TestUser implements FeatureScopeable, FeatureScopeSerializeable
{
    public function __construct(protected string $id) {}

    public function toFeatureIdentifier(string $driver): mixed
    {
        return new class($this->id) extends DevCycleUser implements FeatureScopeSerializeable
        {
            public function __construct(protected string $identifier)
            {
                parent::__construct(['user_id' => $this->identifier]);
            }

            public function featureScopeSerialize(): string
            {
                return $this->identifier;
            }
        };
    }

    public function featureScopeSerialize(): string
    {
        return $this->id;
    }
}
