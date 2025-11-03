<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * @package App\Traits
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-02
 * @purpose Global Scope per isolamento automatico tenant
 * 
 * CONTESTO: /home/fabio/NATAN_LOC
 * PERCORSO FILE: app/Traits/TenantScoped.php
 * 
 * Trait che fornisce:
 * - Global Scope automatico per filtrare query per tenant_id
 * - Auto-set tenant_id in creating() usando TenantResolver
 * 
 * Applicare a tutti i modelli che necessitano isolamento tenant.
 */

/**
 * Global Scope per filtrare automaticamente per tenant_id
 */
class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder
     * 
     * @param Builder $builder Query builder
     * @param Model $model Model instance
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Ottieni tenant_id risolto
        $tenantId = app()->bound('currentTenantId') ? app('currentTenantId') : null;
        
        if ($tenantId !== null) {
            $table = $model->getTable();
            $builder->where("{$table}.tenant_id", $tenantId);
        }
    }
}

/**
 * Trait per modelli tenant-scoped
 * 
 * Usa questo trait nei modelli che necessitano isolamento tenant:
 * 
 * ```php
 * use App\Traits\TenantScoped;
 * 
 * class MyModel extends Model {
 *     use TenantScoped;
 * }
 * ```
 */
trait TenantScoped
{
    /**
     * Boot the tenant scoped trait
     * 
     * Aggiunge il Global Scope e auto-imposta tenant_id in creating()
     * 
     * @return void
     */
    protected static function bootTenantScoped(): void
    {
        // Aggiungi Global Scope per filtrare automaticamente per tenant_id
        static::addGlobalScope(new TenantScope);
        
        // Auto-set tenant_id quando si crea un nuovo record
        static::creating(function ($model) {
            if (!isset($model->tenant_id) || $model->tenant_id === null) {
                // Prova a ottenere tenant_id dal container o resolver
                $tenantId = app()->bound('currentTenantId') 
                    ? app('currentTenantId') 
                    : \App\Resolvers\TenantResolver::resolve();
                
                if ($tenantId !== null) {
                    $model->tenant_id = $tenantId;
                }
            }
        });
    }
    
    /**
     * Remove tenant scope temporarily for a query
     * 
     * Utile per query admin che devono vedere tutti i tenant
     * 
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }
}






