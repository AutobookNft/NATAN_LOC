<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\NatanDocument;

/**
 * ProjectDocumentChunk Model
 *
 * Document chunks with embeddings for semantic search (RAG)
 * NOW UNIFIED: Works with both FEGI egis and NATAN documents
 *
 * @package App\Models
 * @author Padmin D. Curtis (AI Partner OS3.0)
 * @version 2.0.0 (FlorenceEGI - Unified with NATAN)
 * @date 2025-11-21
 * @purpose Store document chunks with vector embeddings for RAG search (unified FEGI/NATAN)
 *
 * @property int $id
 * @property int $egi_id (FK to egis table)
 * @property int $chunk_index
 * @property string $chunk_text
 * @property array|null $embedding
 * @property string $embedding_model
 * @property int|null $tokens_count
 * @property int|null $page_number
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read Egi $egi
 * @property-read NatanDocument $document (alias for NATAN context)
 */
class ProjectDocumentChunk extends Model {
    use HasFactory;

    protected $fillable = [
        'egi_id',
        'chunk_index',
        'chunk_text',
        'embedding',
        'embedding_model',
        'tokens_count',
        'page_number',
        'metadata',
    ];

    protected $casts = [
        'embedding' => 'array',
        'metadata' => 'array',
        'chunk_index' => 'integer',
        'tokens_count' => 'integer',
        'page_number' => 'integer',
    ];

    protected $attributes = [
        'embedding_model' => 'text-embedding-ada-002',
    ];

    /**
     * Get parent EGI (universal relation)
     */
    public function egi(): BelongsTo {
        return $this->belongsTo(NatanDocument::class, 'egi_id');
    }

    /**
     * Get parent document (alias for NATAN context)
     *
     * This provides backward compatibility and semantic clarity
     * when working with NATAN documents (egis with context='pa_document')
     */
    public function document(): BelongsTo {
        return $this->belongsTo(\App\Models\NatanDocument::class, 'egi_id');
    }

    /**
     * Scope: chunks with embeddings
     */
    public function scopeWithEmbedding($query) {
        return $query->whereNotNull('embedding');
    }

    /**
     * Scope: order by chunk index
     */
    public function scopeOrdered($query) {
        return $query->orderBy('chunk_index');
    }

    /**
     * Check if chunk has embedding
     */
    public function hasEmbedding(): bool {
        return !is_null($this->embedding) && is_array($this->embedding) && count($this->embedding) > 0;
    }

    /**
     * Get embedding dimensions
     */
    public function getEmbeddingDimensionsAttribute(): int {
        return $this->hasEmbedding() ? count($this->embedding) : 0;
    }

    /**
     * Calculate cosine similarity with another embedding
     *
     * @param array $otherEmbedding Vector to compare with
     * @return float Similarity score (0.0 to 1.0)
     */
    public function cosineSimilarity(array $otherEmbedding): float {
        if (!$this->hasEmbedding()) {
            return 0.0;
        }

        $embedding = $this->embedding;

        if (count($embedding) !== count($otherEmbedding)) {
            throw new \InvalidArgumentException('Vectors must have same dimension');
        }

        $dotProduct = 0.0;
        $magnitude1 = 0.0;
        $magnitude2 = 0.0;

        for ($i = 0; $i < count($embedding); $i++) {
            $dotProduct += $embedding[$i] * $otherEmbedding[$i];
            $magnitude1 += $embedding[$i] * $embedding[$i];
            $magnitude2 += $otherEmbedding[$i] * $otherEmbedding[$i];
        }

        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);

        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0.0;
        }

        return $dotProduct / ($magnitude1 * $magnitude2);
    }

    /**
     * Get chunk preview (first 100 chars)
     */
    public function getPreviewAttribute(): string {
        return \Illuminate\Support\Str::limit($this->chunk_text, 100);
    }
}