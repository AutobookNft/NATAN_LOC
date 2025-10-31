<?php

namespace App\Enums\Gdpr;

/**
 * @Oracode Enum: Cookie Consent Categories
 * 🎯 Purpose: Defines GDPR-compliant cookie categories for consent management
 * 🛡️ Privacy: Maps cookie categories to consent types with legal basis
 * 🧱 Core Logic: Centralizes cookie category definitions for consistent usage
 *
 * @package App\Enums\Gdpr
 * @author Padmin D. Curtis (AI Partner) for Fabio Cherici
 * @version 1.0.0 (FlorenceEGI - Cookie Consent Categories)
 * @date 2025-09-17
 * @accessibility-trait Provides clear category definitions for user consent UI
 */
enum CookieConsentCategory: string {
    case ESSENTIAL = 'essential';
    case FUNCTIONAL = 'functional';
    case ANALYTICS = 'analytics';
    case MARKETING = 'marketing';
    case PROFILING = 'profiling';

    /**
     * Get human-readable label for the category
     *
     * @return string
     */
    public function label(): string {
        return match ($this) {
            self::ESSENTIAL => __('cookie.categories.essential.label'),
            self::FUNCTIONAL => __('cookie.categories.functional.label'),
            self::ANALYTICS => __('cookie.categories.analytics.label'),
            self::MARKETING => __('cookie.categories.marketing.label'),
            self::PROFILING => __('cookie.categories.profiling.label'),
        };
    }

    /**
     * Get detailed description for the category
     *
     * @return string
     */
    public function description(): string {
        return match ($this) {
            self::ESSENTIAL => __('cookie.categories.essential.description'),
            self::FUNCTIONAL => __('cookie.categories.functional.description'),
            self::ANALYTICS => __('cookie.categories.analytics.description'),
            self::MARKETING => __('cookie.categories.marketing.description'),
            self::PROFILING => __('cookie.categories.profiling.description'),
        };
    }

    /**
     * Check if the category is required (cannot be opted out)
     *
     * @return bool
     */
    public function isRequired(): bool {
        return match ($this) {
            self::ESSENTIAL => true,
            self::FUNCTIONAL => false,
            self::ANALYTICS => false,
            self::MARKETING => false,
            self::PROFILING => false,
        };
    }

    /**
     * Check if user can withdraw consent for this category
     *
     * @return bool
     */
    public function canWithdraw(): bool {
        return match ($this) {
            self::ESSENTIAL => false, // Essential cookies cannot be withdrawn
            self::FUNCTIONAL => true,
            self::ANALYTICS => true,
            self::MARKETING => true,
            self::PROFILING => true,
        };
    }

    /**
     * Get default consent value for new visitors
     *
     * @return bool
     */
    public function defaultValue(): bool {
        return match ($this) {
            self::ESSENTIAL => true,  // Always enabled by default
            self::FUNCTIONAL => false,
            self::ANALYTICS => false,
            self::MARKETING => false,
            self::PROFILING => false,
        };
    }

    /**
     * Get GDPR legal basis for this category
     *
     * @return string
     */
    public function legalBasis(): string {
        return match ($this) {
            self::ESSENTIAL => 'contract', // Necessary for service provision
            self::FUNCTIONAL => 'consent',
            self::ANALYTICS => 'consent',
            self::MARKETING => 'consent',
            self::PROFILING => 'consent',
        };
    }

    /**
     * Get UI icon identifier for this category
     *
     * @return string
     */
    public function icon(): string {
        return match ($this) {
            self::ESSENTIAL => 'shield-check',
            self::FUNCTIONAL => 'cog-6-tooth',
            self::ANALYTICS => 'chart-bar',
            self::MARKETING => 'megaphone',
            self::PROFILING => 'user-circle',
        };
    }

    /**
     * Get color for UI representation (using FlorenceEGI brand colors)
     *
     * @return string
     */
    public function color(): string {
        return match ($this) {
            self::ESSENTIAL => '#2D5016', // Verde Rinascita (required)
            self::FUNCTIONAL => '#D4A574', // Oro Fiorentino (functional)
            self::ANALYTICS => '#1B365D', // Blu Algoritmo (analytics)
            self::MARKETING => '#E67E22', // Arancio Energia (marketing)
            self::PROFILING => '#6B6B6B', // Grigio Pietra (profiling)
        };
    }

    /**
     * Get array of consent type slugs that belong to this category
     *
     * @return array
     */
    public function consentTypeSlugs(): array {
        return match ($this) {
            self::ESSENTIAL => [
                'platform-services',
                'terms-of-service',
                'privacy-policy',
                'age-confirmation',
                'allow-personal-data-processing'
            ],
            self::FUNCTIONAL => [
                'personalization',
                'collaboration_participation'
            ],
            self::ANALYTICS => [
                'analytics'
            ],
            self::MARKETING => [
                'marketing'
            ],
            self::PROFILING => [
                // Future profiling consent types can be added here
            ],
        };
    }

    /**
     * Get all cookie categories as array for UI
     *
     * @return array
     */
    public static function toArray(): array {
        $categories = [];

        foreach (self::cases() as $category) {
            $categories[$category->value] = [
                'label' => $category->label(),
                'description' => $category->description(),
                'required' => $category->isRequired(),
                'can_withdraw' => $category->canWithdraw(),
                'default' => $category->defaultValue(),
                'legal_basis' => $category->legalBasis(),
                'icon' => $category->icon(),
                'color' => $category->color(),
                'consent_types' => $category->consentTypeSlugs()
            ];
        }

        return $categories;
    }

    /**
     * Map consent type slug to cookie category
     *
     * @param string $slug
     * @return CookieConsentCategory|null
     */
    public static function fromConsentTypeSlug(string $slug): ?self {
        foreach (self::cases() as $category) {
            if (in_array($slug, $category->consentTypeSlugs())) {
                return $category;
            }
        }

        return null;
    }

    /**
     * Get categories that require explicit consent
     *
     * @return array<CookieConsentCategory>
     */
    public static function optionalCategories(): array {
        return array_filter(self::cases(), fn($category) => !$category->isRequired());
    }

    /**
     * Get required categories that cannot be opted out
     *
     * @return array<CookieConsentCategory>
     */
    public static function requiredCategories(): array {
        return array_filter(self::cases(), fn($category) => $category->isRequired());
    }
}