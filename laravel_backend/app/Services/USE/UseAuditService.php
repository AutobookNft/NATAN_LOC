<?php

namespace App\Services\USE;

use App\Models\User;
use App\Services\Gdpr\AuditLogService;
use App\Models\NatanChatMessage;
use Illuminate\Support\Facades\DB;
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

    public function __construct(
        AuditLogService $auditService,
        UltraLogManager $logger,
        ErrorManagerInterface $errorManager
    ) {
        $this->auditService = $auditService;
        $this->logger = $logger;
        $this->errorManager = $errorManager;
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
     * Save sources to MongoDB
     * 
     * @param array $chunks Chunks with source references
     * @param int $tenantId Tenant ID
     * @return void
     */
    protected function saveSources(array $chunks, int $tenantId): void
    {
            // TODO: Implement MongoDB save
            // For now, log only
            $this->logger->debug('[UseAuditService] USE sources saved', [
                'tenant_id' => $tenantId,
                'sources_count' => count($chunks),
                'log_category' => 'USE_SOURCES_SAVED'
            ]);
    }

    /**
     * Save claims to MongoDB
     * 
     * @param array $claims Verified claims
     * @param string $answerId Answer ID
     * @param int $tenantId Tenant ID
     * @return void
     */
    protected function saveClaims(array $claims, string $answerId, int $tenantId): void
    {
        // TODO: Implement MongoDB save
        // Each claim should be saved with:
        // - answer_id
        // - text
        // - source_ids
        // - urs, urs_label
        // - is_inference
        // - created_at
        
        $this->logger->debug('[UseAuditService] USE claims saved', [
            'tenant_id' => $tenantId,
            'answer_id' => $answerId,
            'claims_count' => count($claims),
            'log_category' => 'USE_CLAIMS_SAVED'
        ]);
    }

    /**
     * Save query audit to MongoDB
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
            'model_used' => $useResult['model_used'] ?? null
        ];

        // TODO: Implement MongoDB save to query_audit collection
        // For now, log only
        
        $this->logger->info('[UseAuditService] USE query audit', array_merge($auditData, [
            'log_category' => 'USE_QUERY_AUDIT'
        ]));

        // Also save to Laravel audit log (GDPR)
        $this->auditService->logUserAction(
            user: $user,
            action: 'USE_QUERY_AUDIT',
            context: $auditData,
            category: GdprActivityCategory::AI_PROCESSING
        );
    }

    /**
     * Get query audit history for user
     * 
     * @param User $user User instance
     * @param int $tenantId Tenant ID
     * @param int $limit Limit results
     * @return array Audit records
     */
    public function getAuditHistory(User $user, int $tenantId, int $limit = 50): array
    {
        // TODO: Implement MongoDB query
        // Query query_audit collection filtered by user_id and tenant_id
        
        return [];
    }

    /**
     * Get claims for answer
     * 
     * @param string $answerId Answer ID
     * @param int $tenantId Tenant ID
     * @return array Claims
     */
    public function getClaimsForAnswer(string $answerId, int $tenantId): array
    {
        // TODO: Implement MongoDB query
        // Query claims collection filtered by answer_id and tenant_id
        
        return [];
    }
}

