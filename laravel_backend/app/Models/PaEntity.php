<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @package App\Models
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-10-31
 * @purpose Model per tenant (PA o Enterprise)
 *
 * Rappresenta un tenant (ente PA o azienda) nel sistema multi-tenant.
 * NATAN supporta sia Pubblica Amministrazione che Aziende Private.
 */
class PaEntity extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pa_entities';

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
}
