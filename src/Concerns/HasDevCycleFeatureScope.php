<?php

declare(strict_types=1);

namespace BenGiese22\LaravelPennantDevCycle\Concerns;

use DevCycle\Model\DevCycleUser;

/**
 * Apply this trait to your User model (or any Eloquent model) to satisfy
 * Pennant's FeatureScopeable and FeatureScopeSerializeable contracts
 * for the DevCycle driver.
 *
 * The model must have a `getKey()` method (all Eloquent models do) and
 * optionally an `email` attribute. Override `toDevCycleUser()` for
 * custom user data mapping.
 */
trait HasDevCycleFeatureScope
{
    public function toFeatureIdentifier(string $driver): mixed
    {
        if ($driver !== 'devcycle') {
            return $this->getKey();
        }

        return $this->toDevCycleUser();
    }

    public function featureScopeSerialize(): string
    {
        return (string) $this->getKey();
    }

    /**
     * @return DevCycleUser<array-key, mixed>
     */
    protected function toDevCycleUser(): DevCycleUser
    {
        $data = [
            'user_id' => (string) $this->getKey(),
        ];

        if (isset($this->email)) {
            $data['email'] = $this->email;
        }

        if (isset($this->name)) {
            $data['name'] = $this->name;
        }

        return new DevCycleUser($data);
    }
}
