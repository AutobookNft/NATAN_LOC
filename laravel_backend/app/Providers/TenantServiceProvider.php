<?php

declare(strict_types=1);

namespace App\Providers;

use App\Resolvers\TenantResolver;
use Illuminate\Support\ServiceProvider;

/**
 * @package App\Providers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-02
 * @purpose Service Provider per gestione tenant resolution e injection nel container
 * 
 * CONTESTO: /home/fabio/NATAN_LOC
 * PERCORSO FILE: app/Providers/TenantServiceProvider.php
 * 
 * Registra il tenant_id risolto nel container Laravel come 'currentTenantId',
 * rendendolo disponibile per Global Scopes, modelli, e servizi.
 */
class TenantServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     * 
     * Risolve il tenant_id usando TenantResolver e lo registra nel container
     * per essere utilizzato da Global Scopes e modelli.
     * 
     * @return void
     */
    public function register(): void
    {
        // Risolve il tenant_id usando TenantResolver
        $tenantId = TenantResolver::resolve();
        
        // Registra nel container come singleton per essere accessibile ovunque
        $this->app->instance('currentTenantId', $tenantId);
        
        // Facilita l'accesso via helper se necessario
        if ($tenantId !== null) {
            // Log per debug (rimuovere in produzione se necessario)
            if (config('app.debug')) {
                \Log::debug('[Tenancy] Tenant risolto', [
                    'tenant_id' => $tenantId,
                    'host' => request()->getHost(),
                    'user_id' => auth()->id(),
                ]);
            }
        }
    }

    /**
     * Bootstrap services.
     * 
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
