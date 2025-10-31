<?php

namespace App\Enums\Gdpr;

/**
 * @oracode Enum for GDPR Request Types
 * @oracode-dimension technical
 * @value-flow Centralizes request type definitions for consistency
 * @community-impact Ensures clear understanding of GDPR request categories
 * @transparency-level All request types are clearly defined and documented
 */
enum GdprRequestType: string
{
    case CONSENT_UPDATE = 'consent_update';
    case DATA_EXPORT = 'data_export';
    case DATA_BREACH = 'data_breach';
    case PROCESSING_RESTRICTION = 'processing_restriction';
    case ACCOUNT_DELETION = 'account_deletion';
    case BREACH_REPORT = 'breach_report';
    case ERASURE = 'erasure';
    case ACCESS = 'access';
    case RECTIFICATION = 'rectification';
    case PORTABILITY = 'portability';
    case RESTRICTION = 'restriction';
    case OBJECTION = 'objection';

    /**
     * Get human-readable label for the request type
     */
    public function label(): string
    {
        return match($this) {
            self::CONSENT_UPDATE => __('gdpr.requests.types.consent_update'),
            self::DATA_EXPORT => __('gdpr.requests.types.data_export'),
            self::DATA_BREACH => __('gdpr.requests.types.data_breach'),
            self::PROCESSING_RESTRICTION => __('gdpr.requests.types.processing_restriction'),
            self::ACCOUNT_DELETION => __('gdpr.requests.types.account_deletion'),
            self::BREACH_REPORT => __('gdpr.requests.types.breach_report'),
            self::ERASURE => __('gdpr.requests.types.erasure'),
            self::ACCESS => __('gdpr.requests.types.access'),
            self::RECTIFICATION => __('gdpr.requests.types.rectification'),
            self::PORTABILITY => __('gdpr.requests.types.portability'),
            self::RESTRICTION => __('gdpr.requests.types.restriction'),
            self::OBJECTION => __('gdpr.requests.types.objection'),
        };
    }

    /**
     * Get color class for UI representation
     */
    public function color(): string
    {
        return match($this) {
            self::ERASURE => 'red',
            self::ACCESS => 'blue',
            self::RECTIFICATION => 'yellow',
            self::PORTABILITY => 'green',
            self::RESTRICTION => 'orange',
            self::OBJECTION => 'purple',
        };
    }
}
