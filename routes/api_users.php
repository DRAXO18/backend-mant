<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Profile\ClientController;
use App\Http\Controllers\Profile\OwnerController;
use App\Http\Controllers\Profile\AdminController;

Route::apiResource('clients', ClientController::class)
    ->parameters(['clients' => 'id'])
    ->middleware([
        'index' => 'permission:users.client.view',
        'store' => 'permission:users.client.create',
        'update' => 'permission:users.client.update',
        'destroy' => 'permission:users.client.delete'
    ]);

Route::apiResource('owners', OwnerController::class)
    ->parameters(['owners' => 'id'])
    ->middleware([
        'index' => 'permission:users.owner.view',
        'store' => 'permission:users.owner.create',
        'update' => 'permission:users.owner.update',
        'destroy' => 'permission:users.owner.delete'
    ]);

Route::apiResource('admins', AdminController::class)
    ->parameters(['admins' => 'id'])
    ->middleware([
        'index' => 'permission:users.admin.view',
        'store' => 'permission:users.admin.create',
        'update' => 'permission:users.admin.update',
        'destroy' => 'permission:users.admin.delete'
    ]);