<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * @Oracode Model: NATAN Document (PA Document in Project)
 * 🎯 Purpose: Proxy for Egi model with context='pa_document'
 * 🛡️ Privacy: Filters only PA documents
 * 🧱 Core Logic: Documents uploaded to NATAN projects
 * 
 * @package App\Models
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC - Documents Integration FEGI)
 * @date 2025-11-21
 * @purpose Unified model using FEGI egis table with context filtering
 * 
 * @property int $id
 * @property int $collection_id (FK to collections, maps to NatanProject)
 * @property int|null $user_id (uploader)
 * @property int|null $tenant_id
 * @property string $title
 * @property string|null $description
 * @property string $original_filename
 * @property string $mime_type
 * @property int $size_bytes
 * @property string $pa_file_path (storage path)
 * @property string $document_status (pending|processing|ready|failed)
 * @property string|null $document_error_message
 * @property array|null $processing_metadata
 * @property \Illuminate\Support\Carbon|null $document_processed_at
 * @property string $context (always 'pa_document')
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read NatanProject $project
 * @property-read User $uploader
 * @property-read \Illuminate\Database\Eloquent\Collection|ProjectDocumentChunk[] $chunks
 */
class NatanDocument extends Model
{
    /**
     * Database table
     */
    protected $table = 'egis';
    
    /**
     * Fillable attributes
     */
    protected $fillable = [
        'collection_id',
        'user_id',
        'tenant_id',
        'title',
        'description',
        'original_filename',
        'mime_type',
        'size_bytes',
        'pa_file_path',
        'document_status',
        'document_error_message',
        'processing_metadata',
        'document_processed_at',
        'context',
    ];
    
    /**
     * Casts
     */
    protected $casts = [
        'processing_metadata' => 'array',
        'size_bytes' => 'integer',
        'document_processed_at' => 'datetime',
    ];
    
    /**
     * Default attributes
     */
    protected $attributes = [
        'context' => 'pa_document',
        'document_status' => 'pending',
    ];
    
    /**
     * Boot method: apply global scope
     */
    protected static function booted(): void
    {
        // CRITICAL: Filter ONLY pa_document context
        static::addGlobalScope('pa_document', function (Builder $builder) {
            $builder->where('context', 'pa_document');
        });
        
        // Auto-set context on creation
        static::creating(function (NatanDocument $document) {
            $document->context = 'pa_document';
        });
    }
    
    /**
     * Get project (collection)
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(NatanProject::class, 'collection_id');
    }
    
    /**
     * Get uploader user
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Get document chunks (embeddings)
     */
    public function chunks(): HasMany
    {
        return $this->hasMany(ProjectDocumentChunk::class, 'egi_id');
    }
    
    /**
     * Accessor: filename → original_filename
     */
    public function getFilenameAttribute(): ?string
    {
        return $this->attributes['original_filename'] ?? null;
    }
    
    /**
     * Accessor: file_path → pa_file_path
     */
    public function getFilePathAttribute(): ?string
    {
        return $this->attributes['pa_file_path'] ?? null;
    }
    
    /**
     * Mutator: file_path → pa_file_path
     */
    public function setFilePathAttribute(?string $value): void
    {
        $this->attributes['pa_file_path'] = $value;
    }
    
    /**
     * Accessor: status → document_status
     */
    public function getStatusAttribute(): ?string
    {
        return $this->attributes['document_status'] ?? null;
    }
    
    /**
     * Mutator: status → document_status
     */
    public function setStatusAttribute(?string $value): void
    {
        $this->attributes['document_status'] = $value;
    }
    
    /**
     * Accessor: error_message → document_error_message
     */
    public function getErrorMessageAttribute(): ?string
    {
        return $this->attributes['document_error_message'] ?? null;
    }
    
    /**
     * Mutator: error_message → document_error_message
     */
    public function setErrorMessageAttribute(?string $value): void
    {
        $this->attributes['document_error_message'] = $value;
    }
    
    /**
     * Accessor: processed_at → document_processed_at
     */
    public function getProcessedAtAttribute(): ?\Illuminate\Support\Carbon
    {
        return $this->attributes['document_processed_at'] ?? null;
    }
    
    /**
     * Mutator: processed_at → document_processed_at
     */
    public function setProcessedAtAttribute($value): void
    {
        $this->attributes['document_processed_at'] = $value;
    }
    
    /**
     * Scope: only ready documents
     */
    public function scopeReady(Builder $query): Builder
    {
        return $query->where('document_status', 'ready');
    }
    
    /**
     * Scope: for specific project
     */
    public function scopeForProject(Builder $query, NatanProject $project): Builder
    {
        return $query->where('collection_id', $project->id);
    }
    
    /**
     * Scope: for specific tenant
     */
    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }
    
    /**
     * Check if document is ready
     */
    public function isReady(): bool
    {
        return $this->document_status === 'ready';
    }
    
    /**
     * Check if document is processing
     */
    public function isProcessing(): bool
    {
        return $this->document_status === 'processing';
    }
    
    /**
     * Check if document failed
     */
    public function isFailed(): bool
    {
        return $this->document_status === 'failed';
    }
    
    /**
     * Mark document as processing
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'document_status' => 'processing',
            'document_error_message' => null,
        ]);
    }
    
    /**
     * Mark document as ready
     */
    public function markAsReady(array $metadata = []): void
    {
        $this->update([
            'document_status' => 'ready',
            'document_processed_at' => now(),
            'processing_metadata' => array_merge($this->processing_metadata ?? [], $metadata),
            'document_error_message' => null,
        ]);
    }
    
    /**
     * Mark document as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'document_status' => 'failed',
            'document_error_message' => $errorMessage,
        ]);
    }
}

