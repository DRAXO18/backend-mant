<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleLoginController;

Route::get('/auth/google/redirect', [GoogleLoginController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleLoginController::class, 'handleGoogleCallback']);
