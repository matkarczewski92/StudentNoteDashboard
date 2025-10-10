<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Aliasowane middleware (dostępne w trasach jako 'role', 'auth', itd.)
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withProviders([
        \App\Providers\AuthServiceProvider::class, // ✅ rejestracja gate'ów i polityk
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
