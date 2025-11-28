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
        
        // Skip durante console commands senza DB (migrations, composer)
        if (app()->runningInConsole()) {
            return null;
        }
        
        // Prova a caricare il tenant dall'ID se disponibile
        $tenantId = app()->bound('currentTenantId') ? app('currentTenantId') : null;
        if ($tenantId) {
            try {
                self::$currentTenant = Tenant::find($tenantId);
            } catch (\Exception $e) {
                // DB non disponibile - ritorna null
                return null;
            }
            return self::$currentTenant;
        }
        
        return null;
    }

    /**
     * Ottiene l'ID del tenant corrente (senza query DB)
     */
    public static function getTenantId(): ?int
    {
        // Prima prova a ottenere l'ID senza fare query
        if (app()->bound('currentTenantId')) {
            $id = app('currentTenantId');
            if ($id !== null) {
                return (int) $id;
            }
        }
        
        // Poi prova dal tenant caricato (se già in memoria)
        if (self::$currentTenant !== null) {
            return self::$currentTenant->id;
        }
        
        // Skip query durante console
        if (app()->runningInConsole()) {
            return null;
        }
        
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











