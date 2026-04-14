<?php

use BenGiese22\LaravelPennantDevCycle\Http\Controllers\DevCycleFeatureController;
use Illuminate\Support\Facades\Route;

Route::middleware(config('devcycle.routes.middleware', ['api']))
    ->prefix(config('devcycle.routes.prefix', 'api/devcycle'))
    ->group(function (): void {
        Route::get('/features', [DevCycleFeatureController::class, 'index']);
        Route::get('/features/{featureKey}', [DevCycleFeatureController::class, 'show']);
        Route::patch('/features/{featureKey}', [DevCycleFeatureController::class, 'update']);
    });
