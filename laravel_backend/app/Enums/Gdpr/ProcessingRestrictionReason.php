<?php

namespace App\Enums\Gdpr;

/**
 * @oracode Enum for processing restriction reasons
 * @oracode-dimension technical
 * @value-flow Legal basis for requesting processing restrictions
 * @transparency-level GDPR-compliant reason tracking
 */
enum ProcessingRestrictionReason: string
{
    case ACCURACY_DISPUTE = 'accuracy_dispute';
    case UNLAWFUL_PROCESSING = 'unlawful_processing';
    case NO_LONGER_NEEDED = 'no_longer_needed';
    CASE OBJECTION_PENDING = 'objection_pending';
    case LEGITIMATE_INTERESTS = 'legitimate_interests';
    case LEGAL_CLAIMS = 'legal_claims';
    case PUBLIC_INTEREST = 'public_interest';
    case OTHER = 'other';

    public function label(): string
    {
        return match($this) {
            self::ACCURACY_DISPUTE => __('gdpr.restriction.reasons.accuracy_dispute'),
            self::UNLAWFUL_PROCESSING => __('gdpr.restriction.reasons.unlawful_processing'),
            self::NO_LONGER_NEEDED => __('gdpr.restriction.reasons.no_longer_needed'),
            self::OBJECTION_PENDING => __('gdpr.restriction.reasons.objection_pending'),
            self::LEGITIMATE_INTERESTS => __('gdpr.restriction.reasons.legitimate_interests'),
            self::LEGAL_CLAIMS => __('gdpr.restriction.reasons.legal_claims'),
            self::PUBLIC_INTEREST => __('gdpr.restriction.reasons.public_interest'),
            self::OTHER => __('gdpr.restriction.reasons.other'),
        };
    }
}
