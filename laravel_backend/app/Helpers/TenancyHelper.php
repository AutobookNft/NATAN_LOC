<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\PaEntity;

/**
 * @package App\Helpers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-10-31
 * @purpose Helper per gestione tenancy single-database
 */
class TenancyHelper
{
    private static ?PaEntity $currentTenant = null;

    /**
     * Imposta il tenant corrente
     */
    public static function setTenant(?PaEntity $tenant): void
    {
        self::$currentTenant = $tenant;
        app()->instance('current_tenant', $tenant);
    }

    /**
     * Ottiene il tenant corrente
     */
    public static function getTenant(): ?PaEntity
    {
        return self::$currentTenant ?? app('current_tenant');
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

