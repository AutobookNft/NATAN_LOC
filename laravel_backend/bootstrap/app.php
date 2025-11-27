<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: '', // Disabilita il prefisso 'api/' automatico perché le route API già hanno il prefisso corretto
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenancy' => \App\Http\Middleware\InitializeTenancy::class,
            'superadmin' => \App\Http\Middleware\EnsureSuperadmin::class,
            'natan.access' => \App\Http\Middleware\EnsureNatanAccess::class,
        ]);

        // Registra il middleware tenancy nel gruppo web per inizializzare il tenant
        // Viene eseguito dopo l'autenticazione, quindi Auth::user() è disponibile
        // Usiamo 'web' group invece di append globale per avere più controllo sull'ordine
        $middleware->web(append: [
            \App\Http\Middleware\InitializeTenancy::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
