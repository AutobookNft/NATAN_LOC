<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Gdpr;

/**
 * ConsentTypeDto - Data Transfer Object for GDPR Consent Types
 *
 * Represents a consent type configuration with all its properties
 * for UI display and validation purposes.
 */
class ConsentTypeDto
{
    public function __construct(
        public readonly string $key,
        public readonly string $category,
        public readonly string $legalBasis,
        public readonly bool $required,
        public readonly bool $defaultValue,
        public readonly bool $canWithdraw,
        public readonly array $processingPurposes = [],
    ) {}

    /**
     * Convert DTO to array for JSON serialization
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'category' => $this->category,
            'legal_basis' => $this->legalBasis,
            'required' => $this->required,
            'default_value' => $this->defaultValue,
            'can_withdraw' => $this->canWithdraw,
            'processing_purposes' => $this->processingPurposes,
        ];
    }
}
