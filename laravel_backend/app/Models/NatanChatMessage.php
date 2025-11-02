<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @package App\Models
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-01
 * @purpose Model per messaggi chat NATAN (usa tabella esistente natan_chat_messages da EGI)
 */
class NatanChatMessage extends Model
{
    use TenantScoped;

    protected $table = 'natan_chat_messages';

    // CRITICAL: Use existing FlorenceEGI MySQL database
    protected $connection = 'mysql';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'project_id',
        'session_id',
        'role',
        'content',
        'reference_message_id',
        'persona_id',
        'persona_name',
        'persona_confidence',
        'persona_selection_method',
        'persona_reasoning',
        'persona_alternatives',
        'rag_sources',
        'rag_acts_count',
        'rag_method',
        'web_search_enabled',
        'web_search_provider',
        'web_search_results',
        'web_search_count',
        'web_search_from_cache',
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
        'web_search_enabled' => 'boolean',
        'web_search_results' => 'array',
        'web_search_count' => 'integer',
        'web_search_from_cache' => 'boolean',
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
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get messages from a specific session
     */
    public function scopeForSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope to get messages for a specific user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Calculate total tokens for a session
     */
    public static function getTotalTokensForSession(string $sessionId): int
    {
        return static::where('session_id', $sessionId)
            ->where('role', 'assistant')
            ->get()
            ->sum(function ($msg) {
                return ($msg->tokens_input ?? 0) + ($msg->tokens_output ?? 0);
            });
    }

    /**
     * Get total cost for a session (calculate from tokens)
     */
    public static function getTotalCostForSession(string $sessionId, ?string $model = null): float
    {
        $messages = static::where('session_id', $sessionId)
            ->where('role', 'assistant')
            ->get();

        $totalCost = 0.0;
        foreach ($messages as $msg) {
            if ($msg->tokens_input || $msg->tokens_output) {
                $cost = \App\Services\CostCalculator::calculateCost([
                    'input' => $msg->tokens_input ?? 0,
                    'output' => $msg->tokens_output ?? 0,
                ], $msg->ai_model ?? $model);
                $totalCost += $cost;
            }
        }

        return $totalCost;
    }
}
