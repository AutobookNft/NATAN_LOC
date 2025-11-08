<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ChatCommands\NatanChatCommandService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
    public function __construct(
        private UltraLogManager $logger,
        private ErrorManagerInterface $errorManager,
        private NatanChatCommandService $commandService
    ) {
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
}


