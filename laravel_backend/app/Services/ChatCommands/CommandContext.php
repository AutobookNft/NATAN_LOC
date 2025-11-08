<?php

declare(strict_types=1);

namespace App\Services\ChatCommands;

use App\Models\Tenant;
use App\Models\User;

/**
 * @package App\Services\ChatCommands
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (FlorenceEGI - Direct Command Queries)
 * @date 2025-11-08
 * @purpose Contesto esecuzione comando (utente, tenant, input)
 */
final class CommandContext
{
    public function __construct(
        private User $user,
        private ?Tenant $tenant,
        private CommandInput $input
    ) {
    }

    public function user(): User
    {
        return $this->user;
    }

    public function tenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function input(): CommandInput
    {
        return $this->input;
    }
}


