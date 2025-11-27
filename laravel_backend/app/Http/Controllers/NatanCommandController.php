<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ChatCommands\CommandResult;
use App\Services\ChatCommands\NatanChatCommandService;
use App\Services\ChatCommands\NaturalLanguageQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Ultra\ErrorManager\Interfaces\ErrorManagerInterface;
use Ultra\UltraLogManager\UltraLogManager;

/**
 * @package App\Http\Controllers\Natan
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (FlorenceEGI - Direct Command Queries)
 * @date 2025-11-08
 * @purpose Gestisce comandi diretti nella chat (bypass AI) con audit e policy ruoli
 */
class NatanCommandController extends Controller
{
    private string $pythonServiceUrl;
    
    public function __construct(
        private UltraLogManager $logger,
        private ErrorManagerInterface $errorManager,
        private NatanChatCommandService $commandService,
        private NaturalLanguageQueryService $naturalQueryService
    ) {
        $this->pythonServiceUrl = config('services.python_ai.url', 'http://localhost:8001');
    }

    public function execute(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'command' => 'required|string|max:2000',
        ]);

        $user = Auth::user();

        $this->logger->info('[NatanCommandController] Received command', [
            'user_id' => $user?->id,
            'command' => $validated['command'],
            'log_category' => 'CHAT_COMMAND',
        ]);

        try {
            $result = $this->commandService->execute($validated['command'], $user);

            return response()->json([
                'success' => true,
                'command' => $result->commandName(),
                'message' => $result->message(),
                'rows' => $result->rows(),
                'metadata' => $result->metadata(),
                'verification_status' => 'direct_query',
            ]);
        } catch (RuntimeException $e) {
            Log::warning('[NatanCommandController] Command validation error', [
                'user_id' => $user?->id,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return $this->errorManager->handle('CHAT_COMMAND_FAILED', [
                'user_id' => $user?->id,
                'command' => $validated['command'],
            ], $e);
        }
    }

    /**
     * Stima complessità query e restituisce processing notice
     * Il frontend chiama questo PRIMA di natural() per mostrare il messaggio subito
     */
    public function estimateProcessing(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'text' => 'required|string|max:2000',
        ]);
        
        $text = $validated['text'];
        
        try {
            $pythonApiUrl = config('services.python_ai.url', 'http://localhost:8001');
            
            $payload = [
                'messages' => [
                    ['role' => 'user', 'content' => $text]
                ],
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id,
                'persona' => 'strategic',
                'use_rag_fortress' => true,
            ];
            
            $response = Http::timeout(5)
                ->post("{$pythonApiUrl}/api/v1/chat/estimate", $payload);
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
            
            // Fallback se estimate non funziona
            return response()->json([
                'will_take_time' => false,
                'estimated_seconds' => 5,
                'processing_notice' => null,
                'query_type' => 'unknown',
                'num_documents_estimated' => 0,
            ]);
            
        } catch (\Throwable $e) {
            Log::error('[NatanCommandController] Estimate error: ' . $e->getMessage());
            return response()->json([
                'will_take_time' => false,
                'estimated_seconds' => 5,
                'processing_notice' => null,
                'query_type' => 'error',
                'num_documents_estimated' => 0,
            ]);
        }
    }

    public function natural(Request $request): JsonResponse
    {
        // TEMPORARY: Force log to file to verify execution
        file_put_contents('/tmp/natan_memory_debug.log', date('Y-m-d H:i:s') . " - natural() called\n", FILE_APPEND);
        
        $validated = $request->validate([
            'text' => 'required|string|max:2000',
        ]);

        $text = $validated['text'];
        
        file_put_contents('/tmp/natan_memory_debug.log', "Text: {$text}\n", FILE_APPEND);
        
        // Get user early for memory check
        $user = Auth::user();
        
        if (!$user instanceof User) {
            return response()->json([
                'success' => false,
                'code' => 'unauthenticated',
                'message' => __('natan.errors.authentication_required'),
            ], 401);
        }

        file_put_contents('/tmp/natan_memory_debug.log', "User ID: {$user->id}\n", FILE_APPEND);

        // Check for memory request FIRST - before any other processing
        $memoryService = app(\App\Services\MemoryDetectionService::class);
        $memoryResult = $memoryService->processMessage($user->id, $user->tenant_id, $text);
        
        file_put_contents('/tmp/natan_memory_debug.log', "Memory result: " . json_encode($memoryResult) . "\n", FILE_APPEND);
        
        if ($memoryResult) {
            // Memory saved - return success with acknowledgment
            $this->logger->info('[NatanCommandController] Memory detected and saved', [
                'user_id' => $user->id,
                'memory_id' => $memoryResult['memory_id'],
                'type' => $memoryResult['type'],
                'log_category' => 'MEMORY_SAVED',
            ]);

            return response()->json([
                'success' => true,
                'code' => 'memory_saved',
                'message' => '✅ Ho memorizzato questa informazione e la utilizzerò nelle conversazioni future.',
                'rows' => [],
                'metadata' => [
                    'memory_saved' => true,
                    'memory_id' => $memoryResult['memory_id'],
                    'memory_type' => $memoryResult['type'],
                ],
                'verification_status' => 'memory_saved',
            ]);
        }

        Log::debug('[NatanCommandController] natural query received', [
            'user_id' => $user->id,
            'text' => $text,
        ]);

        $lowerText = mb_strtolower($text, 'UTF-8');
        $blacklist = array_map(
            static fn (string $term) => mb_strtolower($term, 'UTF-8'),
            config('natan.natural_query.blacklist', [])
        );

        foreach ($blacklist as $term) {
            if ($term !== '' && str_contains($lowerText, $term)) {
                $this->logger->warning('[NatanCommandController] Natural query blocked by blacklist', [
                    'user_id' => $user->id,
                    'term' => $term,
                    'log_category' => 'NATURAL_QUERY_BLOCKED',
                ]);

                return response()->json([
                    'success' => false,
                    'code' => 'blacklisted',
                    'message' => __('natan.commands.natural.errors.blacklisted'),
                ], 422);
            }
        }

        try {
            // Use new /api/v1/chat endpoint with classification and memory support
            $pythonApiUrl = config('services.python_ai.url', 'http://localhost:8001');
            
            $payload = [
                'messages' => [
                    ['role' => 'user', 'content' => $text]
                ],
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id,
                'persona' => 'strategic',
                'use_rag_fortress' => true,
            ];
            
            // STEP 1: Chiama /estimate per ottenere processing notice SUBITO
            $estimateResponse = Http::timeout(5)
                ->post("{$pythonApiUrl}/api/v1/chat/estimate", $payload);
            
            $processingNotice = null;
            if ($estimateResponse->successful()) {
                $estimate = $estimateResponse->json();
                if ($estimate['will_take_time'] ?? false) {
                    $processingNotice = $estimate['processing_notice'] ?? null;
                    
                    // Se c'è un processing notice, restituiscilo immediatamente come risposta parziale
                    // Il frontend può poi fare polling o attendere
                    if ($processingNotice) {
                        Log::info('[NatanCommandController] Long processing detected', [
                            'estimated_seconds' => $estimate['estimated_seconds'] ?? 0,
                            'num_documents' => $estimate['num_documents_estimated'] ?? 0,
                        ]);
                    }
                }
            }
            
            // STEP 2: Procedi con la chiamata reale (questa prenderà tempo)
            $response = Http::timeout(240)
                ->post("{$pythonApiUrl}/api/v1/chat", $payload);

            if (!$response->successful()) {
                $error = $response->json() ?? ['detail' => 'Unknown error'];
                $errorMessage = $error['detail'] ?? $error['message'] ?? "HTTP {$response->status()}";
                
                Log::error('[NatanCommandController] Chat API error', [
                    'user_id' => $user->id,
                    'status' => $response->status(),
                    'error' => $errorMessage,
                ]);
                
                return response()->json([
                    'success' => false,
                    'code' => 'api_error',
                    'message' => is_string($errorMessage) ? $errorMessage : json_encode($errorMessage),
                    'metadata' => [],
                    'verification_status' => 'error',
                ], 500);
            }

            $result = $response->json();
            
            return response()->json([
                'success' => true,
                'command' => 'chat',
                'message' => $result['message'] ?? '',
                'rows' => [],
                'metadata' => [
                    'model' => $result['model'] ?? null,
                    'citations' => $result['citations'] ?? [],
                    'urs_score' => $result['urs_score'] ?? null,
                    'processing_notice' => $processingNotice,  // Include notice se c'era
                ],
                'verification_status' => $result['urs_explanation'] ?? 'processed',
            ]);
            
        } catch (\Throwable $throwable) {
            return $this->errorManager->handle('CHAT_COMMAND_FAILED', [
                'user_id' => $user?->id,
                'command' => '[natural]',
                'message' => $throwable->getMessage(),
            ], $throwable);
        }
    }
}


