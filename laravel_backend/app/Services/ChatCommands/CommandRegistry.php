<?php

declare(strict_types=1);

namespace App\Services\ChatCommands;

use App\Services\ChatCommands\Contracts\CommandHandlerInterface;
use RuntimeException;

/**
 * @package App\Services\ChatCommands
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (FlorenceEGI - Direct Command Queries)
 * @date 2025-11-08
 * @purpose Registro che individua l'handler corretto per un comando
 */
final class CommandRegistry
{
    /**
     * @var array<int, CommandHandlerInterface>
     */
    private array $handlers;

    /**
     * @param array<int, CommandHandlerInterface> $handlers
     */
    public function __construct(array $handlers = [])
    {
        $this->handlers = $handlers;
    }

    public function register(CommandHandlerInterface $handler): void
    {
        $this->handlers[] = $handler;
    }

    public function resolve(CommandInput $input): CommandHandlerInterface
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($input)) {
                return $handler;
            }
        }

        throw new RuntimeException(sprintf('No handler registered for command [%s]', $input->getName()));
    }
}


