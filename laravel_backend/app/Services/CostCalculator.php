<?php

declare(strict_types=1);

namespace App\Services;

/**
 * @package App\Services
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-01
 * @purpose Calcola costi EUR dai tokens usati per ogni modello AI
 */
class CostCalculator
{
    /**
     * Pricing per modello (EUR per 1M tokens)
     * Aggiornato a prezzi 2025
     */
    private const PRICING = [
        // Anthropic Claude
        'claude-sonnet-4-20250514' => [
            'input' => 3.0,      // $3 per 1M input tokens
            'output' => 15.0,    // $15 per 1M output tokens
        ],
        'claude-3-5-sonnet-20241022' => [
            'input' => 3.0,
            'output' => 15.0,
        ],
        'anthropic.sonnet-3.5' => [
            'input' => 3.0,
            'output' => 15.0,
        ],
        'claude-3-opus-20240229' => [
            'input' => 15.0,
            'output' => 75.0,
        ],
        // OpenAI
        'gpt-4o' => [
            'input' => 2.5,
            'output' => 10.0,
        ],
        'gpt-4-turbo' => [
            'input' => 10.0,
            'output' => 30.0,
        ],
        'gpt-3.5-turbo' => [
            'input' => 0.5,
            'output' => 1.5,
        ],
        // Ollama (locale, costo zero)
        'ollama.*' => [
            'input' => 0.0,
            'output' => 0.0,
        ],
    ];

    /**
     * Calculate cost in EUR from tokens used
     * 
     * @param array $tokensUsed Token usage dict: {input: int, output: int, total: int, prompt: int, completion: int}
     * @param string|null $model Model name (e.g., 'anthropic.sonnet-3.5', 'claude-sonnet-4-20250514')
     * @return float Cost in EUR (rounded to 4 decimals)
     */
    public static function calculateCost(array $tokensUsed, ?string $model = null): float
    {
        if (empty($tokensUsed)) {
            return 0.0;
        }

        // Extract input/output tokens (support multiple formats)
        $inputTokens = $tokensUsed['input'] ?? $tokensUsed['prompt'] ?? 0;
        $outputTokens = $tokensUsed['output'] ?? $tokensUsed['completion'] ?? 0;

        if ($inputTokens === 0 && $outputTokens === 0) {
            return 0.0;
        }

        // Get pricing for model (normalize model name)
        $pricing = self::getPricingForModel($model);

        // Calculate cost: (input_tokens / 1M) * input_price + (output_tokens / 1M) * output_price
        $inputCost = ($inputTokens / 1_000_000) * $pricing['input'];
        $outputCost = ($outputTokens / 1_000_000) * $pricing['output'];
        $totalCost = $inputCost + $outputCost;

        return round($totalCost, 4);
    }

    /**
     * Get pricing for model (with fallbacks)
     * 
     * @param string|null $model Model name
     * @return array ['input' => float, 'output' => float]
     */
    private static function getPricingForModel(?string $model): array
    {
        if (!$model) {
            // Default: Claude Sonnet 3.5 pricing
            return self::PRICING['anthropic.sonnet-3.5'];
        }

        // Check exact match first
        if (isset(self::PRICING[$model])) {
            return self::PRICING[$model];
        }

        // Check if Ollama (local, free)
        if (str_starts_with($model, 'ollama.') || str_contains($model, 'ollama')) {
            return self::PRICING['ollama.*'];
        }

        // Fallback: try to match base model name
        foreach (self::PRICING as $key => $pricing) {
            if (str_contains($model, str_replace('anthropic.', '', $key)) ||
                str_contains($key, str_replace('anthropic.', '', $model))) {
                return $pricing;
            }
        }

        // Default fallback: Claude Sonnet 3.5 pricing
        return self::PRICING['anthropic.sonnet-3.5'];
    }

    /**
     * Calculate total tokens from tokens_used dict
     * 
     * @param array $tokensUsed Token usage dict
     * @return int Total tokens
     */
    public static function calculateTotalTokens(array $tokensUsed): int
    {
        if (isset($tokensUsed['total'])) {
            return (int) $tokensUsed['total'];
        }

        $input = $tokensUsed['input'] ?? $tokensUsed['prompt'] ?? 0;
        $output = $tokensUsed['output'] ?? $tokensUsed['completion'] ?? 0;

        return (int) ($input + $output);
    }
}












