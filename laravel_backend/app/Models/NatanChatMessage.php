<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * NatanChatMessage Model
 * 
 * Stores N.A.T.A.N. chat conversation history with multi-persona support
 * 
 * @property int $id
 * @property int $user_id
 * @property string $session_id
 * @property string $role
 * @property string $content
 * @property string|null $persona_id
 * @property string|null $persona_name
 * @property float|null $persona_confidence
 * @property string|null $persona_selection_method
 * @property string|null $persona_reasoning
 * @property array|null $persona_alternatives
 * @property array|null $rag_sources
 * @property int $rag_acts_count
 * @property string|null $rag_method
 * @property string|null $ai_model
 * @property int|null $tokens_input
 * @property int|null $tokens_output
 * @property int|null $response_time_ms
 * @property bool|null $was_helpful
 * @property string|null $user_feedback
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class NatanChatMessage extends Model {
    protected $table = 'natan_chat_messages';

    protected $fillable = [
        'user_id',
        'session_id',
        'role',
        'content',
        'reference_message_id', // Track elaborations/iterations
        'persona_id',
        'persona_name',
        'persona_confidence',
        'persona_selection_method',
        'persona_reasoning',
        'persona_alternatives',
        'rag_sources',
        'rag_acts_count',
        'rag_method',
        'web_search_enabled', // NEW v3.0
        'web_search_provider', // NEW v3.0
        'web_search_results', // NEW v3.0
        'web_search_count', // NEW v3.0
        'web_search_from_cache', // NEW v3.0
        'ai_model',
        'tokens_input',
        'tokens_output',
        'response_time_ms',
        'was_helpful',
        'user_feedback',
    ];

    protected $casts = [
        'persona_confidence' => 'float',
        'persona_alternatives' => 'array',
        'rag_sources' => 'array',
        'rag_acts_count' => 'integer',
        'web_search_enabled' => 'boolean', // NEW v3.0
        'web_search_results' => 'array', // NEW v3.0
        'web_search_count' => 'integer', // NEW v3.0
        'web_search_from_cache' => 'boolean', // NEW v3.0
        'tokens_input' => 'integer',
        'tokens_output' => 'integer',
        'response_time_ms' => 'integer',
        'was_helpful' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who sent/received this message
     */
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the original message this is elaborating on (if any)
     */
    public function referenceMessage(): BelongsTo {
        return $this->belongsTo(NatanChatMessage::class, 'reference_message_id');
    }

    /**
     * Get all elaborations of this message
     */
    public function elaborations() {
        return $this->hasMany(NatanChatMessage::class, 'reference_message_id');
    }

    /**
     * Scope to get messages from a specific session
     */
    public function scopeForSession($query, string $sessionId) {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope to get messages for a specific user
     */
    public function scopeForUser($query, int $userId) {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get only user messages
     */
    public function scopeUserMessages($query) {
        return $query->where('role', 'user');
    }

    /**
     * Scope to get only assistant messages
     */
    public function scopeAssistantMessages($query) {
        return $query->where('role', 'assistant');
    }

    /**
     * Scope to get messages by persona
     */
    public function scopeByPersona($query, string $personaId) {
        return $query->where('persona_id', $personaId);
    }

    /**
     * Scope to get recent messages (last N hours)
     */
    public function scopeRecent($query, int $hours = 24) {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Check if this is an assistant message
     */
    public function isAssistant(): bool {
        return $this->role === 'assistant';
    }

    /**
     * Check if this is a user message
     */
    public function isUser(): bool {
        return $this->role === 'user';
    }

    /**
     * Get persona display info
     */
    public function getPersonaInfo(): ?array {
        if (!$this->persona_id) {
            return null;
        }

        return [
            'id' => $this->persona_id,
            'name' => $this->persona_name,
            'confidence' => $this->persona_confidence,
            'method' => $this->persona_selection_method,
            'reasoning' => $this->persona_reasoning,
            'alternatives' => $this->persona_alternatives,
        ];
    }

    /**
     * Get RAG info
     */
    public function getRagInfo(): ?array {
        if ($this->rag_acts_count === 0) {
            return null;
        }

        return [
            'method' => $this->rag_method,
            'acts_count' => $this->rag_acts_count,
            'sources' => $this->rag_sources,
        ];
    }

    /**
     * Get API usage stats
     */
    public function getApiStats(): ?array {
        if (!$this->ai_model) {
            return null;
        }

        return [
            'model' => $this->ai_model,
            'tokens_input' => $this->tokens_input,
            'tokens_output' => $this->tokens_output,
            'response_time_ms' => $this->response_time_ms,
        ];
    }
}
