<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Tenant;

/**
 * @package App\Helpers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-02
 * @purpose Helper per gestione tenancy single-database
 */
class TenancyHelper
{
    private static ?Tenant $currentTenant = null;

    /**
     * Imposta il tenant corrente
     */
    public static function setTenant(?Tenant $tenant): void
    {
        self::$currentTenant = $tenant;
        if ($tenant) {
            app()->instance('currentTenantId', $tenant->id);
        }
    }

    /**
     * Ottiene il tenant corrente
     */
    public static function getTenant(): ?Tenant
    {
        if (self::$currentTenant !== null) {
            return self::$currentTenant;
        }
        
        // Prova a caricare il tenant dall'ID se disponibile
        $tenantId = app()->bound('currentTenantId') ? app('currentTenantId') : null;
        if ($tenantId) {
            self::$currentTenant = Tenant::find($tenantId);
            return self::$currentTenant;
        }
        
        return null;
    }

    /**
     * Ottiene l'ID del tenant corrente
     */
    public static function getTenantId(): ?int
    {
        return self::getTenant()?->id;
    }

    /**
     * Verifica se c'è un tenant attivo
     */
    public static function hasTenant(): bool
    {
        return self::getTenant() !== null;
    }
}











