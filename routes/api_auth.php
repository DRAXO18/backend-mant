<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout']);
Route::middleware('auth:api')->get('/me', [LoginController::class, 'me']);

Route::get('/speed-test', function () {
    return ['ok' => true];
});