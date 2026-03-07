<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/api_auth.php';

Route::middleware(['auth:api'])->group(function () {

    require __DIR__.'/api_users.php';
    require __DIR__.'/api_vehicles.php';
    require __DIR__.'/api_services.php';
    require __DIR__.'/api_products.php';
    require __DIR__.'/api_roles.php';

});