<?php

declare(strict_types=1);

namespace BenGiese22\LaravelPennantDevCycle\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DevCycleManagementClient
{
    private const TOKEN_CACHE_KEY = 'devcycle:mgmt:access_token';

    private const TOKEN_TTL_BUFFER_SECONDS = 60;

    public function getAccessToken(): string
    {
        $cached = Cache::get(self::TOKEN_CACHE_KEY);

        if (is_string($cached)) {
            return $cached;
        }

        $authBase = rtrim((string) config('devcycle.mgmt.auth_base'), '/');

        $response = Http::asForm()->post($authBase.'/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => config('devcycle.mgmt.client_id'),
            'client_secret' => config('devcycle.mgmt.client_secret'),
            'audience' => rtrim((string) config('devcycle.mgmt.api_base'), '/').'/',
        ]);

        $response->throw();

        $token = (string) $response->json('access_token');
        $expiresIn = (int) $response->json('expires_in', 3600);
        $ttl = max($expiresIn - self::TOKEN_TTL_BUFFER_SECONDS, 60);

        Cache::put(self::TOKEN_CACHE_KEY, $token, $ttl);

        return $token;
    }

    /**
     * @param  array<string, string|int>  $query
     * @return array<string, mixed>
     */
    public function listFeatures(string $projectKey, array $query = []): array
    {
        $response = $this->requestWithToken()->get(
            "v2/projects/{$projectKey}/features",
            $query,
        );

        $response->throw();

        return $response->json();
    }

    /**
     * @return array<string, mixed>
     */
    public function getFeature(string $projectKey, string $featureKey): array
    {
        $response = $this->requestWithToken()->get(
            "v2/projects/{$projectKey}/features/{$featureKey}"
        );

        $response->throw();

        return $response->json();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function updateFeature(string $projectKey, string $featureKey, array $payload): array
    {
        $response = $this->requestWithToken()->patch(
            "v2/projects/{$projectKey}/features/{$featureKey}",
            $payload,
        );

        $response->throw();

        return $response->json();
    }

    protected function requestWithToken(): PendingRequest
    {
        $apiBase = rtrim((string) config('devcycle.mgmt.api_base'), '/');

        return Http::withToken($this->getAccessToken())
            ->baseUrl($apiBase);
    }
}
