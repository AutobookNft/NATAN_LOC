<?php

declare(strict_types=1);

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * @package App\Scopes
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-10-31
 * @purpose Global Scope per isolamento tenant (single-database multi-tenancy)
 *
 * Questo scope applica automaticamente WHERE tenant_id = X a tutte le query
 * dei modelli che lo usano, garantendo isolamento completo tra tenant.
 */
class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param Builder $builder
     * @param Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = \App\Helpers\TenancyHelper::getTenantId()
            ?? request()->header('X-Tenant-ID')
            ?? (\Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::user()?->tenant_id : null);

        if ($tenantId) {
            $builder->where($model->getTable() . '.tenant_id', $tenantId);
        }
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param Builder $builder
     * @return void
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('withoutTenantScope', function (Builder $builder) use ($builder) {
            return $builder->withoutGlobalScope(self::class);
        });

        $builder->macro('withAllTenants', function (Builder $builder) use ($builder) {
            return $builder->withoutGlobalScope(self::class);
        });
    }
}
