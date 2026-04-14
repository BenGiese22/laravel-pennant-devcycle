<?php

declare(strict_types=1);

namespace BenGiese22\LaravelPennantDevCycle\Tests\Fixtures;

use BenGiese22\LaravelPennantDevCycle\Concerns\HasDevCycleFeatureScope;
use Laravel\Pennant\Contracts\FeatureScopeable;
use Laravel\Pennant\Contracts\FeatureScopeSerializeable;

class TraitTestUser implements FeatureScopeable, FeatureScopeSerializeable
{
    use HasDevCycleFeatureScope;

    public function __construct(
        protected int $id,
        public ?string $email = null,
        public ?string $name = null,
    ) {}

    public function getKey(): int
    {
        return $this->id;
    }
}
