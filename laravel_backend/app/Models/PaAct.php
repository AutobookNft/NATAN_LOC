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
 * @purpose Model per documenti PA (multi-tenant)
 *
 * Rappresenta un documento caricato nel sistema NATAN_LOC.
 * Supporta vari tipi di documento (PA acts, contracts, reports, etc.)
 */
class PaAct extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pa_acts';

    protected $fillable = [
        'tenant_id',
        'document_id',
        'protocol_number',
        'protocol_date',
        'title',
        'description',
        'document_type',
        'issuer',
        'department',
        'responsible',
        'act_category',
        'file_path',
        'file_hash',
        'file_mime',
        'file_size_bytes',
        'original_filename',
        'blockchain_anchored',
        'blockchain_txid',
        'blockchain_hash',
        'blockchain_network',
        'blockchain_anchored_at',
        'metadata',
        'status',
    ];

    protected $casts = [
        'protocol_date' => 'date',
        'blockchain_anchored' => 'boolean',
        'blockchain_anchored_at' => 'datetime',
        'metadata' => 'array',
        'file_size_bytes' => 'integer',
    ];

    /**
     * Boot the model
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    /**
     * Get tenant this document belongs to
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(PaEntity::class, 'tenant_id');
    }
}
