<?php

declare(strict_types=1);

namespace Bgiese\LaravelPennantDevcycle;

use BadMethodCallException;
use DevCycle\Api\DevCycleClient;
use DevCycle\Model\DevCycleUser;
use DevCycle\Model\Variable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Laravel\Pennant\Contracts\Driver;
use Laravel\Pennant\Contracts\FeatureScopeable;

class DevCycleDriver implements Driver
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        protected DevCycleClient $client,
        protected array $config = []
    ) {
        $this->config = array_merge([
            'default_scope' => [
                'user_id' => 'system',
                'email' => 'system@example.com',
            ],
        ], $config);
    }

    public function define(string $feature, callable $resolver): void
    {
        throw new BadMethodCallException('Defining features is not supported while using DevCycle.');
    }

    public function defined(): array
    {
        $variables = $this->client->allVariables($this->toUser($this->config['default_scope']));

        return Collection::make($variables)
            ->map(static fn (Variable $variable) => $variable->getValue())
            ->all();
    }

    public function getAll(array $features): array
    {
        return Collection::make($features)
            ->map(fn ($scopes, $feature) => Collection::make($scopes)
                ->map(fn ($scope) => $this->get($feature, $scope))
                ->all())
            ->all();
    }

    public function get(string $feature, mixed $scope): mixed
    {
        $variables = Collection::make(
            $this->client->allVariables($this->toUser($this->config['default_scope']))
        );

        $default = $variables->get($feature);

        if ($default instanceof Variable) {
            $default = $this->defaultValue($default);
        } else {
            $default = null;
        }

        try {
            $variable = $this->client->variable($this->toUser($scope), $feature, $default);
        } catch (\Throwable $exception) {
            Log::error('Unable to resolve feature flag via DevCycle', [
                'feature' => $feature,
                'scope' => $scope,
                'exception' => $exception->getMessage(),
            ]);

            return false;
        }

        if (! $variable instanceof Variable) {
            return false;
        }

        return $variable->getValue();
    }

    public function set(string $feature, mixed $scope, mixed $value): void
    {
        throw new BadMethodCallException('Setting features is not supported while using DevCycle.');
    }

    public function setForAllScopes(string $feature, mixed $value): void
    {
        throw new BadMethodCallException('Setting features is not supported while using DevCycle.');
    }

    public function delete(string $feature, mixed $scope): void
    {
        throw new BadMethodCallException('Deleting features is not supported while using DevCycle.');
    }

    public function purge(?array $features): void
    {
        throw new BadMethodCallException('Purging features is not supported while using DevCycle.');
    }

    private function toUser(mixed $scope): DevCycleUser
    {
        if ($scope instanceof DevCycleUser) {
            return $scope;
        }

        if ($scope instanceof FeatureScopeable) {
            $scope = $scope->toFeatureIdentifier(static::class);
        }

        if (is_array($scope)) {
            return new DevCycleUser($scope);
        }

        if (is_string($scope)) {
            return new DevCycleUser([
                'user_id' => $scope,
                'email' => $scope,
            ]);
        }

        throw new InvalidArgumentException('Unable to convert provided scope into a DevCycle user.');
    }

    private function defaultValue(Variable $variable): mixed
    {
        return match (strtolower((string) $variable->getType())) {
            'boolean' => true,
            'integer', 'number' => 1,
            'double' => 1.0,
            'string' => 'default',
            'json' => [],
            default => null,
        };
    }
}
