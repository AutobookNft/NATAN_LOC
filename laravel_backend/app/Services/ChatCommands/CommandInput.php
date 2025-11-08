<?php

declare(strict_types=1);

namespace App\Services\ChatCommands;

/**
 * @package App\Services\ChatCommands
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (FlorenceEGI - Direct Command Queries)
 * @date 2025-11-08
 * @purpose Value object che rappresenta un comando chat parsato
 */
final class CommandInput
{
    private string $raw;

    private string $name;

    /**
     * @var array<string, array<int, string>>
     */
    private array $arguments;

    /**
     * @param string $raw Raw command string (es. "@atto numero=pa_act_...")
     * @param string $name Normalized command name (senza prefisso @)
     * @param array<string, array<int, string>> $arguments Parsed arguments grouped by key
     */
    public function __construct(string $raw, string $name, array $arguments = [])
    {
        $this->raw = $raw;
        $this->name = $name;
        $this->arguments = $arguments;
    }

    public function getRaw(): string
    {
        return $this->raw;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Restituisce il primo valore per una chiave di argomento (helper più comune)
     */
    public function getArgument(string $key, ?string $default = null): ?string
    {
        $values = $this->arguments[$key] ?? [];

        return $values[0] ?? $default;
    }

    /**
     * Restituisce tutti i valori per una chiave (supporta parametri ripetuti)
     *
     * @return array<int, string>
     */
    public function getArgumentList(string $key): array
    {
        return $this->arguments[$key] ?? [];
    }

    /**
     * Verifica se il comando contiene una determinata chiave
     */
    public function hasArgument(string $key): bool
    {
        return array_key_exists($key, $this->arguments);
    }
}


