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
     * Nota: Durante le migration (artisan migrate) o in console, la risoluzione
     * del tenant viene saltata e viene registrato null, per evitare errori
     * quando il sistema non è ancora completamente inizializzato.
     * 
     * @return void
     */
    public function register(): void
    {
        $tenantId = null;
        
        // Salta la risoluzione del tenant durante migration/console per evitare errori
        // Il tenant sarà risolto on-demand quando necessario via TenantResolver::resolve()
        if ($this->app->runningInConsole() && !$this->app->runningUnitTests()) {
            // In console, controlla se siamo in una richiesta HTTP (es: queue worker con HTTP context)
            // Altrimenti salta la risoluzione (es: durante migration)
            if (!app()->bound('request') || request() === null) {
                // Durante migration, non c'è request disponibile
                $this->app->instance('currentTenantId', null);
                return;
            }
        }
        
        // Prova a risolvere il tenant solo se request() è disponibile
        try {
            if (app()->bound('request') && request() !== null) {
                $tenantId = TenantResolver::resolve();
            }
        } catch (\Exception $e) {
            // Se la risoluzione fallisce (es: database non ancora migrato), usa null
            $tenantId = null;
        }
        
        // Registra nel container come singleton per essere accessibile ovunque
        $this->app->instance('currentTenantId', $tenantId);
        
        // Facilita l'accesso via helper se necessario
        if ($tenantId !== null && !$this->app->runningInConsole()) {
            // Log per debug (rimuovere in produzione se necessario)
            if (config('app.debug')) {
                try {
                    \Log::debug('[Tenancy] Tenant risolto', [
                        'tenant_id' => $tenantId,
                        'host' => request()?->getHost(),
                        'user_id' => auth()->id(),
                    ]);
                } catch (\Exception $e) {
                    // Ignora errori di logging durante bootstrap
                }
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
