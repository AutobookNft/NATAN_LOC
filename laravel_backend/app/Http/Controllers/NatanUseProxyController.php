<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Gdpr\ConsentService;
use App\Services\USE\UseOrchestrator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Ultra\UltraLogManager\UltraLogManager;
use Ultra\ErrorManager\Interfaces\ErrorManagerInterface;

/**
 * @package App\Http\Controllers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC - USE Proxy Controller)
 * @date 2025-11-02
 * @purpose Proxy controller for USE queries - forwards requests to Python FastAPI with auth and audit
 */
class NatanUseProxyController extends Controller
{
    protected UseOrchestrator $useOrchestrator;
    protected ConsentService $consentService;
    protected UltraLogManager $logger;
    protected ErrorManagerInterface $errorManager;
    protected string $pythonApiUrl;

    public function __construct(
        UseOrchestrator $useOrchestrator,
        ConsentService $consentService,
        UltraLogManager $logger,
        ErrorManagerInterface $errorManager
    ) {
        $this->useOrchestrator = $useOrchestrator;
        $this->consentService = $consentService;
        $this->logger = $logger;
        $this->errorManager = $errorManager;
        $this->pythonApiUrl = config('services.python_ai.url', 'http://localhost:9000');
    }

    /**
     * Proxy USE query to Python FastAPI
     * Handles authentication, consent check, and audit logging
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function proxyUseQuery(Request $request): JsonResponse
    {
        try {
            // 1. ULM: Log proxy request start
            $this->logger->info('[NatanUseProxy] USE query proxy start', [
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'log_category' => 'USE_PROXY_START'
            ]);

            // 2. Verify authentication
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => __('natan.errors.authentication_required'),
                ], 401);
            }

            // 3. Validate request
            $validated = $request->validate([
                'question' => 'required|string|max:5000',
                'tenant_id' => 'required|integer|min:1',
                'persona' => 'nullable|string|max:100',
                'model' => 'nullable|string|max:100',
                'query_embedding' => 'nullable|array',
            ]);

            // 4. Resolve tenant_id (use user's tenant if not provided or different)
            $tenantId = $validated['tenant_id'] ?? $user->tenant_id ?? null;
            if (!$tenantId) {
                return response()->json([
                    'status' => 'error',
                    'message' => __('natan.errors.tenant_id_required'),
                ], 400);
            }

            // 5. Check GDPR consent for AI processing
            if (!$this->consentService->hasConsent($user, 'ai_processing')) {
                return response()->json([
                    'status' => 'error',
                    'message' => __('natan.errors.no_ai_consent'),
                ], 403);
            }

            // 6. Call USE Orchestrator (handles Python API call, logging, audit)
            $result = $this->useOrchestrator->processQuery(
                question: $validated['question'],
                user: $user,
                tenantId: $tenantId,
                persona: $validated['persona'] ?? 'strategic',
                queryEmbedding: $validated['query_embedding'] ?? null
            );

            // 7. ULM: Log proxy success
            $this->logger->info('[NatanUseProxy] USE query proxy completed', [
                'user_id' => $user->id,
                'tenant_id' => $tenantId,
                'verification_status' => $result['verification_status'] ?? null,
                'verified_claims_count' => count($result['verified_claims'] ?? []),
                'log_category' => 'USE_PROXY_SUCCESS'
            ]);

            // 8. Return result (preserve all Python API fields)
            return response()->json($result);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors
            $this->logger->warning('[NatanUseProxy] USE query validation failed', [
                'user_id' => Auth::id(),
                'errors' => $e->errors(),
                'log_category' => 'USE_PROXY_VALIDATION'
            ]);

            return response()->json([
                'status' => 'error',
                'message' => __('natan.errors.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // UEM: Handle unexpected errors
            return $this->errorManager->handle('USE_PROXY_FAILED', [
                'user_id' => Auth::id(),
                'tenant_id' => $request->input('tenant_id'),
                'question' => $request->input('question', '') ? substr($request->input('question', ''), 0, 100) : null,
                'error_message' => $e->getMessage(),
            ], $e);
        }
    }

    /**
     * Proxy embedding generation to Python FastAPI
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function proxyEmbedding(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => __('natan.errors.authentication_required'),
                ], 401);
            }

            $validated = $request->validate([
                'text' => 'required|string|max:10000',
                'tenant_id' => 'required|integer|min:1',
            ]);

            $tenantId = $validated['tenant_id'] ?? $user->tenant_id ?? null;
            if (!$tenantId) {
                return response()->json([
                    'status' => 'error',
                    'message' => __('natan.errors.tenant_id_required'),
                ], 400);
            }

            // Generate embedding via orchestrator
            $embedding = $this->useOrchestrator->generateEmbedding(
                text: $validated['text'],
                tenantId: $tenantId
            );

            return response()->json([
                'status' => 'success',
                'embedding' => $embedding,
            ]);
        } catch (\Exception $e) {
            return $this->errorManager->handle('USE_EMBEDDING_PROXY_FAILED', [
                'user_id' => Auth::id(),
                'tenant_id' => $request->input('tenant_id'),
                'error_message' => $e->getMessage(),
            ], $e);
        }
    }
}
