<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Helpers\TenancyHelper;
use App\Models\PaEntity;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package App\Http\Middleware
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-10-31
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
        }

        return $next($request);
    }

    /**
     * Detect tenant from request
     */
    private function detectTenant(Request $request): ?PaEntity
    {
        // 1. Subdomain detection (es: tenant1.natan.florenceegi.com)
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0] ?? null;

        if ($subdomain && $subdomain !== 'www' && $subdomain !== 'natan' && $subdomain !== 'localhost' && $subdomain !== '127.0.0.1') {
            $tenant = PaEntity::where('slug', $subdomain)->first();
            if ($tenant) {
                return $tenant;
            }
        }

        // 2. Header X-Tenant-ID (API calls)
        $tenantId = $request->header('X-Tenant-ID');
        if ($tenantId) {
            $tenant = PaEntity::find($tenantId);
            if ($tenant) {
                return $tenant;
            }
        }

        // 3. User authenticated (tenant_id from user)
        if (Auth::check() && Auth::user()->tenant_id) {
            $tenant = PaEntity::find(Auth::user()->tenant_id);
            if ($tenant) {
                return $tenant;
            }
        }

        // 4. Query parameter (solo per sviluppo - rimuovere in produzione)
        if (app()->environment('local') || app()->environment('testing')) {
            $tenantId = $request->query('tenant_id');
            if ($tenantId) {
                $tenant = PaEntity::find($tenantId);
                if ($tenant) {
                    return $tenant;
                }
            }
        }

        return null;
    }
}