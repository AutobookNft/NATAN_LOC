<?php

namespace App\Services\USE;

use App\Models\User;
use App\Services\Gdpr\AuditLogService;
use App\Models\NatanChatMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Ultra\UltraLogManager\UltraLogManager;
use Ultra\ErrorManager\Interfaces\ErrorManagerInterface;
use App\Enums\Gdpr\GdprActivityCategory;

/**
 * USE Audit Service - Audit granulare USE
 * 
 * Saves USE pipeline data to MongoDB:
 * - sources (atomic sources)
 * - claims (verified claims with URS)
 * - query_audit (complete query audit trail)
 * 
 * @package App\Services\USE
 * @version 1.0.0
 */
class UseAuditService
{
    protected AuditLogService $auditService;
    protected UltraLogManager $logger;
    protected ErrorManagerInterface $errorManager;
    protected string $pythonApiUrl;

    public function __construct(
        AuditLogService $auditService,
        UltraLogManager $logger,
        ErrorManagerInterface $errorManager
    ) {
        $this->auditService = $auditService;
        $this->logger = $logger;
        $this->errorManager = $errorManager;
        $this->pythonApiUrl = config('services.python_ai.url', 'http://localhost:8000');
    }

    /**
     * Save USE pipeline result to MongoDB
     * 
     * @param array $useResult USE pipeline result
     * @param User $user User instance
     * @param int $tenantId Tenant ID
     * @param string $answerId Answer ID
     * @return void
     */
    public function saveUseResult(
        array $useResult,
        User $user,
        int $tenantId,
        string $answerId
    ): void {
        try {
            // Save sources (if any)
            if (isset($useResult['chunks_used']) && !empty($useResult['chunks_used'])) {
                $this->saveSources($useResult['chunks_used'], $tenantId);
            }

            // Save claims
            if (isset($useResult['verified_claims']) && !empty($useResult['verified_claims'])) {
                $this->saveClaims($useResult['verified_claims'], $answerId, $tenantId);
            }

            // Save query audit
            $this->saveQueryAudit($useResult, $user, $tenantId);

            // Log to ULM
            $this->logger->info('[UseAuditService] USE result saved', [
                'tenant_id' => $tenantId,
                'user_id' => $user->id,
                'answer_id' => $answerId,
                'status' => $useResult['status'] ?? 'unknown',
                'avg_urs' => $useResult['avg_urs'] ?? null,
                'log_category' => 'USE_RESULT_SAVED'
            ]);
        } catch (\Exception $e) {
            $this->errorManager->handle(
                'USE_AUDIT_SAVE_FAILED',
                [
                    'service' => 'UseAuditService',
                    'method' => 'saveUseResult',
                    'tenant_id' => $tenantId,
                    'user_id' => $user->id,
                    'answer_id' => $answerId
                ],
                $e
            );

            throw $e;
        }
    }

    /**
     * Save sources to MongoDB via Python API
     * 
     * @param array $chunks Chunks with source references
     * @param int $tenantId Tenant ID
     * @return void
     */
    protected function saveSources(array $chunks, int $tenantId): void
    {
        try {
            $response = Http::timeout(10)->post("{$this->pythonApiUrl}/api/v1/audit/sources", [
                'chunks' => $chunks,
                'tenant_id' => $tenantId
            ]);

            if ($response->successful()) {
                $this->logger->info('[UseAuditService] USE sources saved to MongoDB', [
                    'tenant_id' => $tenantId,
                    'sources_count' => count($chunks),
                    'log_category' => 'USE_SOURCES_SAVED'
                ]);
            } else {
                $this->logger->warning('[UseAuditService] Failed to save sources to MongoDB', [
                    'tenant_id' => $tenantId,
                    'status' => $response->status(),
                    'error' => $response->body(),
                    'log_category' => 'USE_SOURCES_SAVE_WARNING'
                ]);
            }
        } catch (\Exception $e) {
            // Non-blocking error: log but don't throw
            $this->logger->error('[UseAuditService] Exception saving sources to MongoDB', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
                'log_category' => 'USE_SOURCES_SAVE_ERROR'
            ]);
        }
    }

    /**
     * Save claims to MongoDB via Python API
     * 
     * @param array $claims Verified claims
     * @param string $answerId Answer ID
     * @param int $tenantId Tenant ID
     * @return void
     */
    protected function saveClaims(array $claims, string $answerId, int $tenantId): void
    {
        try {
            $response = Http::timeout(10)->post("{$this->pythonApiUrl}/api/v1/audit/claims", [
                'claims' => $claims,
                'answer_id' => $answerId,
                'tenant_id' => $tenantId
            ]);

            if ($response->successful()) {
                $this->logger->info('[UseAuditService] USE claims saved to MongoDB', [
                    'tenant_id' => $tenantId,
                    'answer_id' => $answerId,
                    'claims_count' => count($claims),
                    'log_category' => 'USE_CLAIMS_SAVED'
                ]);
            } else {
                $this->logger->warning('[UseAuditService] Failed to save claims to MongoDB', [
                    'tenant_id' => $tenantId,
                    'answer_id' => $answerId,
                    'status' => $response->status(),
                    'error' => $response->body(),
                    'log_category' => 'USE_CLAIMS_SAVE_WARNING'
                ]);
            }
        } catch (\Exception $e) {
            // Non-blocking error: log but don't throw
            $this->logger->error('[UseAuditService] Exception saving claims to MongoDB', [
                'tenant_id' => $tenantId,
                'answer_id' => $answerId,
                'error' => $e->getMessage(),
                'log_category' => 'USE_CLAIMS_SAVE_ERROR'
            ]);
        }
    }

    /**
     * Save query audit to MongoDB via Python API
     * 
     * @param array $useResult USE pipeline result
     * @param User $user User instance
     * @param int $tenantId Tenant ID
     * @return void
     */
    protected function saveQueryAudit(array $useResult, User $user, int $tenantId): void
    {
        $auditData = [
            'tenant_id' => $tenantId,
            'user_id' => $user->id,
            'question' => $useResult['question'] ?? '',
            'intent' => $useResult['classification']['intent'] ?? 'unknown',
            'classifier_conf' => $useResult['classification']['confidence'] ?? 0.0,
            'router_action' => $useResult['routing']['action'] ?? 'unknown',
            'status' => $useResult['status'] ?? 'unknown',
            'verification_status' => $useResult['verification_status'] ?? 'unknown',
            'avg_urs' => $useResult['avg_urs'] ?? null,
            'verified_claims_count' => count($useResult['verified_claims'] ?? []),
            'blocked_claims_count' => count($useResult['blocked_claims'] ?? []),
            'answer_id' => $useResult['answer_id'] ?? null,
            'model_used' => $useResult['model_used'] ?? null,
            'tokens_used' => $useResult['tokens_used'] ?? null
        ];

        try {
            // Save to MongoDB via Python API
            $response = Http::timeout(10)->post("{$this->pythonApiUrl}/api/v1/audit/query", $auditData);

            if ($response->successful()) {
                $this->logger->info('[UseAuditService] USE query audit saved to MongoDB', array_merge($auditData, [
                    'log_category' => 'USE_QUERY_AUDIT_SAVED'
                ]));
            } else {
                $this->logger->warning('[UseAuditService] Failed to save query audit to MongoDB', [
                    'status' => $response->status(),
                    'error' => $response->body(),
                    'log_category' => 'USE_QUERY_AUDIT_WARNING'
                ]);
            }
        } catch (\Exception $e) {
            // Non-blocking error: log but don't throw
            $this->logger->error('[UseAuditService] Exception saving query audit to MongoDB', [
                'error' => $e->getMessage(),
                'log_category' => 'USE_QUERY_AUDIT_ERROR'
            ]);
        }

        // Also save to Laravel audit log (GDPR) - this is blocking
        $this->auditService->logUserAction(
            user: $user,
            action: 'use_query',
            context: $auditData,
            category: GdprActivityCategory::DATA_ACCESS
        );
    }

    /**
     * Get query audit history for user from MongoDB via Python API
     * 
     * @param User $user User instance
     * @param int $tenantId Tenant ID
     * @param int|null $limit Limit results (null = all, STATISTICS RULE)
     * @return array Audit records
     */
    public function getAuditHistory(User $user, int $tenantId, ?int $limit = null): array
    {
        $limit = $limit ?? 50;
        
        try {
            $response = Http::timeout(10)->get("{$this->pythonApiUrl}/api/v1/audit/history", [
                'tenant_id' => $tenantId,
                'user_id' => $user->id,
                'limit' => $limit ?? 1000  // Max limit for API call
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['results'] ?? [];
            } else {
                $this->logger->warning('[UseAuditService] Failed to get audit history', [
                    'status' => $response->status(),
                    'error' => $response->body(),
                    'log_category' => 'USE_AUDIT_HISTORY_WARNING'
                ]);
                return [];
            }
        } catch (\Exception $e) {
            $this->logger->error('[UseAuditService] Exception getting audit history', [
                'error' => $e->getMessage(),
                'log_category' => 'USE_AUDIT_HISTORY_ERROR'
            ]);
            return [];
        }
    }

    /**
     * Get claims for answer from MongoDB via Python API
     * 
     * @param string $answerId Answer ID
     * @param int $tenantId Tenant ID
     * @return array Claims
     */
    public function getClaimsForAnswer(string $answerId, int $tenantId): array
    {
        try {
            $response = Http::timeout(10)->get("{$this->pythonApiUrl}/api/v1/audit/claims/{$answerId}", [
                'tenant_id' => $tenantId
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['claims'] ?? [];
            } else {
                $this->logger->warning('[UseAuditService] Failed to get claims for answer', [
                    'answer_id' => $answerId,
                    'status' => $response->status(),
                    'error' => $response->body(),
                    'log_category' => 'USE_CLAIMS_GET_WARNING'
                ]);
                return [];
            }
        } catch (\Exception $e) {
            $this->logger->error('[UseAuditService] Exception getting claims for answer', [
                'answer_id' => $answerId,
                'error' => $e->getMessage(),
                'log_category' => 'USE_CLAIMS_GET_ERROR'
            ]);
            return [];
        }
    }
}
