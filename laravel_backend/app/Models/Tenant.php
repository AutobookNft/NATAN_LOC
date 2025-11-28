<?php

declare(strict_types=1);

namespace App\Models;

use FlorenceEgi\CoreModels\Traits\HasAggregations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @package App\Models
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.1.0 (NATAN_LOC)
 * @date 2025-11-28
 * @purpose Model per tenant (PA o Enterprise) con supporto Aggregazioni P2P
 *
 * Rappresenta un tenant (ente PA o azienda) nel sistema multi-tenant.
 * NATAN_LOC supporta sia Pubblica Amministrazione che Aziende Private.
 * 
 * CONTESTO: /home/fabio/NATAN_LOC
 * PERCORSO FILE: app/Models/Tenant.php
 * 
 * Il campo entity_type distingue tra:
 * - 'pa': Pubblica Amministrazione (Comuni, Regioni, Enti pubblici)
 * - 'company': Aziende private
 * - 'public_entity': Enti pubblici non territoriali
 * - 'other': Altri tipi di entità
 * 
 * AGGREGAZIONI P2P:
 * I tenant possono formare aggregazioni consensuali per condividere dati.
 * Usa HasAggregations trait per le funzionalità di aggregazione.
 */
class Tenant extends Model
{
    use HasFactory, SoftDeletes, HasAggregations;

    protected $table = 'tenants';

    protected $fillable = [
        'name',
        'slug',
        'code',
        'entity_type',
        'email',
        'phone',
        'address',
        'vat_number',
        'settings',
        'is_active',
        'trial_ends_at',
        'subscription_ends_at',
        'notes',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
    ];

    /**
     * Get users belonging to this tenant
     */
    public function users()
    {
        return $this->hasMany(User::class, 'tenant_id');
    }

    /**
     * Get documents (PA acts, contracts, reports, etc.) for this tenant
     */
    public function paActs()
    {
        return $this->hasMany(PaAct::class, 'tenant_id');
    }

    /**
     * Get conversations for this tenant
     */
    public function conversations()
    {
        return $this->hasMany(UserConversation::class, 'tenant_id');
    }

    /**
     * Get chat messages for this tenant
     */
    public function chatMessages()
    {
        return $this->hasMany(NatanChatMessage::class, 'tenant_id');
    }

    /**
     * Get user memories for this tenant
     */
    public function userMemories()
    {
        return $this->hasMany(NatanUserMemory::class, 'tenant_id');
    }
}


