<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\NatanChatMessage;
use App\Services\CostCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * @package App\Http\Controllers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-01
 * @purpose Controller per salvataggio e gestione conversazioni NATAN
 */
class NatanConversationController extends Controller
{
    /**
     * Save or update conversation with messages
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function saveConversation(Request $request): JsonResponse
    {
        // Debug: log request per verificare se arriva
        \Log::info('NatanConversationController::saveConversation called', [
            'method' => $request->method(),
            'path' => $request->path(),
            'full_url' => $request->fullUrl(),
            'has_user' => Auth::check(),
            'user_id' => Auth::id(),
        ]);
        
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:500',
            'persona' => 'nullable|string|max:100',
            'project_id' => 'nullable|integer|exists:collections,id',
            'messages' => 'required|array',
            'messages.*.id' => 'required|string',
            'messages.*.role' => 'required|in:user,assistant',
            'messages.*.content' => 'required|string',
            'messages.*.timestamp' => 'required|date',
            'messages.*.claims' => 'nullable|array',
            'messages.*.sources' => 'nullable|array',
            'messages.*.verification_status' => 'nullable|string',
            'messages.*.tokens_used' => 'nullable|array',
            'messages.*.model_used' => 'nullable|string',
            'messages.*.command_name' => 'nullable|string|max:255',
            'messages.*.command_result' => 'nullable|array',
            // RAG-Fortress fields
            'messages.*.urs_score' => 'nullable|numeric|min:0|max:100',
            'messages.*.urs_explanation' => 'nullable|string|max:1000',
            'messages.*.claims_used' => 'nullable|array',
            'messages.*.sources_list' => 'nullable|array',
            'messages.*.gaps_detected' => 'nullable|array',
            'messages.*.hallucinations_found' => 'nullable|array',
            'messages.*.avg_urs' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        try {
            DB::beginTransaction();

            // Use session_id from conversation_id (or generate new)
            $sessionId = $request->input('conversation_id') ?? 'natan_' . uniqid('', true);
            $messagesMetadata = $request->input('messages', []);
            $persona = $request->input('persona', 'strategic');
            $projectId = $request->input('project_id', null);

            // Save each message as separate row in natan_chat_messages (existing table structure)
            // Only save new messages (check by id or content hash to avoid duplicates)
            $savedMessages = [];
            foreach ($messagesMetadata as $msg) {
                // Check if message already exists (by id if provided, or by content hash)
                $existingMessage = null;
                if (isset($msg['id']) && $msg['id']) {
                    // Try to find by external message ID stored in a field (if available)
                    // For now, check by session_id, role, content, and approximate timestamp
                    $msgTimestamp = isset($msg['timestamp']) ? \Carbon\Carbon::parse($msg['timestamp']) : null;
                    $existingMessage = NatanChatMessage::where('session_id', $sessionId)
                        ->where('user_id', $user->id)
                        ->where('role', $msg['role'])
                        ->where('content', $msg['content'])
                        ->when($msgTimestamp, function ($query) use ($msgTimestamp) {
                            // Check if message was created within 5 seconds of this timestamp
                            return $query->whereBetween('created_at', [
                                $msgTimestamp->copy()->subSeconds(5),
                                $msgTimestamp->copy()->addSeconds(5),
                            ]);
                        })
                        ->first();
                }

                // If message already exists, update it with new data (especially claims/sources)
                if ($existingMessage) {
                    // Update existing message with new claims/sources if they're provided
                    if ($msg['role'] === 'assistant') {
                        $ragSources = [
                            'claims' => $msg['claims'] ?? [],
                            'sources' => $msg['sources'] ?? [],
                            'verification_status' => $msg['verification_status'] ?? $msg['verificationStatus'] ?? null,
                            'avg_urs' => $msg['avg_urs'] ?? $msg['avgUrs'] ?? null,
                        ];
                        // RAG-Fortress fields
                        if (isset($msg['urs_score'])) {
                            $ragSources['urs_score'] = $msg['urs_score'];
                        }
                        if (isset($msg['urs_explanation'])) {
                            $ragSources['urs_explanation'] = $msg['urs_explanation'];
                        }
                        if (isset($msg['claims_used']) && is_array($msg['claims_used'])) {
                            $ragSources['claims_used'] = $msg['claims_used'];
                        }
                        if (isset($msg['sources_list']) && is_array($msg['sources_list'])) {
                            $ragSources['sources_list'] = $msg['sources_list'];
                        }
                        if (isset($msg['gaps_detected']) && is_array($msg['gaps_detected'])) {
                            $ragSources['gaps_detected'] = $msg['gaps_detected'];
                        }
                        if (isset($msg['hallucinations_found']) && is_array($msg['hallucinations_found'])) {
                            $ragSources['hallucinations_found'] = $msg['hallucinations_found'];
                        }
                        if (isset($msg['command_name'])) {
                            $ragSources['command_name'] = $msg['command_name'];
                        }
                        if (isset($msg['command_result'])) {
                            $ragSources['command_result'] = $msg['command_result'];
                        }
                        
                        // Update tokens and model if provided
                        $updateData = [];
                        if (isset($msg['tokens_used']) && is_array($msg['tokens_used'])) {
                            $tokensInput = $msg['tokens_used']['input'] ?? $msg['tokens_used']['prompt'] ?? 0;
                            $tokensOutput = $msg['tokens_used']['output'] ?? $msg['tokens_used']['completion'] ?? 0;
                            if ($tokensInput > 0) $updateData['tokens_input'] = $tokensInput;
                            if ($tokensOutput > 0) $updateData['tokens_output'] = $tokensOutput;
                        }
                        if (isset($msg['model_used'])) {
                            $updateData['ai_model'] = $msg['model_used'];
                        }
                        
                        // Update rag_sources if we have new data
                        if (
                            !empty($ragSources['claims'])
                            || !empty($ragSources['sources'])
                            || isset($ragSources['command_name'])
                            || isset($ragSources['command_result'])
                        ) {
                            $updateData['rag_sources'] = $ragSources;
                        }
                        
                        // Update the message if we have new data
                        if (!empty($updateData)) {
                            $existingMessage->update($updateData);
                            $existingMessage->refresh();
                        }
                    }
                    
                    $savedMessages[] = $existingMessage;
                    continue;
                }

                $tokensInput = 0;
                $tokensOutput = 0;
                $aiModel = null;

                // Extract tokens from metadata
                if (isset($msg['tokens_used']) && is_array($msg['tokens_used'])) {
                    $tokensInput = $msg['tokens_used']['input'] ?? $msg['tokens_used']['prompt'] ?? 0;
                    $tokensOutput = $msg['tokens_used']['output'] ?? $msg['tokens_used']['completion'] ?? 0;
                }
                $aiModel = $msg['model_used'] ?? null;

                // Extract sources, claims, and verification status for assistant messages
                $ragSources = null;
                if ($msg['role'] === 'assistant') {
                    // Store claims, sources, and verification status in rag_sources field
                    $ragSources = [
                        'claims' => $msg['claims'] ?? [],
                        'sources' => $msg['sources'] ?? [],
                        'verification_status' => $msg['verification_status'] ?? $msg['verificationStatus'] ?? null,
                        'avg_urs' => $msg['avg_urs'] ?? $msg['avgUrs'] ?? null,
                    ];
                    // RAG-Fortress fields
                    if (isset($msg['urs_score'])) {
                        $ragSources['urs_score'] = $msg['urs_score'];
                    }
                    if (isset($msg['urs_explanation'])) {
                        $ragSources['urs_explanation'] = $msg['urs_explanation'];
                    }
                    if (isset($msg['claims_used']) && is_array($msg['claims_used'])) {
                        $ragSources['claims_used'] = $msg['claims_used'];
                    }
                    if (isset($msg['sources_list']) && is_array($msg['sources_list'])) {
                        $ragSources['sources_list'] = $msg['sources_list'];
                    }
                    if (isset($msg['gaps_detected']) && is_array($msg['gaps_detected'])) {
                        $ragSources['gaps_detected'] = $msg['gaps_detected'];
                    }
                    if (isset($msg['hallucinations_found']) && is_array($msg['hallucinations_found'])) {
                        $ragSources['hallucinations_found'] = $msg['hallucinations_found'];
                    }
                    if (isset($msg['command_name'])) {
                        $ragSources['command_name'] = $msg['command_name'];
                    }
                    if (isset($msg['command_result'])) {
                        $ragSources['command_result'] = $msg['command_result'];
                    }
                }

                // Risolvi tenant_id esplicitamente
                $tenantId = $user->tenant_id 
                    ?? app()->bound('currentTenantId') 
                        ? app('currentTenantId') 
                        : \App\Resolvers\TenantResolver::resolve();

                // Save message to natan_chat_messages table
                $saved = NatanChatMessage::create([
                    'tenant_id' => $tenantId,
                    'user_id' => $user->id,
                    'project_id' => $projectId,
                    'session_id' => $sessionId,
                    'role' => $msg['role'],
                    'content' => $msg['content'],
                    'persona_id' => $persona,
                    'persona_name' => $this->getPersonaName($persona),
                    'tokens_input' => $tokensInput > 0 ? $tokensInput : null,
                    'tokens_output' => $tokensOutput > 0 ? $tokensOutput : null,
                    'ai_model' => $aiModel,
                    'rag_sources' => $ragSources, // Store claims, sources, verification status
                    'created_at' => isset($msg['timestamp']) ? \Carbon\Carbon::parse($msg['timestamp']) : now(),
                ]);

                $savedMessages[] = $saved;
            }

            // Calculate totals for session
            $totalTokens = NatanChatMessage::getTotalTokensForSession($sessionId);
            $totalCostEur = NatanChatMessage::getTotalCostForSession($sessionId);

            DB::commit();

            return response()->json([
                'success' => true,
                'conversation' => [
                    'id' => $sessionId,
                    'session_id' => $sessionId,
                    'title' => $request->input('title') ?? $this->generateTitle($messagesMetadata),
                    'message_count' => count($savedMessages),
                    'total_tokens_used' => $totalTokens,
                    'total_cost_eur' => $totalCostEur,
                    'last_message_at' => $savedMessages[count($savedMessages) - 1]->created_at ?? now(),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error saving conversation: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get conversation messages
     *
     * @param string $conversationId (session_id)
     * @return JsonResponse
     */
    public function getConversation(string $conversationId): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        // Get messages from natan_chat_messages for this session
        // TenantScoped trait applica automaticamente filtro tenant_id
        // Verifica esplicita che il tenant_id dell'utente corrisponda
        $tenantId = $user->tenant_id 
            ?? app()->bound('currentTenantId') 
                ? app('currentTenantId') 
                : \App\Resolvers\TenantResolver::resolve();
        
        $messages = NatanChatMessage::where('session_id', $conversationId)
            ->where('user_id', $user->id)
            ->when($tenantId, function ($query) use ($tenantId) {
                return $query->where('tenant_id', $tenantId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        if ($messages->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found',
            ], 404);
        }

        // Convert to frontend format
        $convertedMessages = $messages->map(function ($msg) {
            $messageData = [
                'id' => (string) $msg->id,
                'role' => $msg->role,
                'content' => $msg->content,
                'timestamp' => $msg->created_at->toISOString(),
                'tokens_used' => [
                    'input' => $msg->tokens_input ?? 0,
                    'output' => $msg->tokens_output ?? 0,
                ],
                'model_used' => $msg->ai_model,
            ];

            // Restore claims, sources, and verification status from rag_sources for assistant messages
            if ($msg->role === 'assistant' && $msg->rag_sources && is_array($msg->rag_sources)) {
                $messageData['claims'] = $msg->rag_sources['claims'] ?? [];
                $messageData['sources'] = $msg->rag_sources['sources'] ?? [];
                $messageData['verification_status'] = $msg->rag_sources['verification_status'] ?? null;
                $messageData['avg_urs'] = $msg->rag_sources['avg_urs'] ?? null;
                if (!empty($msg->rag_sources['command_name'])) {
                    $messageData['command_name'] = $msg->rag_sources['command_name'];
                }
                if (!empty($msg->rag_sources['command_result'])) {
                    $messageData['command_result'] = $msg->rag_sources['command_result'];
                }
            }

            return $messageData;
        })->toArray();

        // Get first user message for title
        $firstUserMessage = $messages->where('role', 'user')->first();
        $title = $firstUserMessage ? substr($firstUserMessage->content, 0, 50) . '...' : 'Untitled Conversation';

        return response()->json([
            'success' => true,
            'conversation' => [
                'id' => $conversationId,
                'session_id' => $conversationId,
                'title' => $title,
                'persona' => $messages->first()->persona_id ?? 'strategic',
                'messages' => $convertedMessages,
                'message_count' => $messages->count(),
                'total_tokens_used' => NatanChatMessage::getTotalTokensForSession($conversationId),
                'total_cost_eur' => NatanChatMessage::getTotalCostForSession($conversationId),
                'last_message_at' => $messages->last()->created_at,
            ],
        ]);
    }

    /**
     * Generate unique conversation ID (session_id format)
     */
    private function generateConversationId(): string
    {
        return 'natan_' . uniqid('', true);
    }

    /**
     * Get persona display name
     */
    private function getPersonaName(string $personaId): ?string
    {
        $personas = [
            'strategic' => 'Strategic Consultant',
            'technical' => 'Technical Consultant',
            'legal' => 'Legal Consultant',
            'financial' => 'Financial Consultant',
            'urban_social' => 'Urban & Social Consultant',
            'communication' => 'Communication Consultant',
            'archivist' => 'Archivist Consultant',
        ];

        return $personas[$personaId] ?? ucfirst($personaId);
    }

    /**
     * Generate title from first user message
     */
    private function generateTitle(array $messages): string
    {
        foreach ($messages as $message) {
            if (isset($message['role']) && $message['role'] === 'user') {
                $content = $message['content'] ?? '';
                // Take first 50 chars as title
                return mb_substr($content, 0, 50) . (mb_strlen($content) > 50 ? '...' : '');
            }
        }
        return 'Conversazione senza titolo';
    }
}