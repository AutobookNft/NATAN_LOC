<?php

declare(strict_types=1);

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @package App\Models
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-10-31
 * @purpose Model per sessioni conversazione NATAN (multi-tenant)
 */
class UserConversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_conversations';
    
    // CRITICAL: Use existing FlorenceEGI MariaDB database
    // According to hybrid_database_implementation.md: MySQL/MariaDB for relational data (shared with EGI)
    // The database name is "EGI" (as configured in EGI/.env: DB_DATABASE=EGI)
    // MongoDB is used for documents/acts/cognitive layer (natan_ai_core)
    protected $connection = 'mysql'; // Use 'mysql' connection pointing to existing EGI database

    protected $fillable = [
        'tenant_id',
        'user_id',
        'conversation_id',
        'title',
        'type',
        'persona',
        'config',
        'message_count',
        'total_tokens_used',
        'total_cost_eur',
        'total_latency_ms',
        'metadata',
        'last_message_at',
    ];

    protected $casts = [
        'config' => 'array',
        'metadata' => 'array',
        'total_cost_eur' => 'decimal:4',
        'last_message_at' => 'datetime',
        'message_count' => 'integer',
        'total_tokens_used' => 'integer',
        'total_latency_ms' => 'integer',
    ];

    /**
     * Boot the model
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    /**
     * Get tenant this conversation belongs to
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    /**
     * Get user this conversation belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
