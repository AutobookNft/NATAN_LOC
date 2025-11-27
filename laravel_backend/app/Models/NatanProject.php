<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * @Oracode Model: NATAN Project (PA Document Management)
 * 🎯 Purpose: Proxy for Collection model with context='pa_project'
 * 🛡️ Privacy: Filters only PA project collections
 * 🧱 Core Logic: Projects for document management in NATAN
 * 
 * @package App\Models
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC - Projects Integration FEGI)
 * @date 2025-11-21
 * @purpose Unified model using FEGI collections table with context filtering
 * 
 * @property int $id
 * @property int $creator_id (maps to collections.creator_id)
 * @property string $name (maps to collections.collection_name)
 * @property string|null $description
 * @property string $icon
 * @property string $color
 * @property array|null $settings
 * @property bool $is_active
 * @property string $context (always 'pa_project')
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read User $owner
 * @property-read \Illuminate\Database\Eloquent\Collection|NatanDocument[] $documents
 */
class NatanProject extends Model
{
    /**
     * Database table
     */
    protected $table = 'collections';
    
    /**
     * Fillable attributes
     */
    protected $fillable = [
        'creator_id',
        'collection_name',
        'description',
        'icon',
        'color',
        'settings',
        'is_active',
        'context',
    ];
    
    /**
     * Casts
     */
    protected $casts = [
        // 'settings' => 'array', // Handled by accessor for defaults
        'is_active' => 'boolean',
    ];
    
    /**
     * Appends (attributes to include in JSON serialization)
     */
    protected $appends = [
        'name',
    ];
    
    /**
     * Default attributes
     */
    protected $attributes = [
        'context' => 'pa_project',
        'icon' => 'folder_open',
        'color' => '#1B365D',
        'is_active' => true,
        'settings' => '{"max_documents":50,"max_size_mb":10,"auto_embed":true,"priority_rag":true,"allowed_types":["pdf","docx","txt","csv","xlsx","md"]}',
    ];
    
    /**
     * Boot method: apply global scope
     */
    protected static function booted(): void
    {
        // CRITICAL: Filter ONLY pa_project context
        static::addGlobalScope('pa_project', function (Builder $builder) {
            $builder->where('context', 'pa_project');
        });
        
        // Auto-set context on creation
        static::creating(function (NatanProject $project) {
            $project->context = 'pa_project';
        });
    }
    
    /**
     * Get project owner (PA user)
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
    
    /**
     * Get project documents (EGIs with context='pa_document')
     */
    public function documents(): HasMany
    {
        return $this->hasMany(NatanDocument::class, 'collection_id');
    }
    
    /**
     * Accessor: name → collection_name
     */
    public function getNameAttribute(): ?string
    {
        return $this->attributes['collection_name'] ?? null;
    }
    
    /**
     * Mutator: name → collection_name
     */
    public function setNameAttribute(?string $value): void
    {
        $this->attributes['collection_name'] = $value;
    }
    
    /**
     * Accessor: settings with default values
     * Ensures settings is always an array with defaults
     */
    public function getSettingsAttribute($value): array
    {
        $defaults = [
            'max_documents' => 50,
            'max_size_mb' => 10,
            'auto_embed' => true,
            'priority_rag' => true,
            'allowed_types' => ['pdf', 'docx', 'txt', 'csv', 'xlsx', 'md'],
        ];
        
        // If settings is null, return defaults
        if ($value === null) {
            return $defaults;
        }
        
        // If settings is a JSON string, decode it
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = $decoded ?: [];
        }
        
        // Merge with defaults to ensure all keys exist
        return array_merge($defaults, $value ?: []);
    }
    
    /**
     * Scope: for specific user
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('creator_id', $user->id);
    }
    
    /**
     * Get documents count
     */
    public function getDocumentsCountAttribute(): int
    {
        return $this->documents()->count();
    }
    
    /**
     * Get ready documents count
     */
    public function getReadyDocumentsCountAttribute(): int
    {
        return $this->documents()->where('document_status', 'ready')->count();
    }
    
    /**
     * Check if project can accept more documents
     */
    public function canAddDocument(): bool
    {
        $maxDocuments = $this->settings['max_documents'] ?? 50;
        return $this->documents()->count() < $maxDocuments;
    }
    
    /**
     * Get max file size in bytes
     */
    public function getMaxFileSizeBytes(): int
    {
        $maxSizeMb = $this->settings['max_size_mb'] ?? 10;
        return $maxSizeMb * 1024 * 1024;
    }
    
    /**
     * Get allowed file types
     */
    public function getAllowedFileTypes(): array
    {
        return $this->settings['allowed_types'] ?? ['pdf', 'docx', 'txt', 'csv', 'xlsx', 'md'];
    }
}

