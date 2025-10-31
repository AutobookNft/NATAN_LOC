<?php

namespace App\Services;

use App\Models\NatanUserMemory;
use Illuminate\Support\Collection;

class NatanMemoryService {
    /**
     * Pattern per rilevare comandi di memorizzazione
     */
    private const MEMORY_PATTERNS = [
        '/^(ricorda|memorizza|tieni a mente|segna|ricordati|non dimenticare)[:\s]+(.+)/i',
        '/^(ricorda|memorizza|tieni a mente|segna|ricordati|non dimenticare) (che|di)[:\s]+(.+)/i',
    ];

    /**
     * Rileva se il messaggio contiene un comando di memorizzazione
     */
    public function detectMemoryCommand(string $message): ?array {
        foreach (self::MEMORY_PATTERNS as $pattern) {
            if (preg_match($pattern, trim($message), $matches)) {
                // Estrai il contenuto da memorizzare
                $content = $matches[count($matches) - 1];

                return [
                    'is_memory_command' => true,
                    'content' => trim($content),
                    'command' => strtolower($matches[1]),
                ];
            }
        }

        return null;
    }

    /**
     * Salva una nuova memoria per l'utente
     */
    public function storeMemory(int $userId, string $content, string $type = 'general'): NatanUserMemory {
        // Estrai keywords
        $keywords = $this->extractKeywords($content);

        return NatanUserMemory::create([
            'user_id' => $userId,
            'memory_content' => $content,
            'memory_type' => $type,
            'keywords' => implode(', ', $keywords),
        ]);
    }

    /**
     * Recupera memorie rilevanti per una query
     */
    public function getRelevantMemories(int $userId, string $query, int $limit = 5): Collection {
        return NatanUserMemory::searchRelevant($userId, $query, $limit);
    }

    /**
     * Recupera tutte le memorie attive di un utente
     */
    public function getAllUserMemories(int $userId): Collection {
        return NatanUserMemory::forUser($userId)
            ->active()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Ottieni statistiche memoria utente
     */
    public function getUserMemoryStats(int $userId): array {
        $memories = NatanUserMemory::forUser($userId)->active()->get();

        return [
            'total_memories' => $memories->count(),
            'most_used' => $memories->sortByDesc('usage_count')->first(),
            'recent' => $memories->sortByDesc('created_at')->take(3),
            'by_type' => $memories->groupBy('memory_type')->map->count(),
        ];
    }

    /**
     * Marca memorie come utilizzate
     */
    public function markMemoriesAsUsed(Collection $memories): void {
        foreach ($memories as $memory) {
            $memory->markAsUsed();
        }
    }

    /**
     * Elimina una memoria
     */
    public function deleteMemory(int $memoryId, int $userId): bool {
        return NatanUserMemory::where('id', $memoryId)
            ->where('user_id', $userId)
            ->delete();
    }

    /**
     * Disattiva una memoria (soft delete)
     */
    public function deactivateMemory(int $memoryId, int $userId): bool {
        return NatanUserMemory::where('id', $memoryId)
            ->where('user_id', $userId)
            ->update(['is_active' => false]);
    }

    /**
     * Formatta memorie per il prompt
     */
    public function formatMemoriesForPrompt(Collection $memories): string {
        if ($memories->isEmpty()) {
            return '';
        }

        $formatted = "\n\n📝 **MEMORIA UTENTE (da ricordare sempre):**\n\n";

        foreach ($memories as $memory) {
            $formatted .= "- {$memory->memory_content}\n";
        }

        $formatted .= "\n**IMPORTANTE:** Tieni sempre in considerazione queste informazioni memorizzate quando rispondi all'utente.\n";

        return $formatted;
    }

    /**
     * Genera messaggio di conferma memorizzazione
     */
    public function generateConfirmationMessage(string $content): string {
        return "✅ **Memorizzato!** \n\n" .
            "Ho salvato nella mia memoria:\n" .
            "> {$content}\n\n" .
            "Lo terrò sempre a mente nelle nostre conversazioni. 🧠";
    }

    /**
     * Estrai keywords dal contenuto
     */
    private function extractKeywords(string $text): array {
        // Stop words italiane comuni
        $stopWords = [
            'il',
            'lo',
            'la',
            'i',
            'gli',
            'le',
            'un',
            'uno',
            'una',
            'dei',
            'degli',
            'delle',
            'di',
            'da',
            'in',
            'con',
            'su',
            'per',
            'tra',
            'fra',
            'a',
            'e',
            'o',
            'ma',
            'se',
            'che',
            'chi',
            'cui',
            'del',
            'della',
            'al',
            'alla',
            'dal',
            'dalla',
            'nel',
            'nella',
            'sul',
            'sulla',
            'mi',
            'ti',
            'si',
            'ci',
            'vi',
            'lo',
            'la',
            'li',
            'le',
            'sono',
            'è',
            'ho',
            'hai',
            'ha',
            'abbiamo',
            'avete',
            'hanno',
            'mio',
            'tuo',
            'suo',
            'nostro',
            'vostro',
        ];

        // Tokenizza e pulisci
        $text = strtolower($text);
        $text = preg_replace('/[^\w\sàèéìòù]/u', ' ', $text);
        $words = preg_split('/\s+/', $text);

        // Filtra stop words e parole troppo corte
        $keywords = array_filter($words, function ($word) use ($stopWords) {
            return strlen($word) > 3 && !in_array($word, $stopWords);
        });

        // Rimuovi duplicati e limita a 10 keywords
        return array_slice(array_unique(array_values($keywords)), 0, 10);
    }

    /**
     * Rileva se la query richiede l'uso della memoria
     */
    public function shouldUseMemory(string $query): bool {
        $memoryTriggers = [
            'ricordi',
            'ti ricordi',
            'hai memorizzato',
            'cosa sai di',
            'cosa ti ho detto',
            'come ti avevo detto',
            'secondo quello che',
            'basandoti su',
            'tenendo conto',
        ];

        $queryLower = strtolower($query);

        foreach ($memoryTriggers as $trigger) {
            if (strpos($queryLower, $trigger) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Genera saluto personalizzato con nome utente
     */
    public function generateGreeting(string $userName, int $memoryCount): string {
        $greetings = [
            "Ciao {$userName}! 👋",
            "Buongiorno {$userName}! 🌟",
            "Bentornato/a {$userName}! 😊",
            "Ciao {$userName}, come posso aiutarti? 💡",
        ];

        $greeting = $greetings[array_rand($greetings)];

        // Non aggiungiamo più il testo sui ricordi qui - viene mostrato solo nel badge header

        return $greeting;
    }
}
