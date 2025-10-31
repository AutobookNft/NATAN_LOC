<?php

namespace App\Enums\Gdpr;

/**
 * @oracode Enum for GDPR Notification Status
 * @oracode-dimension technical
 * @value-flow Tracks user notifications related to GDPR actions
 * @community-impact Provides clarity on user actions and consent
 * @transparency-level Notification status is always visible to users
 *
 * This enum represents the status of GDPR-related notifications
 * that are sent to users. It includes states for pending user confirmation,
 * user-confirmed actions, user-revoked consent, and user-disavowed suspicious
 * activities. Each state has a specific meaning and can be used to determine
 * whether user action is required or if the notification is related to a security event.
 */
enum GdprNotificationStatus: string {
    case PENDING_USER_CONFIRMATION = 'pending_user_confirmation';
    case USER_CONFIRMED_ACTION = 'user_confirmed_action';
    case USER_REVOKED_CONSENT = 'user_revoked_consent';
    case USER_DISAVOWED_SUSPICIOUS = 'user_disavowed_suspicious';

    // Human-readable labels for each status
    public function requiresUserAction(): bool {
        return str_contains($this->value, 'pending');
    }

    public function isUserRevokedConsent(): bool {
        return $this === self::USER_REVOKED_CONSENT;
    }

    public function isSecurityEvent(): bool {
        return str_contains($this->value, 'disavowed');
    }

    /**
     * Get human-readable label for the status
     */
    public function label(): string {
        return match($this) {
            self::PENDING_USER_CONFIRMATION => __('gdpr.notifications.status.pending_user_confirmation'),
            self::USER_CONFIRMED_ACTION => __('gdpr.notifications.status.user_confirmed_action'),
            self::USER_REVOKED_CONSENT => __('gdpr.notifications.status.user_revoked_consent'),
            self::USER_DISAVOWED_SUSPICIOUS => __('gdpr.notifications.status.user_disavowed_suspicious'),
        };
    }
}
