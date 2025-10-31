<?php

namespace App\Enums\Gdpr;

/**
 * @oracode Enum for processing restriction types
 * @oracode-dimension technical
 * @value-flow Categorizes different types of processing limitations
 * @transparency-level Restriction types are clearly defined
 */
enum ProcessingRestrictionType: string
{
    case MARKETING = 'marketing';
    case PROFILING = 'profiling';
    case ANALYTICS = 'analytics';
    case THIRD_PARTY = 'third_party';
    case AUTOMATED_DECISIONS = 'automated_decisions';
    case DATA_SHARING = 'data_sharing';
    case REMOVED = 'removed';
    case ALL = 'all';

    public function label(): string
    {
        return match($this) {
            self::MARKETING => __('gdpr.restriction.types.marketing'),
            self::PROFILING => __('gdpr.restriction.types.profiling'),
            self::ANALYTICS => __('gdpr.restriction.types.analytics'),
            self::THIRD_PARTY => __('gdpr.restriction.types.third_party'),
            self::AUTOMATED_DECISIONS => __('gdpr.restriction.types.automated_decisions'),
            self::DATA_SHARING => __('gdpr.restriction.types.data_sharing'),
            self::REMOVED => __('gdpr.restriction.types.removed'),
            self::ALL => __('gdpr.restriction.types.all'),
        };
    }

    public function description(): string
    {
        return match($this) {
            self::MARKETING => __('gdpr.restriction.descriptions.marketing'),
            self::PROFILING => __('gdpr.restriction.descriptions.profiling'),
            self::ANALYTICS => __('gdpr.restriction.descriptions.analytics'),
            self::THIRD_PARTY => __('gdpr.restriction.descriptions.third_party'),
            self::AUTOMATED_DECISIONS => __('gdpr.restriction.descriptions.automated_decisions'),
            self::DATA_SHARING => __('gdpr.restriction.descriptions.data_sharing'),
            self::REMOVED => __('gdpr.restriction.descriptions.removed'),
            self::ALL => __('gdpr.restriction.descriptions.all'),
        };
    }
}
