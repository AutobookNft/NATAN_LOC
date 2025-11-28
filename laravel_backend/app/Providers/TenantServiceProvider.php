<?php

declare(strict_types=1);

namespace App\Providers;

use App\Resolvers\TenantResolver;
use Illuminate\Support\ServiceProvider;

/**
 * @package App\Providers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.1.0 (NATAN_LOC)
 * @date 2025-11-28
 * @purpose Service Provider per gestione tenant resolution e injection nel container
 * 
 * CONTESTO: /home/fabio/NATAN_LOC
 * PERCORSO FILE: app/Providers/TenantServiceProvider.php
 * 
 * IMPORTANTE: Questo provider NON fa query DB in register() o boot().
 * La risoluzione del tenant avviene SOLO tramite il middleware InitializeTenancy
 * o su richiesta esplicita via TenantResolver::resolve().
 * 
 * Questo approccio evita errori durante:
 * - composer install/update
 * - artisan commands (migrate, cache:clear, etc.)
 * - queue workers prima dell'inizializzazione
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
        // IMPORTANTE: Non fare MAI query DB in register()!
        // Il DB potrebbe non essere ancora configurato (es: durante composer install, artisan)
        // La risoluzione del tenant avviene SOLO nel boot() o via middleware
        
        // Registra null come placeholder - sarà aggiornato nel boot() o dal middleware
        $this->app->instance('currentTenantId', null);
    }

    /**
     * Bootstrap services.
     * 
     * La risoluzione del tenant avviene qui, dopo che il DB è stato configurato.
     * Tuttavia, per le request HTTP, è meglio lasciar fare al middleware.
     * 
     * @return void
     */
    public function boot(): void
    {
        // La risoluzione effettiva del tenant avviene:
        // 1. Per request HTTP: nel middleware InitializeTenancy (dopo auth)
        // 2. Per console: su richiesta via TenantResolver::resolve()
        // 
        // NON facciamo risoluzione automatica qui per evitare query
        // premature durante artisan commands o composer scripts.
    }
}
