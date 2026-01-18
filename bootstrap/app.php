<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend(\Illuminate\Http\Middleware\HandleCors::class);
        $middleware->web(prepend: [
            \App\Http\Middleware\EncryptCookies::class, // reemplaza el cifrado por el tuyo
        ]);

        $middleware->api(append: [
            // \App\Http\Middleware\JWTFromCookie::class, 
            // \App\Http\Middleware\SupabaseAuth::class, 
        ]);

        $middleware->alias([
            'supabase.auth' => \App\Http\Middleware\SupabaseAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
