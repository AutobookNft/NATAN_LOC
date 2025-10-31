<?php

namespace App\Enums\Gdpr;

/**
 * @oracode Enum for GDPR Request Status
 * @oracode-dimension technical
 * @value-flow Tracks request lifecycle states
 * @community-impact Provides transparency on request processing
 * @transparency-level Request status is always visible to users
 */
enum GdprRequestStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
    case VERIFICATION_REQUIRED = 'verification_required';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => __('gdpr.status.pending'),
            self::IN_PROGRESS => __('gdpr.status.in_progress'),
            self::COMPLETED => __('gdpr.status.completed'),
            self::FAILED => __('gdpr.status.failed'),
            self::REJECTED => __('gdpr.status.rejected'),
            self::CANCELLED => __('gdpr.status.cancelled'),
            self::VERIFICATION_REQUIRED => __('gdpr.status.verification_required'),
        };
    }

    /**
     * Check if status is active (not final)
     */
    public function isActive(): bool
    {
        return in_array($this, [
            self::PENDING,
            self::IN_PROGRESS,
            self::VERIFICATION_REQUIRED
      ]);
    }

    /**
     * Check if status is final
     */
    public function isFinal(): bool
    {
        return in_array($this, [
            self::COMPLETED,
            self::REJECTED,
            self::CANCELLED
        ]);
    }
}
