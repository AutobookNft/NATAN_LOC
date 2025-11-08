<?php

declare(strict_types=1);

namespace App\Services\ChatCommands\Contracts;

use App\Services\ChatCommands\CommandContext;
use App\Services\ChatCommands\CommandInput;
use App\Services\ChatCommands\CommandResult;

/**
 * @package App\Services\ChatCommands\Contracts
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (FlorenceEGI - Direct Command Queries)
 * @date 2025-11-08
 * @purpose Interfaccia per gli handler dei comandi chat
 */
interface CommandHandlerInterface
{
    public function supports(CommandInput $input): bool;

    public function handle(CommandContext $context): CommandResult;
}


