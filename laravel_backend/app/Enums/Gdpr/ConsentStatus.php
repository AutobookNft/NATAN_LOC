<?php

namespace App\Enums\Gdpr;

/**
 * @oracode Enum for User Consent Status
 * @oracode-dimension technical
 * @value-flow Manages consent lifecycle states
 * @community-impact Clear consent status for user control
 * @transparency-level Consent status always visible and auditable
 */
enum ConsentStatus: string
{
    case ACTIVE = 'active';
    case WITHDRAWN = 'withdrawn';
    case EXPIRED = 'expired';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::ACTIVE => __('gdpr.status.active'),
            self::WITHDRAWN => __('gdpr.status.withdrawn'),
            self::EXPIRED => __('gdpr.status.expired'),
        };
    }

    /**
     * Get badge color for UI
     */
    public function badgeColor(): string
    {
        return match($this) {
            self::ACTIVE => 'green',
            self::WITHDRAWN => 'red',
            self::EXPIRED => 'gray',
        };
    }
}
