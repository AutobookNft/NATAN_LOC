<?php

declare(strict_types=1);

namespace App\Services\ChatCommands;

/**
 * @package App\Services\ChatCommands
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (FlorenceEGI - Direct Command Queries)
 * @date 2025-11-08
 * @purpose DTO con il risultato formattato da mostrare in chat
 */
final class CommandResult
{
    /**
     * @param string $commandName Nome comando eseguito
     * @param string $message Testo primario (Markdown) da mostrare nella chat
     * @param array<int, array<string, mixed>> $rows Risultati strutturati da mostrare come elenco/carte
     * @param array<string, mixed> $metadata Metadati aggiuntivi (count, limit, ecc.)
     */
    public function __construct(
        private string $commandName,
        private string $message,
        private array $rows = [],
        private array $metadata = []
    ) {
    }

    public function commandName(): string
    {
        return $this->commandName;
    }

    public function message(): string
    {
        return $this->message;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function rows(): array
    {
        return $this->rows;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }
}


