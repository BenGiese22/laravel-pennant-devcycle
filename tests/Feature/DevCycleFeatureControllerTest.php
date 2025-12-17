<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

/** @var \BenGiese22\LaravelPennantDevCycle\Tests\TestCase $this */
beforeEach(function () {
    config()->set('devcycle.mgmt', [
        'client_id' => 'test-client-id',
        'client_secret' => 'test-client-secret',
        'project_key' => 'test-project',
        'api_base' => 'https://api.devcycle.com',
        'auth_base' => 'https://auth.devcycle.com',
    ]);
});

it('lists features', function () {
    Http::fake([
        'https://auth.devcycle.com/oauth/token' => Http::response([
            'access_token' => 'test-token',
        ], 200),
        'https://api.devcycle.com/v2/projects/test-project/features*' => Http::response([
            'data' => [
                ['key' => 'feature-one'],
            ],
        ], 200),
    ]);

    $response = $this->getJson('/api/devcycle/features');

    $response->assertOk()->assertJson([
        'data' => [
            ['key' => 'feature-one'],
        ],
    ]);

    Http::assertSent(fn (Request $request) => $request->url() === 'https://auth.devcycle.com/oauth/token'
        && $request['client_id'] === 'test-client-id'
        && $request['client_secret'] === 'test-client-secret'
        && $request['grant_type'] === 'client_credentials');

    Http::assertSent(fn (Request $request) => $request->url() === 'https://api.devcycle.com/v2/projects/test-project/features'
        && $request->hasHeader('Authorization', 'Bearer test-token')
        && $request->method() === 'GET');

    Http::assertSentCount(2);
});

it('shows a feature', function () {
    Http::fake([
        'https://auth.devcycle.com/oauth/token' => Http::response([
            'access_token' => 'test-token',
        ], 200),
        'https://api.devcycle.com/v2/projects/test-project/features/my-feature' => Http::response([
            'key' => 'my-feature',
            'name' => 'My Feature',
        ], 200),
    ]);

    $response = $this->getJson('/api/devcycle/features/my-feature');

    $response->assertOk()->assertJson([
        'key' => 'my-feature',
        'name' => 'My Feature',
    ]);

    Http::assertSent(fn (Request $request) => $request->url() === 'https://api.devcycle.com/v2/projects/test-project/features/my-feature'
        && $request->hasHeader('Authorization', 'Bearer test-token')
        && $request->method() === 'GET');
});

it('updates a feature', function () {
    $payload = [
        'name' => 'Updated Name',
        'tags' => ['alpha', 'beta'],
    ];

    Http::fake([
        'https://auth.devcycle.com/oauth/token' => Http::response([
            'access_token' => 'test-token',
        ], 200),
        'https://api.devcycle.com/v2/projects/test-project/features/feature-123' => Http::response([
            'key' => 'feature-123',
            'name' => 'Updated Name',
            'tags' => ['alpha', 'beta'],
        ], 200),
    ]);

    $response = $this->patchJson('/api/devcycle/features/feature-123', $payload);

    $response->assertOk()->assertJson([
        'key' => 'feature-123',
        'name' => 'Updated Name',
        'tags' => ['alpha', 'beta'],
    ]);

    Http::assertSent(fn (Request $request) => $request->url() === 'https://api.devcycle.com/v2/projects/test-project/features/feature-123'
        && $request->method() === 'PATCH'
        && $request->hasHeader('Authorization', 'Bearer test-token')
        && $request->data() === $payload);
});

it('validates update payloads', function () {
    Http::fake();

    $response = $this->patchJson('/api/devcycle/features/feature-123', [
        'tags' => 'not-an-array',
    ]);

    $response->assertStatus(422);

    Http::assertNothingSent();
});
