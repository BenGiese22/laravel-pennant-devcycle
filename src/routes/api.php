<?php

use BenGiese22\LaravelPennantDevCycle\Http\Controllers\DevCycleFeatureController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')
    ->prefix('api')
    ->group(function (): void {
        Route::get('/devcycle/features', [DevCycleFeatureController::class, 'index']);
        Route::get('/devcycle/features/{featureKey}', [DevCycleFeatureController::class, 'show']);
        Route::patch('/devcycle/features/{featureKey}', [DevCycleFeatureController::class, 'update']);
    });
