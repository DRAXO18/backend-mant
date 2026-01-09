<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RoleAndPermissionController;
use App\Http\Controllers\Rubro\DashboardController;
use App\Http\Controllers\Rubro\CompanyController;
use App\Http\Controllers\Rubro\MonitoringController;
use Illuminate\Auth\Events\Login;

Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/register', [RegisterController::class, 'register'])->name('register');

Route::middleware(['auth:api'])->group(function () {
    Route::get('/me', [LoginController::class, 'me']);
    Route::post('/logout', [LoginController::class, 'logout']);
});

Route::prefix('rubro')
    ->middleware(['auth:api', 'panel:rubro'])
    ->group(function () {

        // Dashboard
        Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

        // Empresas
        Route::get('/companies', [CompanyController::class, 'index']);
        Route::get('/companies/{company}', [CompanyController::class, 'show']);
        Route::post('/companies/affiliation', [CompanyController::class, 'store']);
        Route::post('/companies/{company}/approve', [CompanyController::class, 'approve']);
        Route::post('/companies/{company}/reject', [CompanyController::class, 'reject']);
        Route::post('/companies/{company}/suspend', [CompanyController::class, 'suspend']);

        // Monitoreo
        Route::get('/technicians', [MonitoringController::class, 'technicians']);
        Route::get('/clients', [MonitoringController::class, 'clients']);

        // Roles y Permisos
        Route::post('/roles/create', [RoleAndPermissionController::class, 'createRoles']);
        Route::post('/permissions/create', [RoleAndPermissionController::class, 'createPermissions']);
        Route::get('/roles', [RoleAndPermissionController::class, 'getRolesRubro']);
        Route::get('/permissions', [RoleAndPermissionController::class, 'getPermissionsRubro']);
        Route::post('/roles/assign-permissions', [RoleAndPermissionController::class, 'assignPermissionsToRole']);
        Route::post('/roles/assign-user', [RoleAndPermissionController::class, 'assignRoleToUser']);
    });

Route::prefix('client')
    ->middleware(['auth:api', 'panel:client'])
    ->group(function () {
        // rutas cliente
    });

Route::prefix('company')
    ->middleware(['auth:api', 'panel:company'])
    ->group(function () {

        Route::post('/test', [LoginController::class, 'test']);

        Route::post('/roles/create', [RoleAndPermissionController::class, 'createRoles']);
        Route::post('/permissions/create', [RoleAndPermissionController::class, 'createPermissions']);
        Route::post('/roles/assign-permissions', [RoleAndPermissionController::class, 'assignPermissionsToRole']);
        Route::post('/roles/assign-user', [RoleAndPermissionController::class, 'assignRoleToUser']);
    });
