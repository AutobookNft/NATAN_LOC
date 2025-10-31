<?php

namespace App\Services\USE;

use App\Models\User;
use App\Services\DataSanitizerService;
use App\Services\Gdpr\AuditLogService;
use App\Services\Gdpr\ConsentService;
use App\Enums\Gdpr\GdprActivityCategory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Ultra\UltraLogManager\UltraLogManager;
use Ultra\ErrorManager\Interfaces\ErrorManagerInterface;

/**
 * USE Orchestrator - Main orchestrator for Ultra Semantic Engine
 *
 * Coordinates Laravel <-> Python FastAPI communication
 * Handles complete USE pipeline: Classifier -> Router -> Retriever -> Neurale -> Verifier
 *
 * @package App\Services\USE
 * @version 1.0.0
 */
class UseOrchestrator
{
    protected DataSanitizerService $sanitizer;
    protected ConsentService $consentService;
    protected AuditLogService $auditService;
    protected UseAuditService $useAuditService;
    protected UltraLogManager $logger;
    protected ErrorManagerInterface $errorManager;
    protected string $pythonApiUrl;

    public function __construct(
        DataSanitizerService $sanitizer,
        ConsentService $consentService,
        AuditLogService $auditService,
        UseAuditService $useAuditService,
        UltraLogManager $logger,
        ErrorManagerInterface $errorManager
    ) {
        $this->sanitizer = $sanitizer;
        $this->consentService = $consentService;
        $this->auditService = $auditService;
        $this->useAuditService = $useAuditService;
        $this->logger = $logger;
        $this->errorManager = $errorManager;
        $this->pythonApiUrl = config('services.python_ai.url', 'http://localhost:8000');
    }

    /**
     * Process query through USE pipeline
     *
     * @param string $question User question
     * @param User $user User instance
     * @param int $tenantId Tenant ID
     * @param string $persona Persona name (default: strategic)
     * @param string|null $queryEmbedding Optional pre-computed embedding
     * @return array USE pipeline result
     */
    public function processQuery(
        string $question,
        User $user,
        int $tenantId,
        string $persona = 'strategic',
        ?array $queryEmbedding = null
    ): array {
        try {
            // Note: Question text doesn't need sanitization (it's user input, not internal data)
            // DataSanitizerService is for sanitizing internal data (acts, documents) before sending to AI

            // Check consent
            if (!$this->consentService->hasConsent($user, 'ai_processing')) {
                throw new \Exception(__('natan.errors.no_ai_consent'));
            }

            // Log query start
            $this->logger->info('[UseOrchestrator] USE query start', [
                'user_id' => $user->id,
                'tenant_id' => $tenantId,
                'question' => $question,
                'persona' => $persona,
                'log_category' => 'USE_QUERY_START'
            ]);

            // Call Python FastAPI USE endpoint
            $response = Http::timeout(120)
                ->post("{$this->pythonApiUrl}/api/v1/use/query", [
                    'question' => $question,
                    'tenant_id' => $tenantId,
                    'persona' => $persona,
                    'model' => config('natan.default_model', 'anthropic.sonnet-3.5'),
                    'query_embedding' => $queryEmbedding
                ]);

            if (!$response->successful()) {
                throw new \Exception("Python API error: " . $response->body());
            }

            $result = $response->json();

            // Save USE result to MongoDB (non-blocking if fails)
            if (isset($result['answer_id']) && $result['status'] === 'success') {
                try {
                    $this->useAuditService->saveUseResult(
                        $result,
                        $user,
                        $tenantId,
                        $result['answer_id']
                    );
                } catch (\Exception $e) {
                    // Non-blocking: log error but don't fail the request
                    $this->logger->error('[UseOrchestrator] Failed to save USE result to MongoDB', [
                        'user_id' => $user->id,
                        'tenant_id' => $tenantId,
                        'answer_id' => $result['answer_id'] ?? null,
                        'error' => $e->getMessage(),
                        'log_category' => 'USE_AUDIT_SAVE_ERROR'
                    ]);
                }
            }

            // Audit log (Laravel GDPR audit)
            $this->auditService->logUserAction(
                user: $user,
                action: 'use_query',
                context: [
                    'question' => $question,
                    'status' => $result['status'] ?? 'unknown',
                    'avg_urs' => $result['avg_urs'] ?? null,
                    'verified_claims_count' => count($result['verified_claims'] ?? []),
                    'blocked_claims_count' => count($result['blocked_claims'] ?? []),
                    'answer_id' => $result['answer_id'] ?? null,
                    'persona' => $persona
                ],
                category: GdprActivityCategory::AI_PROCESSING
            );

            // Log result
            $this->logger->info('[UseOrchestrator] USE query complete', [
                'user_id' => $user->id,
                'tenant_id' => $tenantId,
                'status' => $result['status'] ?? 'unknown',
                'avg_urs' => $result['avg_urs'] ?? null,
                'answer_id' => $result['answer_id'] ?? null,
                'log_category' => 'USE_QUERY_COMPLETE'
            ]);

            return $result;

        } catch (\Exception $e) {
            $this->errorManager->handle(
                'USE_QUERY_FAILED',
                [
                    'service' => 'UseOrchestrator',
                    'method' => 'processQuery',
                    'tenant_id' => $tenantId,
                    'user_id' => $user->id ?? null,
                    'question' => $question ?? 'N/A'
                ],
                $e
            );

            throw $e;
        }
    }

    /**
     * Generate embedding for query (calls Python API)
     *
     * @param string $text Text to embed
     * @param int $tenantId Tenant ID
     * @return array Embedding vector
     */
    public function generateEmbedding(string $text, int $tenantId): array
    {
        try {
            $response = Http::timeout(30)
                ->post("{$this->pythonApiUrl}/api/v1/embed", [
                    'text' => $text,
                    'tenant_id' => $tenantId,
                    'model' => config('natan.embedding_model', 'text-embedding-3-small')
                ]);

            if (!$response->successful()) {
                throw new \Exception("Python API embedding error: " . $response->body());
            }

            $result = $response->json();
            return $result['embedding'] ?? [];

        } catch (\Exception $e) {
            $this->errorManager->handle(
                'USE_EMBEDDING_FAILED',
                [
                    'service' => 'UseOrchestrator',
                    'method' => 'generateEmbedding',
                    'tenant_id' => $tenantId
                ],
                $e
            );

            throw $e;
        }
    }

    /**
     * Process query with embedding generation
     * Convenience method that generates embedding first
     *
     * @param string $question User question
     * @param User $user User instance
     * @param int $tenantId Tenant ID
     * @param string $persona Persona name
     * @return array USE pipeline result
     */
    public function processQueryWithEmbedding(
        string $question,
        User $user,
        int $tenantId,
        string $persona = 'strategic'
    ): array {
        // Generate embedding
        $embedding = $this->generateEmbedding($question, $tenantId);

        // Process with embedding
        return $this->processQuery(
            question: $question,
            user: $user,
            tenantId: $tenantId,
            persona: $persona,
            queryEmbedding: $embedding
        );
    }
}
