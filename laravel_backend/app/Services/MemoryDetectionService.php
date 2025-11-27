<?php

namespace App\Services;

use App\Models\NatanUserMemory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * Service per rilevare richieste di memorizzazione e gestire memorie utente
 * 
 * TODO: Questa logica di pattern matching dovrebbe essere spostata in Python
 * PHP dovrebbe solo ricevere il flag is_memory_request dal Python service
 * seguendo l'architettura: PHP = routing, Python = AI logic
 * 
 * @package App\Services
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC - Memory System)
 * @date 2025-11-25
 */
class MemoryDetectionService
{
    /**
     * Pattern per rilevare richieste di memorizzazione
     */
    private const MEMORY_PATTERNS = [
        // Comandi diretti di memorizzazione
        '/^ricorda\s+che\s+/i',
        '/^ricordati\s+che\s+/i',
        '/^ricordati\s+di\s+/i',
        '/^tieni\s+a\s+mente\s+che\s+/i',
        '/^memorizza\s+che\s+/i',
        '/^memorizza:\s+/i',
        '/^salva\s+questa\s+informazione:\s+/i',
        '/^non\s+dimenticare\s+che\s+/i',
        '/^non\s+dimenticarti\s+che\s+/i',
        
        // Pattern impliciti
        '/\bricorda\s+che\s+/i',
        '/\bricordati\s+che\s+/i',
        '/\btieni\s+presente\s+che\s+/i',
        '/\btieni\s+a\s+mente\s+che\s+/i',
        '/\bmemorizza\s+che\s+/i',
        '/\bnon\s+dimenticare\s+che\s+/i',
        
        // Preferenze e informazioni personali
        '/^il\s+mio\s+nome\s+è\s+/i',
        '/^mi\s+chiamo\s+/i',
        '/^sono\s+/i',
        '/^lavoro\s+(come|in|per)\s+/i',
        '/^preferisco\s+/i',
        '/^mi\s+piace\s+/i',
        '/^non\s+mi\s+piace\s+/i',
        '/^odio\s+/i',
        '/^amo\s+/i',
        '/^voglio\s+sempre\s+/i',
        '/^non\s+voglio\s+mai\s+/i',
        
        // Istruzioni permanenti
        '/^d\'ora\s+in\s+poi\s+/i',
        '/^da\s+ora\s+in\s+avanti\s+/i',
        '/^sempre\s+/i',
        '/^mai\s+/i',
        '/^ogni\s+volta\s+che\s+/i',
        '/^quando\s+.*\s+devi\s+/i',
        '/^ricordati\s+di\s+.*\s+sempre/i',
        
        // Note e annotazioni
        '/^nota:\s+/i',
        '/^importante:\s+/i',
        '/^attenzione:\s+/i',
        '/^da\s+ricordare:\s+/i',
        '/^informazione\s+importante:\s+/i',
        
        // Pattern inglesi (supporto internazionale)
        '/^remember\s+that\s+/i',
        '/^keep\s+in\s+mind\s+that\s+/i',
        '/^don\'t\s+forget\s+that\s+/i',
        '/^memorize\s+that\s+/i',
        '/^save\s+this:\s+/i',
        '/^note:\s+/i',
        '/^important:\s+/i',
        '/^my\s+name\s+is\s+/i',
        '/^i\s+am\s+/i',
        '/^i\s+prefer\s+/i',
        '/^i\s+like\s+/i',
        '/^i\s+don\'t\s+like\s+/i',
        '/^always\s+/i',
        '/^never\s+/i',
        '/^from\s+now\s+on\s+/i',
    ];

    /**
     * Richieste esplicite di salvataggio memoria (priorità alta)
     */
    private const EXPLICIT_SAVE_PATTERNS = [
        '/salva\s+nei\s+ricordi/i',
        '/aggiungi\s+ai\s+ricordi/i',
        '/salva\s+come\s+memoria/i',
        '/memorizza\s+questo/i',
        '/save\s+to\s+memory/i',
        '/add\s+to\s+memories/i',
        '/save\s+as\s+memory/i',
    ];

    /**
     * Rileva se il messaggio contiene una richiesta di memorizzazione
     */
    public function detectMemoryRequest(string $message): ?array
    {
        file_put_contents('/tmp/natan_memory_debug.log', "detectMemoryRequest called with: {$message}\n", FILE_APPEND);
        
        // Check explicit save requests first
        foreach (self::EXPLICIT_SAVE_PATTERNS as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                file_put_contents('/tmp/natan_memory_debug.log', "MATCHED explicit pattern: {$pattern}\n", FILE_APPEND);
                return [
                    'is_memory_request' => true,
                    'type' => 'explicit',
                    'content' => $this->extractMemoryContent($message, $pattern),
                ];
            }
        }

        // Check general memory patterns
        foreach (self::MEMORY_PATTERNS as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                file_put_contents('/tmp/natan_memory_debug.log', "MATCHED implicit pattern: {$pattern}\n", FILE_APPEND);
                return [
                    'is_memory_request' => true,
                    'type' => 'implicit',
                    'content' => $this->extractMemoryContent($message, $pattern),
                ];
            }
        }

        file_put_contents('/tmp/natan_memory_debug.log', "NO PATTERN MATCHED\n", FILE_APPEND);
        return null;
    }

    /**
     * Estrae il contenuto da memorizzare dal messaggio
     */
    private function extractMemoryContent(string $message, string $pattern): string
    {
        // Remove the trigger pattern
        $content = preg_replace($pattern, '', $message);
        $content = trim($content);
        
        // Se inizia con "che", rimuovilo
        $content = preg_replace('/^che\s+/i', '', $content);
        
        // Se il contenuto estratto è troppo corto, usa l'intero messaggio
        // ma rimuovi solo il trigger iniziale se c'era
        if (strlen($content) < 5) {
            // Pattern per rimuovere solo trigger iniziali comuni
            $message = preg_replace('/^(ricorda che|ricordati che|tieni a mente che|memorizza che|nota:|importante:)\s+/i', '', $message);
            $content = trim($message);
        }
        
        // Se ancora troppo corto, usa tutto
        if (strlen($content) < 5) {
            $content = $message;
        }
        
        return $content;
    }

    /**
     * Salva una memoria per l'utente
     */
    public function saveMemory(
        int $userId,
        int $tenantId,
        string $content,
        string $type = 'general',
        ?string $keywords = null
    ): ?NatanUserMemory {
        try {
            file_put_contents('/tmp/natan_memory_debug.log', "saveMemory called - Creating record...\n", FILE_APPEND);
            
            // Create memory record
            $memory = NatanUserMemory::create([
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'memory_content' => $content,
                'memory_type' => $type,
                'keywords' => $keywords,
                'is_active' => true,
            ]);

            file_put_contents('/tmp/natan_memory_debug.log', "Memory created with ID: {$memory->id}\n", FILE_APPEND);

            // Generate embedding for the memory (async)
            $this->generateMemoryEmbedding($memory);

            Log::info('[MemoryDetection] Memory saved', [
                'user_id' => $userId,
                'memory_id' => $memory->id,
                'type' => $type,
                'content_length' => strlen($content),
            ]);

            return $memory;
        } catch (\Exception $e) {
            file_put_contents('/tmp/natan_memory_debug.log', "ERROR in saveMemory: " . $e->getMessage() . "\n", FILE_APPEND);
            file_put_contents('/tmp/natan_memory_debug.log', "Stack trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
            
            Log::error('[MemoryDetection] Failed to save memory', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Genera embedding per la memoria (chiamata asincrona a Python)
     */
    private function generateMemoryEmbedding(NatanUserMemory $memory): void
    {
        try {
            $pythonServiceUrl = config('services.python_ai.url', 'http://localhost:8000');
            
            // Call Python service to generate embedding
            Http::timeout(5)->post("{$pythonServiceUrl}/api/v1/memories/generate-embedding", [
                'memory_id' => $memory->id,
                'content' => $memory->memory_content,
                'user_id' => $memory->user_id,
            ]);

            Log::info('[MemoryDetection] Embedding generation requested', [
                'memory_id' => $memory->id,
            ]);
        } catch (\Exception $e) {
            // Non-blocking: embedding can be generated later
            Log::warning('[MemoryDetection] Failed to generate embedding immediately', [
                'memory_id' => $memory->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Estrae keywords automaticamente dal contenuto
     */
    public function extractKeywords(string $content, int $maxKeywords = 5): string
    {
        // Remove common stop words
        $stopWords = [
            'il', 'lo', 'la', 'i', 'gli', 'le', 'un', 'uno', 'una',
            'di', 'a', 'da', 'in', 'con', 'su', 'per', 'tra', 'fra',
            'che', 'e', 'o', 'ma', 'se', 'non', 'sono', 'è', 'ha',
            'the', 'a', 'an', 'and', 'or', 'but', 'if', 'is', 'are', 'was', 'were',
        ];

        // Tokenize and clean
        $words = preg_split('/\s+/', strtolower($content));
        $words = array_filter($words, function($word) use ($stopWords) {
            return strlen($word) > 3 && !in_array($word, $stopWords);
        });

        // Count frequency
        $wordFreq = array_count_values($words);
        arsort($wordFreq);

        // Get top keywords
        $keywords = array_slice(array_keys($wordFreq), 0, $maxKeywords);

        return implode(', ', $keywords);
    }

    /**
     * Analizza il messaggio e salva se necessario
     */
    public function processMessage(int $userId, int $tenantId, string $message): ?array
    {
        file_put_contents('/tmp/natan_memory_debug.log', "processMessage called - userId: {$userId}, tenantId: {$tenantId}\n", FILE_APPEND);
        
        $detection = $this->detectMemoryRequest($message);
        
        if (!$detection) {
            file_put_contents('/tmp/natan_memory_debug.log', "Detection returned null\n", FILE_APPEND);
            return null;
        }

        file_put_contents('/tmp/natan_memory_debug.log', "Detection successful: " . json_encode($detection) . "\n", FILE_APPEND);

        // Extract keywords
        $keywords = $this->extractKeywords($detection['content']);
        file_put_contents('/tmp/natan_memory_debug.log', "Keywords: {$keywords}\n", FILE_APPEND);

        // Determine memory type from content
        $type = $this->inferMemoryType($detection['content']);
        file_put_contents('/tmp/natan_memory_debug.log', "Type: {$type}\n", FILE_APPEND);

        // Save memory
        $memory = $this->saveMemory(
            $userId,
            $tenantId,
            $detection['content'],
            $type,
            $keywords
        );

        file_put_contents('/tmp/natan_memory_debug.log', "Memory saved: " . ($memory ? $memory->id : 'NULL') . "\n", FILE_APPEND);

        if ($memory) {
            return [
                'memory_saved' => true,
                'memory_id' => $memory->id,
                'type' => $type,
                'content' => $detection['content'],
            ];
        }

        return null;
    }

    /**
     * Inferisce il tipo di memoria dal contenuto
     */
    private function inferMemoryType(string $content): string
    {
        $lowerContent = strtolower($content);
        
        // Personal info / Facts (nome, lavoro, età, ecc.)
        if (preg_match('/(mi chiamo|il mio nome|sono nato|ho \d+ anni|lavoro (come|in|per)|vivo (a|in)|abito (a|in)|provengo da|my name is|i am|i work|i live)/i', $content)) {
            return 'fact';
        }
        
        // Preferences (mi piace, preferisco, voglio, ecc.)
        if (preg_match('/(preferisco|mi piace|non mi piace|odio|amo|apprezzo|detesto|voglio|non voglio|i prefer|i like|i don\'t like|i love|i hate|i want)/i', $content)) {
            return 'preference';
        }
        
        // Instructions (sempre, mai, devi, non devi, ricordati di, ecc.)
        if (preg_match('/(sempre|mai|devi|non devi|ricordati di|d\'ora in poi|da ora in avanti|ogni volta|quando .* devi|always|never|must|should|remember to|from now on)/i', $content)) {
            return 'instruction';
        }
        
        // Context (progetto specifico, contesto lavorativo, ecc.)
        if (preg_match('/(sul progetto|nel progetto|per il progetto|riguardo|relativamente a|in merito a|about|regarding|concerning)/i', $content)) {
            return 'context';
        }
        
        // Default: general
        return 'general';
    }
}
