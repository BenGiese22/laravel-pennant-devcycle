<?php

declare(strict_types=1);

namespace BenGiese22\LaravelPennantDevCycle;

use BadMethodCallException;
use DevCycle\Api\DevCycleClient;
use DevCycle\Model\DevCycleUser;
use Laravel\Pennant\Contracts\Driver;
use Laravel\Pennant\Contracts\FeatureScopeable;

class DevCycleDriver implements Driver
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        protected DevCycleClient $client,
        protected array $config = [],
    ) {}

    public function define(string $feature, callable $resolver): void
    {
        throw new BadMethodCallException('Defining features is not supported by the DevCycle driver.');
    }

    public function defined(): array
    {
        return [];
    }

    public function getAll(array $features): array
    {
        $evaluations = [];

        foreach ($features as $feature => $scopes) {
            $evaluations[$feature] = [];

            foreach ($scopes as $scope) {
                $evaluations[$feature][] = $this->get($feature, $scope);
            }
        }

        return $evaluations;
    }

    public function get(string $feature, mixed $scope): mixed
    {
        $user = $this->toDevCycleUser($scope);

        return $this->evaluateFeature($feature, $user);
    }

    public function set(string $feature, mixed $scope, mixed $value): void
    {
        throw new BadMethodCallException('Setting features is not supported by the DevCycle driver.');
    }

    public function setForAllScopes(string $feature, mixed $value): void
    {
        throw new BadMethodCallException('Setting features for all scopes is not supported by the DevCycle driver.');
    }

    public function delete(string $feature, mixed $scope): void
    {
        throw new BadMethodCallException('Deleting features is not supported by the DevCycle driver.');
    }

    /**
     * @param  array<int, string>|null  $features
     */
    public function purge(?array $features): void
    {
        //
    }

    /**
     * @param  DevCycleUser<array-key, mixed>|FeatureScopeable  $scope
     * @return DevCycleUser<array-key, mixed>
     */
    protected function toDevCycleUser(mixed $scope): DevCycleUser
    {
        if ($scope instanceof DevCycleUser) {
            return $scope;
        }

        if (! $scope instanceof FeatureScopeable) {
            throw new BadMethodCallException('Scope must implement FeatureScopeable.');
        }

        $identifier = $scope->toFeatureIdentifier('devcycle');

        if (! $identifier instanceof DevCycleUser) {
            throw new BadMethodCallException('Feature scope must resolve to a DevCycleUser.');
        }

        return $identifier;
    }

    /**
     * @param  DevCycleUser<array-key, mixed>  $user
     */
    protected function evaluateFeature(string $feature, DevCycleUser $user): mixed
    {
        $default = $this->config['default'] ?? false;

        return $this->client->variableValue($user, $feature, $default);
    }
}
