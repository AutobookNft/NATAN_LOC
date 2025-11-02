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
            'messages' => 'required|array',
            'messages.*.id' => 'required|string',
            'messages.*.role' => 'required|in:user,assistant',
            'messages.*.content' => 'required|string',
            'messages.*.timestamp' => 'required|date',
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

                // Skip if message already exists
                if ($existingMessage) {
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

                // Risolvi tenant_id esplicitamente
                $tenantId = $user->tenant_id 
                    ?? app()->bound('currentTenantId') 
                        ? app('currentTenantId') 
                        : \App\Resolvers\TenantResolver::resolve();

                // Save message to natan_chat_messages table
                $saved = NatanChatMessage::create([
                    'tenant_id' => $tenantId,
                    'user_id' => $user->id,
                    'session_id' => $sessionId,
                    'role' => $msg['role'],
                    'content' => $msg['content'],
                    'persona_id' => $persona,
                    'persona_name' => $this->getPersonaName($persona),
                    'tokens_input' => $tokensInput > 0 ? $tokensInput : null,
                    'tokens_output' => $tokensOutput > 0 ? $tokensOutput : null,
                    'ai_model' => $aiModel,
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
            return [
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