<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::prefix('kardex')->group(function () {

    Route::get(
        'productos',
        [ProductController::class, 'index']
    )->middleware('permission:products.view');

    Route::post(
        'productos',
        [ProductController::class, 'store']
    )->middleware('permission:products.create');

});