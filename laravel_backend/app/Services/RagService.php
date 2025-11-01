<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Egi;
use App\Models\User;
use App\Services\Gdpr\AuditLogService;
use App\Services\Gdpr\ConsentService;
use App\Enums\Gdpr\GdprActivityCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Ultra\UltraLogManager\UltraLogManager;
use Ultra\ErrorManager\Interfaces\ErrorManagerInterface;

/**
 * RAG (Retrieval Augmented Generation) Service
 *
 * Sistema di recupero intelligente dei dati per alimentare l'AI:
 * 1. Verifica consenso GDPR dell'utente
 * 2. Recupera atti PA rilevanti dal database
 * 3. Sanitizza i dati (solo metadati pubblici)
 * 4. Crea contesto strutturato per l'AI
 * 5. Audit log GDPR di ogni accesso
 *
 * RAG STRATEGIES:
 * - Semantic search: Vector embeddings + cosine similarity (preferred)
 * - Keyword search: SQL LIKE queries (fallback)
 *
 * GDPR COMPLIANCE:
 * - ConsentService: Verifica consenso prima di accedere dati utente
 * - DataSanitizerService: Rimuove PII prima di invio ad AI
 * - AuditLogService: Traccia ogni accesso ai dati PA
 * - ErrorManagerInterface: Gestione errori strutturata con UEM
 *
 * @package App\Services
 * @author Padmin D. Curtis (AI Partner OS3.0)
 * @version 2.0.0 (FlorenceEGI - GDPR/ULTRA Compliance)
 * @date 2025-01-10
 * @purpose RAG system with full GDPR compliance and audit trail
 */
class RagService {
    private UltraLogManager $logger;
    private ErrorManagerInterface $errorManager;
    private AuditLogService $auditService;
    private ConsentService $consentService;
    private DataSanitizerService $sanitizer;
    private EmbeddingService $embeddingService;

    /**
     * Constructor with GDPR/Ultra compliance
     *
     * @param UltraLogManager $logger Ultra logging manager for audit trails
     * @param ErrorManagerInterface $errorManager Ultra error manager for error handling
     * @param AuditLogService $auditService GDPR audit logging service
     * @param ConsentService $consentService GDPR consent management service
     * @param DataSanitizerService $sanitizer Data sanitization service for AI safety
     * @param EmbeddingService $embeddingService Vector embedding service
     * @privacy-safe All injected services handle GDPR compliance
     */
    public function __construct(
        UltraLogManager $logger,
        ErrorManagerInterface $errorManager,
        AuditLogService $auditService,
        ConsentService $consentService,
        DataSanitizerService $sanitizer,
        EmbeddingService $embeddingService
    ) {
        $this->logger = $logger;
        $this->errorManager = $errorManager;
        $this->auditService = $auditService;
        $this->consentService = $consentService;
        $this->sanitizer = $sanitizer;
        $this->embeddingService = $embeddingService;
    }

    /**
     * Recupera il contesto per una query utente
     *
     * STRATEGIA (REGOLA STATISTICS):
     * - Scandaglia TUTTI gli atti disponibili (no limit nascosto)
     * - Semantic search: calcola similarity su TUTTI → torna TOP-N più rilevanti
     * - Keyword search: cerca in TUTTI → ordina → torna TOP-N
     * - $limit controlla SOLO quanti atti tornare all'AI, NON quanti scandagliare
     *
     * GDPR COMPLIANCE:
     * - Verifica consenso prima di accedere dati PA
     * - Audit log ogni accesso
     * - UEM error handling per errori
     * - Sanitizzazione completa prima di tornare dati
     *
     * @param string $query Query dell'utente
     * @param User $user Utente corrente (per scope PA)
     * @param int|null $limit Numero massimo di atti da TORNARE (non da scandagliare). Default: 1000 (scansione totale)
     * @return array Contesto per l'AI con atti più rilevanti
     * @throws \Exception Se consenso mancante o errore critico
     */
    public function getContextForQuery(string $query, User $user, ?int $limit = null): array {
        try {
            // 1. ULM: Log start operation
            $this->logger->info('[RAG] Starting context retrieval', [
                'query_length' => strlen($query),
                'user_id' => $user->id,
                'limit' => $limit,
            ]);

            // 2. GDPR: Check consent BEFORE accessing user documents
            if (!$this->consentService->hasConsent($user, 'allow-personal-data-processing')) {
                $this->logger->warning('[RAG] Missing consent for data processing', [
                    'user_id' => $user->id,
                ]);

                throw new \Exception('Missing consent for personal data processing. Please accept privacy policy.');
            }

            // REGOLA STATISTICS: Di default recupera TUTTI gli atti (no limit nascosto)
            // Limit SOLO se esplicitamente richiesto dal chiamante
            if ($limit === null) {
                $limit = 1000; // Nessun limite pratico (scansione totale)
            }

            // Log strategia query
            $isCountQuery = preg_match('/\b(quant[iae]|numero|totale|conta|somma)\b/i', $query);
            $isAnalysisQuery = preg_match('/\b(quali|mostra|analizza|elenca|top|più attiv|principali|priorità|riassumi|confronta|tutt)\b/i', $query);
            $isSpecificQuery = preg_match('/\b(atto|protocollo|delibera)\s+\d{3,}/i', $query);

            $this->logger->info('[RAG] Query type detected', [
                'is_count_query' => $isCountQuery,
                'is_analysis_query' => $isAnalysisQuery,
                'is_specific_query' => $isSpecificQuery,
            ]);

            // 3. Recupera atti rilevanti
            $relevantActs = $this->findRelevantActs($query, $user, $limit);

            // 4. GDPR: Audit log data access
            $this->auditService->logUserAction(
                $user,
                'rag_context_retrieval',
                [
                    'query_length' => strlen($query),
                    'acts_retrieved' => $relevantActs->count(),
                    'limit_applied' => $limit,
                    'query_type' => [
                        'count' => $isCountQuery,
                        'analysis' => $isAnalysisQuery,
                        'specific' => $isSpecificQuery,
                    ],
                ],
                GdprActivityCategory::DATA_ACCESS
            );

            // 5. Sanitizza i dati
            $sanitizedActs = $this->sanitizer->sanitizeActsCollection($relevantActs);

            // 6. Crea riassunto testuale
            $actsSummary = $this->sanitizer->createActsSummary($relevantActs);

            // 7. Crea statistiche (include total_acts_in_database)
            $stats = $this->sanitizer->createStatsContext($relevantActs, $user->id);

            $context = [
                'acts' => $sanitizedActs,
                'acts_summary' => $actsSummary,
                'stats' => $stats,
                'retrieved_at' => now()->toIso8601String(),
            ];

            // 8. Valida che non ci siano dati privati
            foreach ($sanitizedActs as $act) {
                $this->sanitizer->validateSafeData($act);
            }

            // 9. ULM: Log success
            $this->logger->info('[RAG] Context created successfully', [
                'acts_count' => count($sanitizedActs),
                'summary_length' => strlen($actsSummary),
                'total_in_db' => $stats['total_acts'] ?? 0,
            ]);

            return $context;
        } catch (\Exception $e) {
            // 10. UEM: Error handling
            $this->errorManager->handle('RAG_CONTEXT_RETRIEVAL_FAILED', [
                'user_id' => $user->id,
                'query_length' => strlen($query),
                'limit' => $limit,
                'error_message' => $e->getMessage(),
            ], $e);

            throw $e;
        }
    }

    /**
     * Trova atti rilevanti per una query
     *
     * STRATEGY:
     * 1. Try semantic search (vector embeddings) - PREFERRED
     * 2. Fallback to keyword search if no embeddings
     *
     * Keyword search basata su:
     * - Keyword matching (protocollo, tipo, titolo)
     * - Date range (se menzionate)
     * - Importi (se menzionati)
     * - Status blockchain (se richiesto)
     */
    private function findRelevantActs(string $query, User $user, int $limit): Collection {
        // STRATEGY 1: Try semantic search first (if embeddings exist)
        $semanticResults = $this->semanticSearch($query, $user, $limit, 0.5);
        if ($semanticResults && $semanticResults->isNotEmpty()) {
            $this->logger->info('[RAG] Using semantic search results', [
                'count' => $semanticResults->count(),
            ]);
            return $semanticResults;
        }

        // STRATEGY 2: Fallback to keyword-based search
        $this->logger->info('[RAG] Falling back to keyword search');

        $queryLower = strtolower($query);

        // Base query: atti PA dell'utente
        $baseQuery = Egi::query()
            ->where('user_id', $user->id)
            ->whereNotNull('pa_protocol_number') // Solo atti PA
            ->orderBy('pa_protocol_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Ricerca per numero protocollo (ma NON anni 20XX)
        if (preg_match('/\b(\d{4,}\/\d{4}|\d{5,})\b/', $query, $matches)) {
            // Filtra gli anni (20XX)
            if (!preg_match('/^20\d{2}$/', $matches[1])) {
                $protocolNumber = $matches[1];
                $this->logger->info('[RAG] Searching by protocol number', ['protocol' => $protocolNumber]);

                return $baseQuery
                    ->where('pa_protocol_number', 'like', "%{$protocolNumber}%")
                    ->limit($limit)
                    ->get();
            }
        }

        // Ricerca per tipo atto
        $types = ['delibera', 'determina', 'ordinanza', 'decreto'];
        foreach ($types as $type) {
            if (str_contains($queryLower, $type)) {
                $this->logger->info('[RAG] Searching by document type', ['type' => $type]);

                return $baseQuery
                    ->where('pa_act_type', 'like', "%{$type}%")
                    ->limit($limit)
                    ->get();
            }
        }

        // Ricerca per stato blockchain
        if (
            str_contains($queryLower, 'blockchain') ||
            str_contains($queryLower, 'certificat') ||
            str_contains($queryLower, 'tokenizzat') ||
            str_contains($queryLower, 'anchorat')
        ) {
            $this->logger->info('[RAG] Searching blockchain-anchored acts');

            return $baseQuery
                ->where('pa_anchored', true)
                ->limit($limit)
                ->get();
        }

        // Ricerca per importo (skip per ora, l'importo è in jsonMetadata)
        // TODO: implementare ricerca per importo quando necessario

        // Ricerca per data (anno) - PRIORITÀ ALTA per query temporali
        if (preg_match('/\b(20\d{2})\b/', $query, $matches)) {
            $year = $matches[1];
            $this->logger->info('[RAG] Searching by year', ['year' => $year]);

            return $baseQuery
                ->whereYear('pa_protocol_date', $year)
                ->limit($limit)
                ->get();
        }

        // Ricerca per keyword nel titolo
        $keywords = $this->extractKeywords($query);
        if (!empty($keywords)) {
            $this->logger->info('[RAG] Searching by keywords', ['keywords' => $keywords]);

            $titleSearch = clone $baseQuery;
            foreach ($keywords as $keyword) {
                $titleSearch->where('title', 'like', "%{$keyword}%");
            }

            $results = $titleSearch->limit($limit)->get();
            if ($results->isNotEmpty()) {
                return $results;
            }
        }

        // Fallback: ultimi N atti
        $this->logger->info('[RAG] Fallback to recent acts');
        return $baseQuery->limit($limit)->get();
    }

    /**
     * Semantic Search using Vector Embeddings
     *
     * Uses cosine similarity to find most relevant acts.
     *
     * PERFORMANCE:
     * - 24k acts: ~1-2 sec (acceptable for demo)
     * - Scales to 100k acts
     * - Can migrate to PostgreSQL+pgvector for better performance
     *
     * @param string $query User query
     * @param User $user Current user (for scope)
     * @param int $limit Max results
     * @param float $minSimilarity Minimum similarity threshold (0.0-1.0)
     * @return Collection|null Collection of Egi or null if no embeddings
     */
    public function semanticSearch(string $query, User $user, int $limit = 10, float $minSimilarity = 0.5): ?Collection {
        $this->logger->info('[RAG] Starting semantic search', [
            'query_length' => strlen($query),
            'user_id' => $user->id,
            'limit' => $limit,
        ]);

        // Generate embedding for query
        $queryVector = $this->generateQueryEmbedding($query);
        if (!$queryVector) {
            $this->logger->warning('[RAG] Failed to generate query embedding');
            return null;
        }

        // Get all documents (PA acts, contracts, reports, etc.) with embeddings for this user
        $acts = Egi::query()
            ->where('user_id', $user->id)
            ->whereNotNull('pa_protocol_number')
            ->whereHas('embedding') // Only acts with embeddings
            ->with('embedding')
            ->get();

        if ($acts->isEmpty()) {
            $this->logger->warning('[RAG] No acts with embeddings found');
            return null;
        }

        $this->logger->info('[RAG] Found acts with embeddings', ['count' => $acts->count()]);

        // Calculate similarity for each act
        $scored = $acts->map(function ($act) use ($queryVector) {
            if (!$act->embedding || !$act->embedding->embedding) {
                return null;
            }

            $similarity = $this->embeddingService->cosineSimilarity(
                $queryVector,
                $act->embedding->embedding
            );

            return [
                'act' => $act,
                'similarity' => $similarity,
            ];
        })->filter(function ($item) use ($minSimilarity) {
            return $item !== null && $item['similarity'] >= $minSimilarity;
        });

        // Sort by similarity (descending)
        $sorted = $scored->sortByDesc('similarity')->take($limit);

        // Return acts WITH similarity scores (not just acts)
        $results = $sorted->map(function ($item) {
            return [
                'id' => $item['act']->id,
                'act' => $item['act'],
                'similarity' => $item['similarity'],
            ];
        });

        $this->logger->info('[RAG] Semantic search completed', [
            'results_count' => $results->count(),
            'top_similarity' => $sorted->first()['similarity'] ?? 0,
        ]);

        return $results;
    }

    /**
     * Generate embedding for user query
     *
     * @param string $query
     * @return array|null Vector of 1536 floats
     */
    private function generateQueryEmbedding(string $query): ?array {
        try {
            // Use OpenAI API to generate embedding
            $apiKey = config('services.openai.api_key');
            $baseUrl = config('services.openai.base_url');
            $model = config('services.openai.embedding_model');

            if (!$apiKey) {
                $this->logger->error('[RAG] OpenAI API key not configured');
                return null;
            }

            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])
                ->timeout(config('services.openai.timeout', 30))
                ->post("{$baseUrl}/embeddings", [
                    'model' => $model,
                    'input' => $query,
                ]);

            if (!$response->successful()) {
                $this->logger->error('[RAG] OpenAI API error', [
                    'status' => $response->status(),
                ]);
                return null;
            }

            $data = $response->json();
            return $data['data'][0]['embedding'] ?? null;
        } catch (\Exception $e) {
            // UEM: Error handling for embedding generation
            $this->errorManager->handle('RAG_EMBEDDING_GENERATION_FAILED', [
                'query_length' => strlen($query),
                'error_message' => $e->getMessage(),
            ], $e);

            return null;
        }
    }

    /**
     * Estrae keyword significative dalla query
     */
    private function extractKeywords(string $query): array {
        // Rimuovi stopwords comuni
        $stopwords = [
            'il',
            'lo',
            'la',
            'i',
            'gli',
            'le',
            'un',
            'uno',
            'una',
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
            'sono',
            'è',
            'sei',
            'siamo',
            'siete',
            'hanno',
            'ho',
            'hai',
            'ha',
            'abbiamo',
            'avete',
            'mi',
            'ti',
            'ci',
            'vi',
            'si',
            'mio',
            'tuo',
            'suo',
            'nostro',
            'vostro',
            'loro',
            'questo',
            'quello',
            'questi',
            'quelli',
            'quale',
            'quali',
            'quanto',
            'quanti',
            'quando',
            'dove',
            'come',
            'perché',
            'natan',
            'mostra',
            'trova',
            'cerca',
            'dimmi',
            'quale',
            'quali',
        ];

        $words = preg_split('/\s+/', strtolower($query));
        $keywords = array_filter($words, function ($word) use ($stopwords) {
            return strlen($word) >= 4 && !in_array($word, $stopwords);
        });

        return array_values($keywords);
    }

    /**
     * Recupera statistiche globali per il dashboard
     */
    public function getGlobalStats(User $user): array {
        $acts = Egi::query()
            ->where('user_id', $user->id)
            ->whereNotNull('pa_protocol_number')
            ->get();

        return $this->sanitizer->createStatsContext($acts);
    }

    /**
     * Recupera suggerimenti di query basati sui dati disponibili
     */
    public function getSuggestions(User $user): array {
        $stats = $this->getGlobalStats($user);

        $suggestions = [];

        // Suggerimento: totale atti
        if ($stats['total_acts'] > 0) {
            $suggestions[] = "Quanti atti PA ho caricato?";
        }

        // Suggerimento: atti per tipo
        if (!empty($stats['by_type'])) {
            $topType = array_key_first($stats['by_type']);
            $suggestions[] = "Mostrami tutte le {$topType}";
        }

        // Suggerimento: blockchain
        if ($stats['anchored_acts'] > 0) {
            $suggestions[] = "Quali atti sono certificati su blockchain?";
        }

        // Suggerimento: importi
        if ($stats['total_amount'] > 0) {
            $suggestions[] = "Qual è il valore totale degli atti?";
        }

        // Suggerimento: date
        if (!empty($stats['date_range']['first'])) {
            $year = date('Y', strtotime($stats['date_range']['last']));
            $suggestions[] = "Mostrami gli atti del {$year}";
        }

        // Suggerimenti generici
        $suggestions[] = "Come funziona N.A.T.A.N.?";
        $suggestions[] = "Cosa posso fare con la blockchain?";

        return array_slice($suggestions, 0, 6); // Max 6 suggerimenti
    }
}