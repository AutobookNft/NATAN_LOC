<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @package App\Models
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-02
 * @purpose Model per memorie utente NATAN con isolamento multi-tenant
 */
class NatanUserMemory extends Model {
    use HasFactory, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'memory_content',
        'memory_type',
        'keywords',
        'usage_count',
        'last_used_at',
        'is_active',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'is_active' => 'boolean',
        'usage_count' => 'integer',
    ];

    /**
     * Relazione con User
     */
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    /**
     * Incrementa il contatore di utilizzo
     */
    public function markAsUsed(): void {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Scope per memorie attive
     */
    public function scopeActive($query) {
        return $query->where('is_active', true);
    }

    /**
     * Scope per memorie di un utente specifico
     */
    public function scopeForUser($query, int $userId) {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope per tipo di memoria
     */
    public function scopeOfType($query, string $type) {
        return $query->where('memory_type', $type);
    }

    /**
     * Cerca memorie rilevanti per una query
     */
    public static function searchRelevant(int $userId, string $query, int $limit = 5): \Illuminate\Database\Eloquent\Collection {
        $keywords = self::extractKeywords($query);

        return self::forUser($userId)
            ->active()
            ->where(function ($q) use ($keywords, $query) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('memory_content', 'like', "%{$keyword}%")
                        ->orWhere('keywords', 'like', "%{$keyword}%");
                }
                $q->orWhere('memory_content', 'like', "%{$query}%");
            })
            ->orderBy('last_used_at', 'desc')
            ->orderBy('usage_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Estrae keywords da un testo
     */
    private static function extractKeywords(string $text): array {
        // Rimuovi stop words comuni
        $stopWords = ['il', 'lo', 'la', 'i', 'gli', 'le', 'un', 'uno', 'una', 'di', 'da', 'in', 'con', 'su', 'per', 'tra', 'fra', 'a', 'e', 'o', 'che', 'del', 'della', 'dei', 'delle'];

        $words = preg_split('/\s+/', strtolower($text));
        $keywords = array_filter($words, function ($word) use ($stopWords) {
            return strlen($word) > 3 && !in_array($word, $stopWords);
        });

        return array_values($keywords);
    }
}
