<?php

namespace App\Enums\Gdpr;

/**
 * @package App\Enums\Gdpr
 * @author AI Partner OS2.0-Compliant for Fabio Cherici  
 * @version 1.0.0 (FlorenceEGI MVP - Privacy Level Enum)
 * @os2-pillars Explicit,Coherent,Simple,Secure
 *
 * Enum centralizzato per i livelli di privacy della piattaforma.
 * Fornisce coerenza tra ConsentService, GdprActivityCategory e user_activities table.
 */
enum PrivacyLevel: string {
/** Standard privacy level - 2 years retention */
    case STANDARD = 'standard';

/** High privacy level - 3 years retention */
    case HIGH = 'high';

/** Critical privacy level - 7 years retention (GDPR compliance) */
    case CRITICAL = 'critical';

/** Immutable records - 10 years retention (permanent audit trail) */
    case IMMUTABLE = 'immutable';

    /**
     * Get retention period in days for this privacy level
     * 
     * @return int Days of retention
     */
    public function retentionDays(): int {
        return match ($this) {
            self::STANDARD => 730,    // 2 years
            self::HIGH => 1095,       // 3 years  
            self::CRITICAL => 2555,   // 7 years (GDPR compliance)
            self::IMMUTABLE => 3650,  // 10 years (permanent records)
        };
    }

    /**
     * Get human-readable description of privacy level
     * 
     * @return string Description
     */
    public function description(): string {
        return match ($this) {
            self::STANDARD => 'Standard Privacy - General platform activities and consents',
            self::HIGH => 'High Privacy - Security and authentication related data',
            self::CRITICAL => 'Critical Privacy - GDPR sensitive operations and personal data',
            self::IMMUTABLE => 'Immutable Records - Permanent audit trail and legal compliance',
        };
    }

    /**
     * Get privacy level color for UI representation
     * 
     * @return string Hex color code
     */
    public function color(): string {
        return match ($this) {
            self::STANDARD => '#10B981',  // Green
            self::HIGH => '#F59E0B',      // Amber
            self::CRITICAL => '#EF4444',  // Red
            self::IMMUTABLE => '#6366F1', // Indigo
        };
    }

    /**
     * Check if privacy level requires GDPR audit
     * 
     * @return bool
     */
    public function requiresGdprAudit(): bool {
        return match ($this) {
            self::CRITICAL, self::IMMUTABLE => true,
            self::HIGH, self::STANDARD => false,
        };
    }

    /**
     * Get all privacy levels as array for forms/selects
     * 
     * @return array
     */
    public static function options(): array {
        return collect(self::cases())
            ->mapWithKeys(fn($level) => [$level->value => $level->description()])
            ->toArray();
    }
}
