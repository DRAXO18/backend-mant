<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RoleAndPermissionController;

use Illuminate\Auth\Events\Login;

Route::post('/login', [LoginController::class, 'login'])->name('login');

Route::middleware(['auth:api'])->group(function () {
    Route::get('/me', [LoginController::class, 'me']);
    Route::post('/logout', [LoginController::class, 'logout']);
});

Route::prefix('glp')
    ->middleware(['auth:api'])
    ->group(function () {

        // Roles y Permisos
        Route::post('/roles/create', [RoleAndPermissionController::class, 'createRoles']);
        Route::post('/permissions/create', [RoleAndPermissionController::class, 'createPermissions']);
        Route::get('/roles', [RoleAndPermissionController::class, 'getRolesRubro']);
        Route::get('/permissions', [RoleAndPermissionController::class, 'getPermissionsRubro']);
        Route::post('/roles/assign-permissions', [RoleAndPermissionController::class, 'assignPermissionsToRole']);
        Route::post('/roles/assign-user', [RoleAndPermissionController::class, 'assignRoleToUser']);
    });

