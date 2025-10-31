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
        $middleware->alias([
            'tenancy' => \App\Http\Middleware\InitializeTenancy::class,
        ]);

        // Register tenancy middleware globally (or apply to specific routes)
        // Per ora non globale, applicare manualmente alle route che ne hanno bisogno
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
