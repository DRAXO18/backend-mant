<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceTypeController;

Route::apiResource('services', ServiceController::class)
    ->middleware([
        'index' => 'permission:services.view',
        'store' => 'permission:services.create',
        'update' => 'permission:services.update',
        'destroy' => 'permission:services.delete'
    ]);

Route::post(
    'services/detail',
    [ServiceController::class, 'serviceDetailsStore']
)->middleware('permission:services.create');


Route::apiResource('services-type', ServiceTypeController::class)
    ->middleware([
        'index' => 'permission:service-types.view',
        'store' => 'permission:service-types.create',
        'update' => 'permission:service-types.update',
        'destroy' => 'permission:service-types.delete'
    ]);