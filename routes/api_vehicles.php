<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VehicleController;

Route::apiResource('vehicles', VehicleController::class)
    ->middleware([
        'index' => 'permission:vehicles.view',
        'store' => 'permission:vehicles.create',
        'update' => 'permission:vehicles.update',
        'destroy' => 'permission:vehicles.delete'
    ]);