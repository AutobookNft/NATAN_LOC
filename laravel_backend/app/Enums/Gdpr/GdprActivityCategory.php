<?php

namespace App\Enums\Gdpr;

use Illuminate\Support\Traits\EnumTrait;
use App\Enums\Gdpr\PrivacyLevel;

/**
 * @package App\Enums\Gdpr
 * @author Padmin D. Curtis (AI Partner OS2.0-Compliant) for Fabio Cherici
 * @version 1.1.0 (FlorenceEGI MVP - Activity Category Enum + Biography Support)
 * @os2-pillars Explicit,Coherent,Simple,Secure
 *
 * Enum OS2.0-compliant per la categorizzazione delle attività della piattaforma.
 * Ogni categoria rappresenta un ambito semantico distinto, utile per audit, security e tracciabilità.
 * v1.1: Aggiunto supporto completo per sistema Biography e gestione contenuti.
 */
enum GdprActivityCategory: string {
/** Login/logout activities */
    case AUTHENTICATION = 'authentication';

/** Authentication-related activities */
    case AUTHENTICATION_LOGIN = 'authentication_login';

/** Logout activities */
    case AUTHENTICATION_LOGOUT = 'authentication_logout';

/** User Registration Activities */
    case REGISTRATION = 'registration';

/** GDPR-related actions */
    case GDPR_ACTIONS = 'gdpr_actions';

/** Data viewing/downloading */
    case DATA_ACCESS = 'data_access';

/** Data deletion and erasure */
    case DATA_DELETION = 'data_deletion';

/** Content creation activities (biographies, posts, etc.) */
    case CONTENT_CREATION = 'content_creation';

/** Content modification and updates */
    case CONTENT_MODIFICATION = 'content_modification';

/** General platform interaction */
    case PLATFORM_USAGE = 'platform_usage';

/** System interactions and UI operations */
    case SYSTEM_INTERACTION = 'system_interaction';

/** Security-related activities */
    case SECURITY_EVENTS = 'security_events';

/** Administrative access to back-office (superadmin/admin) */
    case ADMIN_ACCESS = 'admin_access';

/** Administrative actions and changes in back-office */
    case ADMIN_ACTION = 'admin_action';

/** Blockchain/NFT activities */
    case BLOCKCHAIN_ACTIVITY = 'blockchain_activity';

/** File and media operations */
    case MEDIA_MANAGEMENT = 'media_management';

/** Privacy and consent operations */
    case PRIVACY_MANAGEMENT = 'privacy_management';

/** Personal data updates */
    case PERSONAL_DATA_UPDATE = 'personal_data_update';

/** Wallet and financial operations */
    case WALLET_MANAGEMENT = 'wallet_management';

/** Wallet creation (registration) */
    case WALLET_CREATED = 'wallet_created';

/** Wallet secret accessed (mnemonic export) */
    case WALLET_SECRET_ACCESSED = 'wallet_secret_accessed';

/** Notification management and interactions */
    case NOTIFICATION_MANAGEMENT = 'notification_management';

/** AI processing and analysis activities */
    case AI_PROCESSING = 'ai_processing';

/** AI Credits usage and financial tracking */
    case AI_CREDITS_USAGE = 'ai_credits_usage';

/** EGI trait management (creation, modification) */
    case EGI_TRAIT_MANAGEMENT = 'egi_trait_management';

    /**
     * Ritorna la descrizione umana della categoria, OS2.0 style.
     */
    public function label(): string {
        return match ($this) {
            self::AUTHENTICATION => 'User Authentication (Login/Logout)',
            self::AUTHENTICATION_LOGIN => 'User Login Activities',
            self::AUTHENTICATION_LOGOUT => 'User Logout Activities',
            self::REGISTRATION => 'User Registration Activities',
            self::GDPR_ACTIONS => 'GDPR Compliance and Privacy Actions',
            self::DATA_ACCESS => 'Data Access, Viewing or Downloading',
            self::DATA_DELETION => 'Data Deletion and Erasure Operations',
            self::CONTENT_CREATION => 'Content Creation (Biographies, Posts, Articles)',
            self::CONTENT_MODIFICATION => 'Content Modification and Updates',
            self::PLATFORM_USAGE => 'General Platform Usage/Interaction',
            self::SYSTEM_INTERACTION => 'System Interactions and UI Operations',
            self::SECURITY_EVENTS => 'Security-related Events or Incidents',
            self::ADMIN_ACCESS => 'Administrative Panel Access (Superadmin/Admin)',
            self::ADMIN_ACTION => 'Administrative Actions and Configuration Changes',
            self::BLOCKCHAIN_ACTIVITY => 'Blockchain or NFT Related Activity',
            self::MEDIA_MANAGEMENT => 'File Upload, Media and Asset Management',
            self::PRIVACY_MANAGEMENT => 'Privacy Settings and Consent Management',
            self::PERSONAL_DATA_UPDATE => 'Personal Data Updates and Modifications',
            self::WALLET_MANAGEMENT => 'Wallet and Financial Operations Management',
            self::WALLET_CREATED => 'Wallet Creation During Registration',
            self::WALLET_SECRET_ACCESSED => 'Wallet Secret Accessed (Mnemonic Export)',
            self::NOTIFICATION_MANAGEMENT => 'Notification Management and User Interactions',
            self::AI_PROCESSING => 'AI Processing and Analysis Activities',
            self::AI_CREDITS_USAGE => 'AI Credits Usage and Financial Operations',
            self::EGI_TRAIT_MANAGEMENT => 'EGI Trait Management (Creation, Modification)',
        };
    }

    /**
     * @Oracode Method: Get Privacy Level for Category
     * 🎯 Purpose: Return appropriate privacy level for audit retention
     * 📊 Logic: Higher sensitivity = longer retention + higher security
     */
    public function privacyLevel(): PrivacyLevel {
        return match ($this) {
            // CRITICAL - GDPR sensitive operations (7 years retention)
            self::GDPR_ACTIONS,
            self::DATA_DELETION,
            self::PRIVACY_MANAGEMENT,
            self::PERSONAL_DATA_UPDATE,
            self::WALLET_MANAGEMENT,
            self::WALLET_CREATED,
            self::WALLET_SECRET_ACCESSED,
            self::AI_CREDITS_USAGE => PrivacyLevel::CRITICAL, // Financial data tracking

            // HIGH - Security and authentication (3 years retention)
            self::AUTHENTICATION,
            self::AUTHENTICATION_LOGIN,
            self::AUTHENTICATION_LOGOUT,
            self::SECURITY_EVENTS,
            self::REGISTRATION,
            self::ADMIN_ACCESS,
            self::ADMIN_ACTION => PrivacyLevel::HIGH,

            // STANDARD - General activities (2 years retention)
            self::CONTENT_CREATION,
            self::CONTENT_MODIFICATION,
            self::DATA_ACCESS,
            self::BLOCKCHAIN_ACTIVITY,
            self::MEDIA_MANAGEMENT,
            self::PLATFORM_USAGE,
            self::SYSTEM_INTERACTION,
            self::NOTIFICATION_MANAGEMENT,
            self::AI_PROCESSING,
            self::EGI_TRAIT_MANAGEMENT => PrivacyLevel::STANDARD,
        };
    }

    /**
     * @Oracode Method: Get Retention Period in Days
     * 🎯 Purpose: Return retention period based on category sensitivity
     * 📊 Logic: Uses PrivacyLevel enum for consistent retention policies
     */
    public function retentionDays(): int {
        return $this->privacyLevel()->retentionDays();
    }

    /**
     * @Oracode Method: Check if Category Requires GDPR Audit
     * 🎯 Purpose: Determine if activity needs full GDPR audit logging
     * 🛡️ GDPR: Uses PrivacyLevel enum to determine audit requirements
     */
    public function requiresGdprAudit(): bool {
        $privacyLevel = $this->privacyLevel();

        // Use enum method for base audit requirement
        if ($privacyLevel->requiresGdprAudit()) {
            return true;
        }

        // Additional logic for specific standard activities
        return match ($privacyLevel) {
            PrivacyLevel::STANDARD => match ($this) {
                // Only these standard operations need GDPR audit
                self::CONTENT_CREATION,
                self::CONTENT_MODIFICATION,
                self::MEDIA_MANAGEMENT => true,
                default => false
            },
            default => false
        };
    }
}
