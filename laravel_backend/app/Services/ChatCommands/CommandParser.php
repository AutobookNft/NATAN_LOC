<?php

declare(strict_types=1);

namespace App\Services\ChatCommands;

use Illuminate\Support\Str;

/**
 * @package App\Services\ChatCommands
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (FlorenceEGI - Direct Command Queries)
 * @date 2025-11-08
 * @purpose Parser responsabile di interpretare stringhe comando in CommandInput
 */
final class CommandParser
{
    /**
     * Esegue il parsing di una stringa comando
     */
    public function parse(string $rawCommand): ?CommandInput
    {
        $trimmed = ltrim(trim($rawCommand));

        if ($trimmed === '' || !Str::startsWith($trimmed, '@')) {
            return null;
        }

        // Rimuovi prefisso @ e normalizza spazi
        $withoutPrefix = ltrim(mb_substr($trimmed, 1));

        if ($withoutPrefix === '') {
            return null;
        }

        // Estrai nome comando (prima parola)
        $parts = preg_split('/\s+/', $withoutPrefix, 2);
        $name = Str::lower($parts[0] ?? '');

        if ($name === '') {
            return null;
        }

        $argumentsString = $parts[1] ?? '';
        $arguments = $argumentsString !== '' ? $this->parseArguments($argumentsString) : [];

        return new CommandInput($trimmed, $name, $arguments);
    }

    /**
     * Parse degli argomenti con supporto a valori quotati e ripetuti
     *
     * @return array<string, array<int, string>>
     */
    private function parseArguments(string $argumentsString): array
    {
        $arguments = [];

        $pattern = '/(?P<key>[a-zA-Z0-9_\-]+)\s*=\s*(?P<value>"[^"]*"|\'[^\']*\'|\S+)/u';

        if (preg_match_all($pattern, $argumentsString, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $key = Str::lower($match['key']);
                $value = $this->cleanValue($match['value']);

                if ($value === '') {
                    continue;
                }

                if (!array_key_exists($key, $arguments)) {
                    $arguments[$key] = [];
                }

                $arguments[$key][] = $value;
            }
        }

        return $arguments;
    }

    private function cleanValue(string $value): string
    {
        $value = trim($value);

        if ((Str::startsWith($value, '"') && Str::endsWith($value, '"'))
            || (Str::startsWith($value, "'") && Str::endsWith($value, "'"))
        ) {
            $value = Str::substr($value, 1, Str::length($value) - 2);
        }

        return trim($value);
    }
}


