<?php

namespace App\Enums\Gdpr;

/**
 * @oracode Enum for Data Export Status
 * @oracode-dimension technical
 * @value-flow Tracks export generation lifecycle
 * @community-impact Users can monitor their export requests
 * @transparency-level Export status and progress visible
 */
enum DataExportStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case EXPIRED = 'expired';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => __('gdpr.status.pending'),
            self::PROCESSING => __('gdpr.status.processing'),
            self::COMPLETED => __('gdpr.status.completed'),
            self::FAILED => __('gdpr.status.failed'),
            self::EXPIRED => __('gdpr.status.expired'),
        };
    }

    /**
     * Check if export is downloadable
     */
    public function isDownloadable(): bool
    {
        return $this === self::COMPLETED;
    }

    /**
     * Check if export is in progress
     */
    public function isInProgress(): bool
    {
        return in_array($this, [self::PENDING, self::PROCESSING]);
    }
}
