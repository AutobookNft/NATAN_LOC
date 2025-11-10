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
        private NatanChatCommandService $commandService,
        private NaturalLanguageQueryService $naturalQueryService
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

    public function natural(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => 'required|string|max:2000',
        ]);

        $text = $validated['text'];
        Log::debug('[NatanCommandController] natural query received', [
            'user_id' => Auth::id(),
            'text' => $text,
        ]);
        $user = Auth::user();

        $lowerText = mb_strtolower($text, 'UTF-8');
        $blacklist = array_map(
            static fn (string $term) => mb_strtolower($term, 'UTF-8'),
            config('natan.natural_query.blacklist', [])
        );

        foreach ($blacklist as $term) {
            if ($term !== '' && str_contains($lowerText, $term)) {
                $this->logger->warning('[NatanCommandController] Natural query blocked by blacklist', [
                    'user_id' => $user?->id,
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

        if (!$user instanceof User) {
            return response()->json([
                'success' => false,
                'code' => 'unauthenticated',
                'message' => __('natan.errors.authentication_required'),
            ], 401);
        }

        try {
            $result = $this->naturalQueryService->execute($text, $user);

            if (!$result['success']) {
                $status = 422;
                if ($result['code'] === 'throttled') {
                    $status = 429;
                } elseif ($result['code'] === 'analysis_required') {
                    $status = 200;
                }

                $responsePayload = [
                    'success' => false,
                    'code' => $result['code'],
                    'message' => $result['message'],
                    'metadata' => $result['metadata'],
                ];

                return response()->json($responsePayload, $status);
            }

            /** @var CommandResult $commandResult */
            $commandResult = $result['result'];

            return response()->json([
                'success' => true,
                'command' => $commandResult->commandName(),
                'message' => $commandResult->message(),
                'rows' => $commandResult->rows(),
                'metadata' => $commandResult->metadata(),
                'verification_status' => 'direct_query',
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


