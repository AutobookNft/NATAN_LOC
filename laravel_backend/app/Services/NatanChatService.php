<?php

namespace App\Services;

use App\Config\NatanPersonas;
use App\Enums\Gdpr\GdprActivityCategory;
use App\Models\Egi;
use App\Models\NatanChatMessage;
use App\Models\User;
use App\Services\Gdpr\AuditLogService;
use App\Services\Gdpr\ConsentService;
use App\Services\WebSearch\WebSearchService;
use App\Services\WebSearch\WebSearchAutoDetector;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Ultra\UltraLogManager\UltraLogManager;
use Ultra\ErrorManager\Interfaces\ErrorManagerInterface;

/**
 * N.A.T.A.N. Chat Service - AI-powered conversational interface for PA acts
 *
 * VERSION: 4.1.0 - Adaptive Retry with Progressive Context Reduction
 *
 * This service implements RAG (Retrieval Augmented Generation) + Web Search to allow
 * PA officials to interact with their administrative acts + global knowledge using natural language.
 *
 * FEATURES:
 * - Conversational AI: Ask questions about specific acts or general queries
 * - RAG: Retrieves relevant acts before generating response
 * - WEB SEARCH: Augments with global best practices, normatives, funding (NEW v3.0)
 * - Context-aware: Maintains conversation history
 * - Multi-query: "Summarize act X", "Which acts about Y?", "Suggest Z"
 *
 * GDPR-COMPLIANT:
 * - Uses Anthropic Claude 3.5 Sonnet (EU DPA compliant)
 * - Processes ONLY public metadata (no PII, no signatures)
 * - Full audit trail of data sent to AI AND web search
 * - DataSanitizerService + KeywordSanitizerService filter all sensitive data
 *
 * @package App\Services
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 3.0.0 (FlorenceEGI - N.A.T.A.N. Chat AI + Web Search)
 * @date 2025-10-26
 */
class NatanChatService {
    protected AnthropicService $anthropic;
    protected RagService $rag;
    protected WebSearchService $webSearch;
    protected WebSearchAutoDetector $webSearchDetector;
    protected DataSanitizerService $sanitizer;
    protected PersonaSelector $personaSelector;
    protected ConsentService $consentService;
    protected AuditLogService $auditService;
    protected UnifiedKnowledgeService $unifiedKnowledge;
    protected UltraLogManager $logger;
    protected ErrorManagerInterface $errorManager;

    public function __construct(
        AnthropicService $anthropic,
        RagService $rag,
        WebSearchService $webSearch,
        WebSearchAutoDetector $webSearchDetector,
        DataSanitizerService $sanitizer,
        PersonaSelector $personaSelector,
        ConsentService $consentService,
        AuditLogService $auditService,
        UnifiedKnowledgeService $unifiedKnowledge,
        UltraLogManager $logger,
        ErrorManagerInterface $errorManager
    ) {
        $this->anthropic = $anthropic;
        $this->rag = $rag;
        $this->webSearch = $webSearch;
        $this->webSearchDetector = $webSearchDetector;
        $this->sanitizer = $sanitizer;
        $this->personaSelector = $personaSelector;
        $this->consentService = $consentService;
        $this->auditService = $auditService;
        $this->unifiedKnowledge = $unifiedKnowledge;
        $this->logger = $logger;
        $this->errorManager = $errorManager;
    }

    /**
     * Process user query and generate AI response
     *
     * WORKFLOW:
     * 1. Select appropriate persona (auto or manual)
     * 2. [OPTIONAL] Retrieve relevant acts using RAG system (semantic search + fallback)
     * 3. [OPTIONAL] Retrieve web search results (global best practices, normatives) ✨ NEW v3.0
     * 4. Sanitize data (GDPR compliance)
     * 5. Fusion: Merge RAG + Web Search results into unified context
     * 6. Build context with public metadata only
     * 7. Call Anthropic Claude 3.5 Sonnet with selected persona
     * 8. Save message to database with persona metadata + web search tracking
     * 9. Return structured response with sources (internal + external)
     *
     * MULTI-PERSONA SYSTEM:
     * - Auto mode: PersonaSelector analyzes query and chooses best expert
     * - Manual mode: User explicitly selects persona from UI
     * - 6 personas: Strategic, Technical, Legal, Financial, Urban/Social, Communication
     *
     * RAG STRATEGY:
     * - Semantic search: Vector embeddings + cosine similarity (preferred)
     * - Keyword search: SQL LIKE queries (fallback if no embeddings)
     * - Scales to 24k+ acts with acceptable performance
     *
     * WEB SEARCH STRATEGY (NEW v3.0):
     * - Automatic keyword sanitization (GDPR-safe)
     * - Multi-provider support (Perplexity AI, Google Custom Search)
     * - Persona-specific domain prioritization
     * - Results caching for performance
     *
     * ITERATIVE ELABORATION:
     * - use_rag=false: Skips RAG retrieval (for general consulting or elaborations)
     * - referenceContext: When provided, Claude elaborates on a previous response
     * - Use cases: Simplify, Deepen, Transform format, Extract actions, etc.
     *
     * GDPR AUDIT:
     * - Logs what data is sent to AI AND web search APIs
     * - Validates no private fields are present
     * - All data passes through DataSanitizerService + KeywordSanitizerService
     *
     * @param string $userQuery User's question/request
     * @param User $user Current authenticated user
     * @param array $conversationHistory Previous messages for context
     * @param string|null $manualPersonaId Manual persona selection (null = auto mode)
     * @param string|null $sessionId Session ID for conversation tracking (generated if null)
     * @param bool $useRag Enable RAG retrieval (default: true)
     * @param bool $useWebSearch Enable web search (default: false, opt-in) ✨ NEW
     * @param array|null $referenceContext Previous message to elaborate on (null = new query)
     * @param int|null $projectId Active project ID for priority RAG (null = generic chat) ✨ NEW v4.0
     * @return array ['success' => bool, 'response' => string, 'sources' => array, 'web_sources' => array, 'persona' => array, 'session_id' => string]
     * @throws \Exception
     */
    public function processQuery(
        string $userQuery,
        User $user,
        array $conversationHistory = [],
        ?string $manualPersonaId = null,
        ?string $sessionId = null,
        bool $useRag = true,
        bool $useWebSearch = false,
        ?array $referenceContext = null,
        ?int $projectId = null // ✨ NEW v4.0
    ): array {
        $startTime = microtime(true);
        $sessionId = $sessionId ?? uniqid('natan_', true);

        $logContext = [
            'service' => 'NatanChatService',
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'query_length' => strlen($userQuery),
            'manual_persona' => $manualPersonaId,
            'use_rag' => $useRag,
            'has_reference' => $referenceContext !== null,
            'project_id' => $projectId, // ✨ NEW v4.0
        ];

        $this->logger->info('[NatanChatService] Processing user query', $logContext);

        try {
            // STEP 0: Save user message to database
            $userMessage = NatanChatMessage::create([
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'role' => 'user',
                'content' => $userQuery,
                'project_id' => $projectId, // ✨ NEW v4.0
            ]);

            // STEP 1: Select appropriate persona
            $personaSelection = $this->personaSelector->selectPersona(
                $userQuery,
                $manualPersonaId,
                ['conversation_history' => $conversationHistory]
            );

            $logContext['persona'] = $personaSelection['persona_id'];
            $logContext['persona_confidence'] = $personaSelection['confidence'];
            $logContext['persona_method'] = $personaSelection['method'];

            $this->logger->info('[NatanChatService] Persona selected', $logContext);

            // STEP 1.5: Auto-detect if web search should be enabled (FASE 2 - Smart Mode) ✨ NEW v3.0
            // If user didn't explicitly set useWebSearch, let AI decide
            if (!$useWebSearch) {
                $autoDetection = $this->webSearchDetector->shouldEnableWebSearch(
                    $userQuery,
                    $personaSelection['persona_id'],
                    ['conversation_history' => $conversationHistory]
                );

                // Auto-enable if confidence >= 50%
                if ($autoDetection['should_enable']) {
                    $useWebSearch = true;
                    $logContext['web_search_auto_enabled'] = true;
                    $logContext['web_search_confidence'] = $autoDetection['confidence'];
                    $logContext['web_search_reasoning'] = $autoDetection['reasoning'];

                    $this->logger->info('[NatanChatService] Web search AUTO-ENABLED by detector', [
                        'confidence' => $autoDetection['confidence'],
                        'reasoning' => $autoDetection['reasoning'],
                        'triggers' => $autoDetection['triggers'],
                    ]);
                }
            }

            // STEP 2: Retrieve context using Unified Knowledge Base or Legacy RAG+Web
            // ✨ NEW v5.0: Unified Knowledge Base (single source of truth, semantic ranking across all sources)
            // 📚 LEGACY v4.0: Priority RAG + Web Search (separate source management)
            if (config('natan.enable_unified_knowledge')) {
                // 🆕 NEW v5.0: Unified Knowledge Base
                // Single pipeline: gather all sources → chunk → embed → semantic search → format with citations
                try {
                    $unifiedOptions = [
                        'use_rag' => $useRag,
                        'use_web_search' => $useWebSearch,
                        'project_id' => $projectId,
                        'acts_from_reference' => $referenceContext['acts'] ?? null,
                        'web_results' => null, // Will be fetched internally if needed
                        'top_k' => 20, // Top 20 most relevant chunks across all sources
                    ];

                    $unifiedResult = $this->getUnifiedContext($userQuery, $user, $unifiedOptions);

                    // Prepare context in unified format
                    $context = [
                        'unified_sources' => $unifiedResult['unified_sources'] ?? [],
                        'unified_context' => $unifiedResult['unified_context'] ?? '',
                        'stats' => $unifiedResult['stats'] ?? [],
                    ];

                    // Add reference context if provided (for elaborations)
                    if ($referenceContext) {
                        $context['reference_message'] = $referenceContext;
                    }

                    $logContext['unified_knowledge_enabled'] = true;
                    $logContext['total_chunks'] = $context['stats']['total_chunks'] ?? 0;
                    $logContext['avg_similarity'] = $context['stats']['avg_similarity'] ?? 0;
                    $logContext['source_distribution'] = $context['stats']['by_type'] ?? [];

                    $this->logger->info('[NatanChatService] ✨ Unified Knowledge Base context retrieved', $logContext);
                } catch (\Exception $e) {
                    // Fallback to empty context on error
                    $this->logger->error('[NatanChatService] Unified Knowledge Base failed - using empty context', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);

                    $context = [
                        'unified_sources' => [],
                        'unified_context' => '',
                        'stats' => [],
                    ];

                    if ($referenceContext) {
                        $context['reference_message'] = $referenceContext;
                    }

                    $logContext['unified_knowledge_enabled'] = true;
                    $logContext['unified_knowledge_error'] = true;
                }
            } else {
                // 📚 LEGACY v4.0: Priority RAG + Web Search (separate source management)
                // Uses semantic search (vector embeddings) with keyword search fallback
                // REGOLA STATISTICS: No limit hardcoded → scandaglia TUTTE le fonti
                // ✨ NEW v4.0: Priority RAG when project_id is active
                $ragMethod = null;
                if ($useRag) {
                    if ($projectId) {
                        // ✨ NEW v4.0: Priority RAG with 3-tier search (Project Docs > Project Chat > PA Acts)
                        // STEP 2.1: Load Project model
                        $project = \App\Models\Project::find($projectId);

                        if (!$project) {
                            $this->logger->warning('[NatanChatService] Project not found for RAG', [
                                'project_id' => $projectId,
                                'user_id' => $user->id,
                            ]);

                            // Fallback to standard RAG if project not found
                            $context = $this->rag->getContextForQuery($userQuery, $user);
                            $ragMethod = 'semantic';
                        } else {
                            // STEP 2.2: Priority RAG - Search in ALL available sources
                            // NO LIMIT on search = scans entire archive (10k, 100k, 1M acts if present)
                            // Then takes top N by similarity for Claude context (prevent rate limit)
                            $ragResults = app(\App\Services\Projects\ProjectRagService::class)
                                ->searchProjectContext($userQuery, $project, null); // null = search ALL

                            $ragMethod = 'priority_rag'; // Project context mode

                            // Extract results and stats
                            $rawResults = $ragResults['results'] ?? [];
                            $stats = $ragResults['stats'] ?? [];

                            // STEP 2.3: Transform ProjectRag results to standard RAG format
                            // ProjectRag returns: ['type' => 'document'|'chat'|'pa_act', 'text' => '...', 'similarity' => X]
                            // Standard RAG expects: ['id' => X, 'content' => '...', ...]
                            $transformedResults = array_map(function ($result, $index) {
                                return [
                                    'id' => $result['type'] . '_' . ($result['chunk_id'] ?? $result['message_id'] ?? $result['act_id'] ?? $index),
                                    'content' => $result['text'] ?? '',
                                    'source_type' => $result['type'],
                                    'similarity' => $result['similarity'],
                                    'metadata' => $result,
                                ];
                            }, $rawResults, array_keys($rawResults));

                            // STEP 2.4: Prepare context with ALL results
                            // NO pre-filtering! Adaptive retry will reduce if rate limited
                            $this->logger->info('[NatanChatService] Priority RAG - prepared ALL results for Claude', [
                                'total_found' => count($transformedResults),
                                'strategy' => 'SEND_ALL_THEN_ADAPT',
                            ]);

                            $context = [
                                'acts' => $transformedResults, // ALL results - adaptive retry will reduce if needed
                                'acts_summary' => $this->buildProjectContextSummary($transformedResults),
                                'stats' => $stats,
                            ];

                            $logContext['project_id'] = $projectId;
                            $logContext['total_results'] = $stats['total'] ?? 0;
                            $logContext['project_docs_count'] = $stats['documents'] ?? 0;
                            $logContext['project_chat_count'] = $stats['chat'] ?? 0;

                            $this->logger->info('[NatanChatService] Priority RAG context retrieved (Project mode)', $logContext);
                        }
                    } else {
                        // Generic PA chat: Standard RAG with SMART LIMIT
                        // RAG will rank by relevance, we take top 20 most relevant
                        $smartLimit = 20; // Top 20 most relevant acts
                        $contextRaw = $this->rag->getContextForQuery($userQuery, $user, $smartLimit);

                        // Send top 20 ranked acts to Claude
                        $allActs = $contextRaw['acts'] ?? [];

                        $this->logger->info('[NatanChatService] Standard RAG - prepared ALL results for Claude', [
                            'total_found' => count($allActs),
                            'strategy' => 'SEND_ALL_THEN_ADAPT',
                        ]);

                        $context = [
                            'acts' => $allActs, // ALL acts - adaptive retry will reduce if needed
                            'acts_summary' => $contextRaw['acts_summary'] ?? '',
                            'stats' => $contextRaw['stats'] ?? [],
                        ];

                        $ragMethod = 'semantic'; // Default to semantic

                        $logContext['acts_count'] = count($allActs);
                        $logContext['context_summary_length'] = strlen($context['acts_summary']);
                        $logContext['rag_method'] = $ragMethod;

                        $this->logger->info('[NatanChatService] RAG context retrieved and sanitized (Generic PA mode)', $logContext);
                    }
                } else {
                    // No RAG: Check if acts provided via referenceContext
                    // (analyzeActsStream provides acts directly to skip RAG overhead)
                    if ($referenceContext && isset($referenceContext['acts']) && !empty($referenceContext['acts'])) {
                        // Use acts from referenceContext (provided by controller)
                        $context = [
                            'acts' => $referenceContext['acts'],
                            'acts_summary' => '', // No summary needed, acts are pre-formatted
                            'stats' => [],
                        ];

                        $logContext['acts_count'] = count($referenceContext['acts']);
                        $logContext['rag_skipped'] = false; // Acts provided directly
                        $logContext['acts_source'] = 'referenceContext';

                        $this->logger->info('[NatanChatService] Acts provided via referenceContext (analyzeActsStream)', $logContext);
                    } else {
                        // Truly no acts (general consulting or elaboration on text-only message)
                        $context = [
                            'acts' => [],
                            'acts_summary' => '',
                            'stats' => [],
                        ];

                        $logContext['acts_count'] = 0;
                        $logContext['rag_skipped'] = true;

                        $this->logger->info('[NatanChatService] RAG skipped (elaboration or general query)', $logContext);
                    }
                }

                // STEP 2.5: Retrieve web search results (if enabled) ✨ NEW v3.0
                $webSearchResults = [];
                $webSearchMetadata = null;
                if ($useWebSearch) {
                    try {
                        $webSearchResponse = $this->webSearch->search(
                            $userQuery,
                            $personaSelection['persona_id'],
                            5 // Max 5 web results
                        );

                        if (!empty($webSearchResponse['success'])) {
                            $webSearchResults = $webSearchResponse['results'] ?? [];
                            $webSearchMetadata = $webSearchResponse['metadata'] ?? null;

                            $logContext['web_search_count'] = count($webSearchResults);
                            $logContext['web_search_provider'] = $webSearchMetadata['provider'] ?? 'unknown';
                            $logContext['web_search_from_cache'] = $webSearchMetadata['from_cache'] ?? false;

                            $this->logger->info('[NatanChatService] Web search results retrieved', $logContext);
                        } else {
                            $this->logger->warning('[NatanChatService] Web search failed gracefully', [
                                'error' => $webSearchResponse['error'] ?? 'unknown',
                            ]);
                        }
                    } catch (\Exception $e) {
                        // Web search failed but don't block the entire response
                        $this->logger->error('[NatanChatService] Web search exception - continuing without external sources', [
                            'error' => $e->getMessage(),
                            'query' => $userQuery,
                        ]);
                        // Continue with empty web search results - RAG data is still available
                    }

                    // GDPR Audit - Log web search data (use null-coalescing to avoid notices)
                    $this->logger->info('[NatanChatService][GDPR] Web search query sent to external API', [
                        'user_id' => $user->id,
                        'provider' => $webSearchMetadata['provider'] ?? null,
                        'query_sanitized' => $webSearchMetadata['query_sanitized'] ?? null,
                        'keywords_removed' => $webSearchMetadata['keywords_removed'] ?? null,
                        'results_count' => count($webSearchResults),
                        'from_cache' => $webSearchMetadata['from_cache'] ?? false,
                        'timestamp' => now()->toIso8601String(),
                    ]);
                } else {
                    $logContext['web_search_disabled'] = true;
                }

                // STEP 2.6: Fusion - Merge RAG + Web Search into unified context ✨ NEW v3.0
                if (!empty($webSearchResults)) {
                    // Add web_sources to context for Claude
                    $context['web_sources'] = $webSearchResults;

                    // Build web sources summary for prompt
                    $webSourcesSummary = $this->buildWebSourcesSummary($webSearchResults);
                    $context['web_sources_summary'] = $webSourcesSummary;

                    // 🚨 CRITICAL DEBUG LOG
                    $this->logger->critical('🌐🌐🌐 WEB SOURCES ADDED TO CONTEXT', [
                        'web_sources_summary_length' => strlen($webSourcesSummary),
                        'web_sources_summary_preview' => substr($webSourcesSummary, 0, 300),
                        'internal_sources' => count($context['acts']),
                        'external_sources' => count($webSearchResults),
                        'total_sources' => count($context['acts']) + count($webSearchResults),
                    ]);

                    $this->logger->info('[NatanChatService] Context fusion completed (RAG + Web)', [
                        'internal_sources' => count($context['acts']),
                        'external_sources' => count($webSearchResults),
                        'total_sources' => count($context['acts']) + count($webSearchResults),
                    ]);
                }

                // Add reference context if provided (for elaborations)
                if ($referenceContext) {
                    $context['reference_message'] = $referenceContext;

                    // Only log reference ID if it exists (not all reference contexts have IDs)
                    if (isset($referenceContext['id'])) {
                        $logContext['reference_message_id'] = $referenceContext['id'];
                    }

                    $this->logger->info('[NatanChatService] Reference context included for elaboration', $logContext);
                }

                $logContext['unified_knowledge_enabled'] = false;
            }

            // STEP 3: GDPR Audit - Log what we're sending to AI
            $fieldsToLog = [];
            if (!empty($context['acts'])) {
                $firstAct = reset($context['acts']); // Get first element safely
                if (is_array($firstAct)) {
                    $fieldsToLog = array_keys($firstAct);
                }
            }

            $this->logger->info('[NatanChatService][GDPR] Data sent to Anthropic AI', [
                'user_id' => $user->id,
                'acts_count' => count($context['acts']),
                'acts_ids' => array_column($context['acts'], 'id'),
                'fields_sent' => $fieldsToLog,
                'persona_id' => $personaSelection['persona_id'],
                'timestamp' => now()->toIso8601String(),
            ]);

            // STEP 4: Call Anthropic Claude API with ADAPTIVE RETRY on rate_limit_error
            // ENTERPRISE STRATEGY: Try ALL acts first, reduce ONLY if rate limited
            // OBIETTIVO: Massimizzare contesto inviato a Claude per accuratezza massima
            // Retry sequence: ALL_FOUND → HALF → QUARTER → ... until Claude accepts
            $aiResponseData = null;
            $usage = null;
            $originalActsCount = count($context['acts']);

            // START WITH ALL ACTS FOUND (not a config limit!)
            // SMART LIMIT: Start with reasonable amount, not ALL acts
            $claudeContextLimit = min($originalActsCount, 20); // Max 20 acts per request
            $minLimit = config('natan.claude_context_limit_minimum', 5);
            $retryAttempt = 0;
            $maxRetries = 10; // Safety: prevent infinite loop

            \Log::info('🚀🚀🚀 ADAPTIVE RETRY STARTING', [
                'total_acts_found' => $originalActsCount,
                'first_attempt_will_send' => $claudeContextLimit,
                'strategy' => 'SMART_LIMIT_20',
            ]);

            $this->logger->info('[NatanChatService] Starting adaptive retry with smart limit', [
                'total_acts_found' => $originalActsCount,
                'first_attempt_will_send' => $claudeContextLimit,
                'strategy' => 'MAX_20_ACTS_PER_REQUEST',
            ]);

            \Log::info('🔄 ENTERING WHILE LOOP', ['retry_attempt' => $retryAttempt, 'maxRetries' => $maxRetries]);

            while ($retryAttempt < $maxRetries) {
                \Log::info('🔁 WHILE ITERATION START', ['retry_attempt' => $retryAttempt]);

                try {
                    \Log::info('✅ INSIDE TRY BLOCK', ['claudeContextLimit' => $claudeContextLimit]);
                    // Apply current limit to context
                    $limitedContext = $context;
                    if ($claudeContextLimit < $originalActsCount) {
                        $limitedContext['acts'] = array_slice($context['acts'], 0, $claudeContextLimit);
                        $this->logger->info('[NatanChatService] Context reduced for retry', [
                            'original_count' => $originalActsCount,
                            'sending' => $claudeContextLimit,
                            'retry_attempt' => $retryAttempt,
                        ]);
                    } else {
                        $this->logger->info('[NatanChatService] Sending ALL acts found', [
                            'acts_count' => $claudeContextLimit,
                            'retry_attempt' => $retryAttempt,
                        ]);
                    }

                    // Call Anthropic API
                    $aiResponseData = $this->anthropic->chat(
                        $userQuery,
                        $limitedContext,
                        $conversationHistory,
                        $personaSelection['persona_id']
                    );

                    // Success! Break retry loop
                    $this->logger->info('[NatanChatService] ✅ Claude API accepted context', [
                        'retry_attempt' => $retryAttempt,
                        'acts_sent' => $claudeContextLimit,
                        'acts_found' => $originalActsCount,
                        'reduction_percentage' => $originalActsCount > 0 ? round(($claudeContextLimit / $originalActsCount) * 100, 1) : 100,
                    ]);
                    break;
                } catch (\Exception $e) {
                    \Log::error('❌ EXCEPTION CAUGHT IN RETRY LOOP!', [
                        'exception' => get_class($e),
                        'message' => substr($e->getMessage(), 0, 200),
                        'retry_attempt' => $retryAttempt,
                    ]);

                    // Check if it's a rate limit error
                    $errorBody = method_exists($e, 'getMessage') ? $e->getMessage() : '';
                    $isRateLimitError = str_contains($errorBody, 'rate_limit_error') ||
                        str_contains($errorBody, 'rate limit') ||
                        str_contains($errorBody, '429');

                    if ($isRateLimitError && $claudeContextLimit > $minLimit) {
                        // Reduce limit by half and retry
                        $previousLimit = $claudeContextLimit;
                        $claudeContextLimit = max($minLimit, (int)($claudeContextLimit / 2));
                        $retryAttempt++;

                        $this->logger->warning('[NatanChatService] ⚠️ Rate limit hit - reducing context and retrying', [
                            'previous_limit' => $previousLimit,
                            'new_limit' => $claudeContextLimit,
                            'retry_attempt' => $retryAttempt,
                            'reduction_percentage' => round(($claudeContextLimit / $originalActsCount) * 100, 1),
                            'error' => substr($errorBody, 0, 200),
                        ]);

                        // LONGER pause for ACCELERATION LIMIT
                        // Anthropic requires GRADUAL scaling - need significant delays between attempts
                        // Progressive delay: 10s → 15s → 20s → 25s → 30s → 35s → 40s (max)
                        // INCREASED from 5s to 10s base to comply with acceleration limits
                        $delaySec = min(10 + (5 * $retryAttempt), 40);

                        $this->logger->info('[NatanChatService] 💤 Waiting {delay}s before retry (acceleration limit)', [
                            'delay_seconds' => $delaySec,
                            'retry_attempt' => $retryAttempt,
                            'reason' => 'Anthropic acceleration limit - scaling gradually',
                        ]);

                        \Log::info('💤 SLEEPING BEFORE RETRY', [
                            'delay_seconds' => $delaySec,
                            'retry_attempt' => $retryAttempt,
                        ]);

                        sleep($delaySec); // Use sleep() not usleep() for longer delays
                        continue;
                    } else {
                        // Not a rate limit error OR reached minimum limit
                        $this->errorManager->handle('NATAN_API_CALL_FAILED', [
                            'user_id' => $user->id,
                            'is_rate_limit' => $isRateLimitError,
                            'current_limit' => $claudeContextLimit,
                            'min_limit' => $minLimit,
                            'retry_attempt' => $retryAttempt,
                        ], $e);

                        // Check for credit balance error FIRST (most critical)
                        $errorMessage = $e->getMessage();
                        $isCreditError = str_contains($errorMessage, 'credit balance is too low') ||
                            str_contains($errorMessage, 'upgrade or purchase credits');

                        if ($isCreditError) {
                            // Critical: API credits exhausted
                            return [
                                'success' => false,
                                'response' => "⚠️ **Credito API esaurito**\n\n" .
                                    "Il servizio N.A.T.A.N. non può rispondere perché il credito Anthropic è terminato.\n\n" .
                                    "**Azione richiesta dall'amministratore:**\n" .
                                    "1. Accedi a [Anthropic Console](https://console.anthropic.com/settings/billing)\n" .
                                    "2. Ricarica il credito o aggiorna il piano\n" .
                                    "3. Verifica la carta di credito associata\n\n" .
                                    "Tutti gli altri servizi (ricerca semantica, web search) funzionano correttamente.",
                                'error' => 'credit_exhausted',
                                'retry_attempts' => $retryAttempt,
                            ];
                        }

                        // User-friendly message for rate limit exhaustion
                        if ($isRateLimitError && $claudeContextLimit <= $minLimit) {
                            // Don't throw exception - return user-friendly response
                            $userMessage = __('natan.errors.rate_limit_exhausted');

                            return [
                                'success' => false,
                                'response' => $userMessage,
                                'error' => 'rate_limit_exhausted',
                                'retry_attempts' => $retryAttempt,
                            ];
                        }

                        throw $e;
                    }
                }
            }

            // Safety check: if we exhausted retries
            if ($aiResponseData === null) {
                $this->logger->critical('[NatanChatService] Exhausted all retry attempts', [
                    'total_attempts' => $retryAttempt,
                    'max_retries' => $maxRetries,
                    'last_limit' => $claudeContextLimit,
                ]);

                // Return user-friendly response instead of throwing
                return [
                    'success' => false,
                    'response' => __('natan.errors.rate_limit_exhausted'),
                    'error' => 'max_retries_exhausted',
                    'retry_attempts' => $retryAttempt,
                ];
            }

            // Extract message and usage from response
            $aiResponse = $aiResponseData['message'] ?? $aiResponseData; // Backward compatibility
            $usage = $aiResponseData['usage'] ?? null;
            $modelUsed = $aiResponseData['model'] ?? config('services.anthropic.model'); // NEW: track actual model used

            // Update context['acts'] with final limited version for accurate logging
            if ($claudeContextLimit < $originalActsCount) {
                $context['acts'] = array_slice($context['acts'], 0, $claudeContextLimit);
            }

            $responseTime = (int)((microtime(true) - $startTime) * 1000); // milliseconds

            $this->logger->info('[NatanChatService] AI response generated', [
                ...$logContext,
                'response_length' => strlen($aiResponse),
                'response_time_ms' => $responseTime,
                'tokens_input' => $usage['input_tokens'] ?? null,
                'tokens_output' => $usage['output_tokens'] ?? null,
                'model_used' => $modelUsed,
            ]);

            // STEP 5: Build response with sources (sanitized)
            // Handle both standard RAG and ProjectRag formats
            $sources = array_map(function ($act) {
                // ProjectRag format: metadata contains original result
                if (isset($act['source_type']) && $act['source_type'] === 'pa_act') {
                    $metadata = $act['metadata'] ?? [];
                    return [
                        'id' => $metadata['act_id'] ?? $act['id'],
                        'protocol_number' => $metadata['metadata']['protocol_number'] ?? 'N/A',
                        'date' => $metadata['metadata']['date'] ?? null,
                        'type' => $metadata['metadata']['type'] ?? 'pa_act',
                        'title' => $metadata['title'] ?? 'Atto PA',
                        'url' => isset($metadata['act_id']) ? route('pa.acts.show', $metadata['act_id']) : '#',
                        'blockchain_anchored' => $metadata['metadata']['blockchain_anchored'] ?? false,
                    ];
                }

                // Standard RAG format
                return [
                    'id' => $act['id'],
                    'protocol_number' => $act['protocol_number'] ?? 'N/A',
                    'date' => $act['date'] ?? null,
                    'type' => $act['type'] ?? 'unknown',
                    'title' => $act['title'] ?? 'Unknown',
                    'url' => route('pa.acts.show', $act['id']),
                    'blockchain_anchored' => $act['blockchain_anchored'] ?? false,
                ];
            }, $context['acts']);

            // STEP 6: Save assistant message to database
            $assistantMessage = NatanChatMessage::create([
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'role' => 'assistant',
                'content' => $aiResponse,
                'persona_id' => $personaSelection['persona_id'],
                'persona_name' => NatanPersonas::getName($personaSelection['persona_id']),
                'persona_confidence' => $personaSelection['confidence'],
                'persona_selection_method' => $personaSelection['method'],
                'persona_reasoning' => $personaSelection['reasoning'],
                'persona_alternatives' => $personaSelection['alternatives'] ?? [],
                'rag_sources' => array_column($context['acts'], 'id'),
                'rag_acts_count' => count($context['acts']),
                'rag_method' => $ragMethod,
                'web_search_enabled' => $useWebSearch, // NEW v3.0
                'web_search_provider' => $webSearchMetadata['provider'] ?? null, // NEW v3.0
                'web_search_results' => !empty($webSearchResults) ? $webSearchResults : null, // NEW v3.0
                'web_search_count' => count($webSearchResults), // NEW v3.0
                'web_search_from_cache' => $webSearchMetadata['from_cache'] ?? false, // NEW v3.0
                'ai_model' => $modelUsed, // UPDATED: use actual model from API response
                'tokens_input' => $usage['input_tokens'] ?? null, // Track tokens for cost monitoring
                'tokens_output' => $usage['output_tokens'] ?? null, // Track tokens for cost monitoring
                'response_time_ms' => $responseTime,
                'reference_message_id' => $referenceContext['id'] ?? null, // Track elaborations
            ]);

            return [
                'success' => true,
                'response' => $aiResponse,
                'sources' => $sources,
                'relevant_acts_count' => count($context['acts']),
                'web_sources' => $webSearchResults, // NEW v3.0
                'web_search_metadata' => $webSearchMetadata, // NEW v3.0
                'persona' => [
                    'id' => $personaSelection['persona_id'],
                    'name' => NatanPersonas::getName($personaSelection['persona_id']),
                    'confidence' => $personaSelection['confidence'],
                    'method' => $personaSelection['method'],
                    'reasoning' => $personaSelection['reasoning'],
                    'alternatives' => $personaSelection['alternatives'] ?? [],
                    'suggestion' => $this->personaSelector->getSuggestionMessage($personaSelection['alternatives'] ?? []),
                ],
                'session_id' => $sessionId,
                'message_ids' => [
                    'user' => $userMessage->id,
                    'assistant' => $assistantMessage->id,
                ],
                // ✨ NEW: Usage tracking for SSE cost panel
                'usage' => $usage ?? [
                    'input_tokens' => 0,
                    'output_tokens' => 0,
                ],
                // ✨ NEW: Model actually used (may differ from config due to fallback)
                'ai_model' => $modelUsed,
            ];
        } catch (\Throwable $e) {
            // UEM handles logging + user notification
            $this->errorManager->handle('NATAN_QUERY_PROCESSING_FAILED', [
                ...$logContext,
                'query_length' => mb_strlen($userQuery),
                'history_count' => count($conversationHistory),
            ], $e);

            // Detect specific error types for user-friendly messages
            $errorMessage = $e->getMessage();
            $userFriendlyMessage = "Mi dispiace, ho avuto un problema nell'elaborare la tua richiesta. Riprova tra poco.";

            // Check for credit balance error
            if (
                str_contains($errorMessage, 'credit balance is too low') ||
                str_contains($errorMessage, 'upgrade or purchase credits')
            ) {
                $userFriendlyMessage = "⚠️ **Credito API esaurito**\n\n" .
                    "Il servizio N.A.T.A.N. non può rispondere perché il credito Anthropic è terminato.\n\n" .
                    "**Azione richiesta:**\n" .
                    "- L'amministratore deve ricaricare il credito su [Anthropic Console](https://console.anthropic.com/settings/billing)\n" .
                    "- Oppure aggiornare il piano di abbonamento\n\n" .
                    "Tutti gli altri servizi (ricerca semantica, web search) funzionano correttamente. " .
                    "Serve solo ricaricare il credito API per Claude.";
            }
            // Check for rate limit errors
            elseif (
                str_contains($errorMessage, 'rate_limit_error') ||
                str_contains($errorMessage, '429') ||
                str_contains($errorMessage, 'rate limit')
            ) {
                $userFriendlyMessage = "⏱️ **Limite richieste raggiunto**\n\n" .
                    "Troppo traffico API in questo momento. Il sistema sta rallentando automaticamente.\n\n" .
                    "Riprova tra 30-60 secondi.";
            }
            // Check for timeout errors
            elseif (
                str_contains($errorMessage, 'timeout') ||
                str_contains($errorMessage, 'timed out')
            ) {
                $userFriendlyMessage = "⏰ **Timeout connessione**\n\n" .
                    "La richiesta ha impiegato troppo tempo. Prova a:\n" .
                    "- Ridurre il numero di atti analizzati\n" .
                    "- Formulare una domanda più specifica\n" .
                    "- Riprovare tra qualche minuto";
            }

            return [
                'success' => false,
                'response' => $userFriendlyMessage,
                'error' => $errorMessage
            ];
        }
    }

    /**
     * Get suggested questions for user
     *
     * Uses RAG service to generate context-aware suggestions
     *
     * @param User $user
     * @return array
     */
    public function getSuggestedQuestions(User $user): array {
        try {
            return $this->rag->getSuggestions($user);
        } catch (\Exception $e) {
            $this->logger->error('[NatanChatService] Failed to get suggestions from RAG', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            // Fallback suggestions
            return [
                "Come funziona N.A.T.A.N.?",
                "Quali documenti posso caricare?",
                "Come viene garantita la sicurezza dei dati?"
            ];
        }
    }

    /**
     * Build summary text from web search results ✨ NEW v3.0
     *
     * Creates a structured summary of external sources for Claude's context.
     * Format optimized for AI consumption.
     *
     * @param array $webResults Web search results
     * @return string Formatted summary
     */
    protected function buildWebSourcesSummary(array $webResults): string {
        if (empty($webResults)) {
            return '';
        }

        $summary = "## EXTERNAL WEB SOURCES (Best Practices, Normatives, Case Studies)\n\n";

        foreach ($webResults as $index => $result) {
            $num = $index + 1;
            $title = $result['title'] ?? 'Untitled';
            $url = $result['url'] ?? '';
            $snippet = $result['snippet'] ?? '';
            $relevance = $result['relevance_score'] ?? 0;

            $summary .= "### Source {$num} (Relevance: " . round($relevance * 100) . "%)\n";
            $summary .= "**Title:** {$title}\n";
            $summary .= "**URL:** {$url}\n";
            $summary .= "**Content:** {$snippet}\n\n";
        }

        $summary .= "---\n\n";
        $summary .= "**INSTRUCTIONS:**\n";
        $summary .= "- Use these external sources to benchmark internal acts against global best practices\n";
        $summary .= "- Cite sources when making comparisons or recommendations\n";
        $summary .= "- Identify gaps between internal practices and international standards\n";
        $summary .= "- Suggest actionable improvements based on successful case studies\n";

        return $summary;
    }

    /**
     * Build context summary from ProjectRag results
     *
     * Transforms project RAG results (documents + chat) into a structured
     * summary compatible with Claude's context window.
     *
     * @param array $results ProjectRag search results
     * @return string Formatted summary
     */
    protected function buildProjectContextSummary(array $results): string {
        if (empty($results)) {
            return '';
        }

        $summary = "## PROJECT CONTEXT (Documents + Chat History + PA Acts)\n\n";

        $documentCount = 0;
        $chatCount = 0;
        $paActsCount = 0;

        foreach ($results as $index => $result) {
            $num = $index + 1;
            $type = $result['type'] ?? 'unknown';
            $similarity = round(($result['similarity'] ?? 0) * 100);

            if ($type === 'document') {
                $documentCount++;
                $docName = $result['document_name'] ?? 'Unknown';
                $text = $result['text'] ?? '';
                $page = $result['page_number'] ?? 'N/A';

                $summary .= "### Document {$documentCount} (Relevance: {$similarity}%)\n";
                $summary .= "**File:** {$docName}\n";
                $summary .= "**Page:** {$page}\n";
                $summary .= "**Content:** {$text}\n\n";
            } elseif ($type === 'chat') {
                $chatCount++;
                $message = $result['text'] ?? '';
                $response = $result['response'] ?? '';

                $summary .= "### Chat History {$chatCount} (Relevance: {$similarity}%)\n";
                $summary .= "**User:** {$message}\n";
                if ($response) {
                    $summary .= "**Assistant:** {$response}\n";
                }
                $summary .= "\n";
            } elseif ($type === 'pa_act') {
                $paActsCount++;
                $title = $result['title'] ?? 'Atto PA';
                $text = $result['text'] ?? '';

                $summary .= "### PA Act {$paActsCount} (Relevance: {$similarity}%)\n";
                $summary .= "**Title:** {$title}\n";
                $summary .= "**Content:** {$text}\n\n";
            }
        }

        $summary .= "---\n\n";
        $summary .= "**INSTRUCTIONS:**\n";
        $summary .= "- Prioritize information from project documents (weight 1.0 - highest accuracy)\n";
        $summary .= "- Use chat history for context continuity (weight 0.8)\n";
        $summary .= "- Use PA acts for general knowledge (weight 0.5)\n";
        $summary .= "- Cite document/act sources when providing answers\n";
        $summary .= "- Total sources: {$documentCount} documents, {$chatCount} chat messages, {$paActsCount} PA acts\n";

        return $summary;
    }

    /**
     * Get user chat history (list of sessions)
     *
     * GDPR-COMPLIANT:
     * - Checks consent before returning history
     * - Logs audit trail for data access
     * - Returns only user's own sessions (authorization)
     * - Pseudonymized session_ids
     *
     * @param User $user The authenticated PA user
     * @param int|null $limit Max number of sessions to return (default: 50)
     * @return array Sessions with first message preview
     */
    public function getUserChatHistory(User $user, ?int $limit = 50): array {
        $logContext = ['user_id' => $user->id, 'limit' => $limit];
        $this->logger->info('[NATAN][History] Retrieving user chat history', $logContext);

        try {
            // GDPR: Check consent before accessing history
            if (!$this->consentService->hasConsent($user, 'allow-personal-data-processing')) {
                $this->logger->warning('[NATAN][History] Access denied: missing consent', $logContext);
                return [
                    'success' => false,
                    'error' => 'consent_required',
                    'sessions' => [],
                ];
            }

            // GDPR: Audit trail for data access
            $this->auditService->logUserAction(
                $user,
                'natan_history_accessed',
                ['limit' => $limit],
                GdprActivityCategory::DATA_ACCESS
            );

            // Retrieve sessions (grouped by session_id, ordered by most recent)
            $sessions = NatanChatMessage::forUser($user->id)
                ->select(
                    'session_id',
                    DB::raw('MIN(created_at) as session_start'),
                    DB::raw('MAX(created_at) as session_end'),
                    DB::raw('COUNT(*) as message_count')
                )
                ->groupBy('session_id')
                ->orderByDesc('session_end')
                ->limit($limit ?? 50)
                ->get();

            // For each session, get first user message as preview + calculate total cost
            $sessionsWithPreview = $sessions->map(function ($session) use ($user) {
                $firstMessage = NatanChatMessage::forSession($session->session_id)
                    ->forUser($user->id)
                    ->userMessages()
                    ->orderBy('created_at', 'asc')
                    ->first();

                // Calculate total tokens and cost for this session
                $sessionMessages = NatanChatMessage::forSession($session->session_id)
                    ->forUser($user->id)
                    ->assistantMessages() // Only assistant messages have token counts
                    ->get();

                $totalInputTokens = $sessionMessages->sum('tokens_input') ?? 0;
                $totalOutputTokens = $sessionMessages->sum('tokens_output') ?? 0;

                // Calculate cost in EUR using AiCreditsService
                $totalCostEUR = 0;
                if ($totalInputTokens > 0 || $totalOutputTokens > 0) {
                    $creditsService = app(\App\Services\AiCreditsService::class);
                    $totalCostEUR = $creditsService->calculateCostEUR($totalInputTokens, $totalOutputTokens);
                }

                return [
                    'session_id' => $session->session_id,
                    'session_start' => $session->session_start,
                    'session_end' => $session->session_end,
                    'message_count' => $session->message_count,
                    'preview' => $firstMessage ? \Illuminate\Support\Str::limit($firstMessage->content, 100) : null,
                    'first_persona' => $firstMessage ? $firstMessage->persona_name : null,
                    'total_tokens' => $totalInputTokens + $totalOutputTokens,
                    'total_cost_eur' => round($totalCostEUR, 4), // 4 decimals for precision
                ];
            });

            $this->logger->info('[NATAN][History] History retrieved successfully', [
                ...$logContext,
                'sessions_count' => $sessionsWithPreview->count(),
            ]);

            return [
                'success' => true,
                'sessions' => $sessionsWithPreview->toArray(),
            ];
        } catch (\Throwable $e) {
            // UEM: Error handling
            $this->errorManager->handle('NATAN_HISTORY_FAILED', $logContext, $e);

            return [
                'success' => false,
                'error' => 'retrieval_failed',
                'sessions' => [],
            ];
        }
    }

    /**
     * Get all messages from a specific session
     *
     * GDPR-COMPLIANT:
     * - Authorization: only session owner can access
     * - Audit trail for data access
     * - Returns sanitized messages (no raw AI tokens/internals)
     *
     * @param string $sessionId The session ID to retrieve
     * @param User $user The authenticated PA user
     * @return array Messages array or error
     */
    public function getSessionMessages(string $sessionId, User $user): array {
        $logContext = ['session_id' => $sessionId, 'user_id' => $user->id];
        $this->logger->info('[NATAN][History] Retrieving session messages', $logContext);

        try {
            // AUTHORIZATION: Check if session belongs to user
            $sessionCheck = NatanChatMessage::forSession($sessionId)
                ->forUser($user->id)
                ->exists();

            if (!$sessionCheck) {
                $this->logger->warning('[NATAN][History] Unauthorized access attempt', $logContext);

                // GDPR: Audit trail for failed access (potential security event)
                $this->auditService->logUserAction(
                    $user,
                    'natan_session_unauthorized_access',
                    ['session_id' => $sessionId],
                    GdprActivityCategory::SECURITY_EVENT
                );

                return [
                    'success' => false,
                    'error' => 'unauthorized',
                    'messages' => [],
                ];
            }

            // GDPR: Audit trail for successful access
            $this->auditService->logUserAction(
                $user,
                'natan_session_accessed',
                ['session_id' => $sessionId],
                GdprActivityCategory::DATA_ACCESS
            );

            // Retrieve all messages (ordered chronologically)
            $messages = NatanChatMessage::forSession($sessionId)
                ->forUser($user->id)
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($message) {
                    // Calculate cost for this specific message (if assistant)
                    $messageCostEUR = 0;
                    if ($message->role === 'assistant' && ($message->tokens_input || $message->tokens_output)) {
                        $creditsService = app(\App\Services\AiCreditsService::class);
                        $messageCostEUR = $creditsService->calculateCostEUR(
                            $message->tokens_input ?? 0,
                            $message->tokens_output ?? 0
                        );
                    }

                    // Return sanitized message (hide internal AI metadata)
                    return [
                        'id' => $message->id,
                        'role' => $message->role,
                        'content' => $message->content,
                        'persona' => $message->getPersonaInfo(),
                        'rag_info' => $message->getRagInfo(),
                        'web_search_enabled' => $message->web_search_enabled,
                        'web_search_count' => $message->web_search_count,
                        'tokens_input' => $message->tokens_input,
                        'tokens_output' => $message->tokens_output,
                        'cost_eur' => round($messageCostEUR, 4), // Cost for this single message
                        'created_at' => $message->created_at->toIso8601String(),
                        'was_helpful' => $message->was_helpful,
                    ];
                });

            $this->logger->info('[NATAN][History] Session retrieved successfully', [
                ...$logContext,
                'messages_count' => $messages->count(),
            ]);

            return [
                'success' => true,
                'messages' => $messages->toArray(),
            ];
        } catch (\Throwable $e) {
            // UEM: Error handling
            $this->errorManager->handle('NATAN_SESSION_RETRIEVAL_FAILED', $logContext, $e);

            return [
                'success' => false,
                'error' => 'retrieval_failed',
                'messages' => [],
            ];
        }
    }

    /**
     * Delete a user session (GDPR: Right to be forgotten)
     *
     * GDPR-COMPLIANT:
     * - Authorization: only session owner can delete
     * - Audit trail for deletion (critical operation)
     * - Permanent deletion (not soft delete)
     * - Returns confirmation
     *
     * @param string $sessionId The session ID to delete
     * @param User $user The authenticated PA user
     * @return array Success status
     */
    public function deleteUserSession(string $sessionId, User $user): array {
        $logContext = ['session_id' => $sessionId, 'user_id' => $user->id];
        $this->logger->info('[NATAN][History] Deleting user session', $logContext);

        try {
            // AUTHORIZATION: Check if session belongs to user
            $messagesCount = NatanChatMessage::forSession($sessionId)
                ->forUser($user->id)
                ->count();

            if ($messagesCount === 0) {
                $this->logger->warning('[NATAN][History] Unauthorized deletion attempt', $logContext);

                // GDPR: Audit trail for failed deletion (security event)
                $this->auditService->logUserAction(
                    $user,
                    'natan_session_delete_unauthorized',
                    ['session_id' => $sessionId],
                    GdprActivityCategory::SECURITY_EVENT
                );

                return [
                    'success' => false,
                    'error' => 'unauthorized',
                ];
            }

            // GDPR: Audit trail BEFORE deletion (critical operation)
            $this->auditService->logUserAction(
                $user,
                'natan_session_deleted',
                [
                    'session_id' => $sessionId,
                    'messages_count' => $messagesCount,
                ],
                GdprActivityCategory::DATA_DELETION
            );

            // Delete all messages in session (permanent)
            $deleted = NatanChatMessage::forSession($sessionId)
                ->forUser($user->id)
                ->delete();

            $this->logger->info('[NATAN][History] Session deleted successfully', [
                ...$logContext,
                'deleted_count' => $deleted,
            ]);

            return [
                'success' => true,
                'deleted_count' => $deleted,
            ];
        } catch (\Throwable $e) {
            // UEM: Error handling
            $this->errorManager->handle('NATAN_SESSION_DELETE_FAILED', $logContext, $e);

            return [
                'success' => false,
                'error' => 'deletion_failed',
            ];
        }
    }

    /**
     * Retrieve unified context using UnifiedKnowledgeService
     * 
     * Sostituisce il flusso RAG + Web Search + Fusion con un'unica chiamata
     * che unifica tutte le fonti (Acts, Web, Memory, Files) con semantic search.
     * 
     * @param string $query User query
     * @param User $user Current user
     * @param array $options Search options
     * @return array Context for Claude with unified sources
     */
    protected function getUnifiedContext(string $query, User $user, array $options = []): array {
        $this->logger->info('[NatanChatService] Starting unified knowledge retrieval', [
            'query_length' => strlen($query),
            'user_id' => $user->id,
            'options' => $options,
        ]);

        try {
            // Prepare options for UnifiedKnowledgeService
            $searchOptions = [
                'search_acts' => $options['use_rag'] ?? true,
                'search_web' => $options['use_web_search'] ?? false,
                'search_memory' => true, // Always search conversation history
                'project_id' => $options['project_id'] ?? null,
                'limit' => 50, // Top 50 most relevant chunks across all sources
            ];

            // Se abbiamo già recuperato acts o web_results, passali direttamente
            if (isset($options['acts_from_reference'])) {
                $searchOptions['acts'] = $options['acts_from_reference'];
            }

            if (isset($options['web_results'])) {
                $searchOptions['web_results'] = $options['web_results'];
            }

            // Call UnifiedKnowledgeService
            $unifiedResults = $this->unifiedKnowledge->search($query, $searchOptions);

            $this->logger->info('[NatanChatService] Unified knowledge retrieved', [
                'total_chunks' => $unifiedResults->count(),
                'top_similarity' => $unifiedResults->first()?->similarity_score,
                'avg_similarity' => $unifiedResults->avg('similarity_score'),
                'sources_breakdown' => $unifiedResults->groupBy('source_type')->map->count()->toArray(),
            ]);

            // Format per Claude prompt
            $formattedContext = $this->unifiedKnowledge->formatForPrompt($unifiedResults);

            // Build context array for Anthropic chat()
            return [
                'unified_sources' => $unifiedResults->toArray(),
                'unified_context' => $formattedContext,
                'stats' => [
                    'total_chunks' => $unifiedResults->count(),
                    'by_type' => $unifiedResults->groupBy('source_type')->map->count()->toArray(),
                    'top_similarity' => $unifiedResults->first()?->similarity_score ?? 0,
                    'avg_similarity' => round($unifiedResults->avg('similarity_score'), 4),
                ],
            ];
        } catch (\Exception $e) {
            $this->logger->error('[NatanChatService] Unified knowledge retrieval failed', [
                'error' => $e->getMessage(),
                'query' => substr($query, 0, 100),
            ]);

            // Fallback: return empty context
            return [
                'unified_sources' => [],
                'unified_context' => '',
                'stats' => [
                    'total_chunks' => 0,
                    'by_type' => [],
                    'error' => $e->getMessage(),
                ],
            ];
        }
    }
}
