<?php

declare(strict_types=1);

namespace App\Services\ChatCommands;

use App\Models\Tenant;
use App\Models\User;
use App\Services\ChatCommands\Contracts\CommandHandlerInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * @package App\Services\ChatCommands
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (FlorenceEGI - Direct Command Queries)
 * @date 2025-11-08
 * @purpose Servizio principale per interpretare ed eseguire comandi chat
 */
final class NatanChatCommandService
{
    public function __construct(
        private CommandParser $parser,
        private CommandRegistry $registry
    ) {
    }

    /**
     * @throws RuntimeException Quando il comando non è valido o non supportato
     */
    public function execute(string $rawCommand, ?User $user = null, ?Tenant $tenant = null): CommandResult
    {
        $parsed = $this->parser->parse($rawCommand);

        if (!$parsed) {
            throw new RuntimeException('Invalid command syntax.');
        }

        $authenticatedUser = $user ?? $this->getAuthenticatedUser();

        $tenant ??= $authenticatedUser->tenant;

        $context = new CommandContext(
            $authenticatedUser,
            $tenant,
            $parsed
        );

        $handler = $this->resolveHandler($parsed);

        return $handler->handle($context);
    }

    private function getAuthenticatedUser(): User
    {
        $user = Auth::user();

        if (!$user instanceof User) {
            throw new RuntimeException('Authentication required.');
        }

        return $user;
    }

    private function resolveHandler(CommandInput $input): CommandHandlerInterface
    {
        try {
            return $this->registry->resolve($input);
        } catch (RuntimeException $exception) {
            Log::warning('[NatanChatCommandService] Command not supported', [
                'command' => $input->getName(),
                'raw' => $input->getRaw(),
            ]);

            throw $exception;
        }
    }
}


