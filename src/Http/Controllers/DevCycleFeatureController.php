<?php

declare(strict_types=1);

namespace BenGiese22\LaravelPennantDevCycle\Http\Controllers;

use BenGiese22\LaravelPennantDevCycle\Services\DevCycleManagementClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DevCycleFeatureController extends Controller
{
    public function __construct(private readonly DevCycleManagementClient $client) {}

    public function index(Request $request): JsonResponse
    {
        $projectKey = $request->query('project', config('devcycle.mgmt.project_key'));

        $query = array_filter(
            $request->only(['page', 'perPage']),
            static fn ($value) => $value !== null,
        );

        $features = $this->client->listFeatures($projectKey, $query);

        return response()->json($features);
    }

    public function show(string $featureKey, Request $request): JsonResponse
    {
        $projectKey = $request->query('project', config('devcycle.mgmt.project_key'));

        $feature = $this->client->getFeature($projectKey, $featureKey);

        return response()->json($feature);
    }

    public function update(string $featureKey, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string'],
            'description' => ['sometimes', 'string'],
            'tags' => ['sometimes', 'array'],
            'configurations' => ['sometimes', 'array'],
        ]);

        $projectKey = $request->query('project', config('devcycle.mgmt.project_key'));

        $feature = $this->client->updateFeature($projectKey, $featureKey, $validated);

        return response()->json($feature);
    }
}
