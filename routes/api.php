<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RoleAndPermissionController;
use App\Http\Controllers\Profile\ClientController;
use App\Http\Controllers\Profile\OwnerController;
use App\Http\Controllers\Profile\AdminController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceTypeController;

use Illuminate\Auth\Events\Login;

Route::post('/login', [LoginController::class, 'login'])->name('login');

Route::middleware(['auth:api'])->group(function () {
    Route::get('/me', [LoginController::class, 'me']);
    Route::post('/logout', [LoginController::class, 'logout']);

    Route::prefix('clients')->group(function () {
        Route::get('/', [ClientController::class, 'index']);
        Route::post('/', [ClientController::class, 'store']);
        Route::patch('/{id}', [ClientController::class, 'update']);
        Route::delete('/{id}', [ClientController::class, 'destroy']);
    });

    Route::prefix('owners')->group(function () {
        Route::post('/', [OwnerController::class, 'store']);
        Route::get('/', [OwnerController::class, 'index']);
        Route::patch('{id}', [OwnerController::class, 'update']);
        Route::delete('{id}', [OwnerController::class, 'destroy']);
    });

    Route::prefix('admins')->group(function () {
        Route::get('/', [AdminController::class, 'index']);
        Route::post('/', [AdminController::class, 'store']);
        Route::patch('/{id}', [AdminController::class, 'update']);
        Route::delete('/{id}', [AdminController::class, 'destroy']);
    });

    Route::prefix('vehicles')->group(function () {
        Route::get('/', [VehicleController::class, 'index']);
        Route::post('/', [VehicleController::class, 'store']);
        Route::patch('/{id}', [VehicleController::class, 'update']);
        Route::delete('/{id}', [VehicleController::class, 'destroy']);
    });

    Route::prefix('services')->group(function () {
        Route::get('/', [ServiceController::class, 'index']);
        Route::post('/', [ServiceController::class, 'store']);
        Route::post('/detail', [ServiceController::class, 'serviceDetailsStore']);
        Route::patch('/{id}', [ServiceController::class, 'update']);
        Route::delete('/{id}', [ServiceController::class, 'destroy']);
    });
    
     Route::prefix('services-type')->group(function () {
        Route::get('/', [ServiceTypeController::class, 'index']);
        Route::post('/', [ServiceTypeController::class, 'store']);
        Route::patch('/{id}', [ServiceTypeController::class, 'update']);
        Route::delete('/{id}', [ServiceTypeController::class, 'destroy']);
    });

    // Roles y Permisos
    Route::post('/roles/create', [RoleAndPermissionController::class, 'createRoles']);
    Route::post('/permissions/create', [RoleAndPermissionController::class, 'createPermissions']);
    Route::get('/roles', [RoleAndPermissionController::class, 'getRolesubro']);
    Route::get('/permissions', [RoleAndPermissionController::class, 'getPermissions']);
    Route::post('/roles/assign-permissions', [RoleAndPermissionController::class, 'assignPermissionsToRole']);
    Route::post('/roles/assign-user', [RoleAndPermissionController::class, 'assignRoleToUser']);
});
