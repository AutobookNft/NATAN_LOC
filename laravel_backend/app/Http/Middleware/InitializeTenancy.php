<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Helpers\TenancyHelper;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package App\Http\Middleware
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-02
 * @purpose Middleware per inizializzazione tenancy single-database
 *
 * Rileva il tenant da:
 * 1. Subdomain (es: tenant1.natan.florenceegi.com)
 * 2. Header X-Tenant-ID
 * 3. User autenticato (tenant_id)
 * 4. Query parameter ?tenant_id (solo per sviluppo)
 */
class InitializeTenancy
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->detectTenant($request);

        if ($tenant) {
            TenancyHelper::setTenant($tenant);
            // Inietta anche direttamente nel container per compatibilità con Global Scopes
            app()->instance('currentTenantId', $tenant->id);
            
            // Debug log (solo in development)
            if (config('app.debug')) {
                \Log::debug('[InitializeTenancy] Tenant rilevato e iniettato', [
                    'tenant_id' => $tenant->id,
                    'tenant_name' => $tenant->name,
                    'host' => $request->getHost(),
                ]);
            }
        } else {
            // Prova a risolvere il tenant usando TenantResolver come fallback
            $tenantId = \App\Resolvers\TenantResolver::resolve();
            if ($tenantId) {
                app()->instance('currentTenantId', $tenantId);
                
                if (config('app.debug')) {
                    \Log::debug('[InitializeTenancy] Tenant risolto tramite TenantResolver', [
                        'tenant_id' => $tenantId,
                        'host' => $request->getHost(),
                    ]);
                }
            } else {
                // Assicurati che currentTenantId sia null se non risolto
                app()->instance('currentTenantId', null);
                
                if (config('app.debug')) {
                    \Log::debug('[InitializeTenancy] Nessun tenant rilevato', [
                        'host' => $request->getHost(),
                        'auth_check' => Auth::check(),
                        'user_id' => Auth::id(),
                    ]);
                }
            }
        }

        return $next($request);
    }

    /**
     * Detect tenant from request
     */
    private function detectTenant(Request $request): ?Tenant
    {
        // 1. Subdomain detection (es: tenant1.natan.florenceegi.com)
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0] ?? null;

        if ($subdomain && $subdomain !== 'www' && $subdomain !== 'natan' && $subdomain !== 'localhost' && $subdomain !== '127.0.0.1') {
            $tenant = Tenant::where('slug', $subdomain)->first();
            if ($tenant) {
                return $tenant;
            }
        }

        // 2. Header X-Tenant-ID (API calls)
        $tenantId = $request->header('X-Tenant-ID');
        if ($tenantId) {
            $tenant = Tenant::find($tenantId);
            if ($tenant) {
                return $tenant;
            }
        }

        // 3. User authenticated (tenant_id from user)
        // ECCEZIONE: superadmin può usare tenant dalla sessione se selezionato
        if (Auth::check()) {
            $user = Auth::user();
            
            // Se superadmin e ha tenant_id in sessione (tenant selezionato), usa quello
            if ($user->hasRole('superadmin') && $request->session()->has('current_tenant_id')) {
                $sessionTenantId = $request->session()->get('current_tenant_id');
                $tenant = Tenant::find($sessionTenantId);
                if ($tenant) {
                    return $tenant;
                }
            }
            
            // Altrimenti usa il tenant_id dell'utente (come prima)
            if ($user->tenant_id) {
                $tenant = Tenant::find($user->tenant_id);
                if ($tenant) {
                    return $tenant;
                }
            }
        }

        // 4. Query parameter (solo per sviluppo - rimuovere in produzione)
        if (app()->environment('local') || app()->environment('testing')) {
            $tenantId = $request->query('tenant_id');
            if ($tenantId) {
                $tenant = Tenant::find($tenantId);
                if ($tenant) {
                    return $tenant;
                }
            }
        }

        return null;
    }
}