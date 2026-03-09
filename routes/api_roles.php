<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RoleAndPermissionController;

Route::prefix('roles')->group(function () {

    Route::get('/', [RoleAndPermissionController::class, 'getRoles'])
        ->middleware('permission:roles.view');

    Route::post('/create', [RoleAndPermissionController::class, 'createRoles'])
        ->middleware('permission:roles.create');

    Route::post('/assign-permissions', [RoleAndPermissionController::class, 'assignPermissionsToRole'])
        ->middleware('permission:roles.update');

    Route::post('/assign-user', [RoleAndPermissionController::class, 'assignRoleToUser'])
        ->middleware('permission:roles.update');

    // Permisos por rol
    Route::get('/{role}/permissions', [RoleAndPermissionController::class, 'getPermissionsByRole']);
});

Route::prefix('permissions')->group(function () {

    Route::get('/', [RoleAndPermissionController::class, 'getPermissions'])
        ->middleware('permission:permissions.view');

    Route::post('/create', [RoleAndPermissionController::class, 'createPermissions'])
        ->middleware('permission:permissions.create');
});
