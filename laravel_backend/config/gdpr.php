<?php

declare(strict_types=1);

/**
 * GDPR Configuration
 * 
 * @package App\Config
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC - GDPR Compliance Configuration)
 * @date 2025-11-05
 * @purpose Configurazione GDPR per audit trail e compliance PA
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Activity Categories Configuration
    |--------------------------------------------------------------------------
    |
    | Definisce retention period e privacy level per ogni categoria di attività.
    | Allineato con GdprActivityCategory enum e PrivacyLevel enum.
    |
    */
    'activity_categories' => [
        // CRITICAL - GDPR sensitive operations (7 years retention = 2555 days)
        'gdpr_actions' => [
            'name' => 'GDPR Compliance and Privacy Actions',
            'retention_period' => 2555, // 7 years
            'privacy_level' => 'critical',
        ],
        'data_deletion' => [
            'name' => 'Data Deletion and Erasure Operations',
            'retention_period' => 2555,
            'privacy_level' => 'critical',
        ],
        'privacy_management' => [
            'name' => 'Privacy Settings and Consent Management',
            'retention_period' => 2555,
            'privacy_level' => 'critical',
        ],
        'personal_data_update' => [
            'name' => 'Personal Data Updates and Modifications',
            'retention_period' => 2555,
            'privacy_level' => 'critical',
        ],
        'wallet_management' => [
            'name' => 'Wallet and Financial Operations Management',
            'retention_period' => 2555,
            'privacy_level' => 'critical',
        ],
        'wallet_created' => [
            'name' => 'Wallet Creation During Registration',
            'retention_period' => 2555,
            'privacy_level' => 'critical',
        ],
        'wallet_secret_accessed' => [
            'name' => 'Wallet Secret Accessed (Mnemonic Export)',
            'retention_period' => 2555,
            'privacy_level' => 'critical',
        ],
        'ai_credits_usage' => [
            'name' => 'AI Credits Usage and Financial Operations',
            'retention_period' => 2555,
            'privacy_level' => 'critical',
        ],

        // HIGH - Security and authentication (3 years retention = 1095 days)
        'authentication' => [
            'name' => 'User Authentication (Login/Logout)',
            'retention_period' => 1095,
            'privacy_level' => 'high',
        ],
        'authentication_login' => [
            'name' => 'User Login Activities',
            'retention_period' => 1095,
            'privacy_level' => 'high',
        ],
        'authentication_logout' => [
            'name' => 'User Logout Activities',
            'retention_period' => 1095,
            'privacy_level' => 'high',
        ],
        'security_events' => [
            'name' => 'Security-related Events or Incidents',
            'retention_period' => 1095,
            'privacy_level' => 'high',
        ],
        'registration' => [
            'name' => 'User Registration Activities',
            'retention_period' => 1095,
            'privacy_level' => 'high',
        ],
        'admin_access' => [
            'name' => 'Administrative Panel Access (Superadmin/Admin)',
            'retention_period' => 1095,
            'privacy_level' => 'high',
        ],
        'admin_action' => [
            'name' => 'Administrative Actions and Configuration Changes',
            'retention_period' => 1095,
            'privacy_level' => 'high',
        ],

        // STANDARD - General activities (2 years retention = 730 days)
        'content_creation' => [
            'name' => 'Content Creation (Biographies, Posts, Articles)',
            'retention_period' => 730,
            'privacy_level' => 'standard',
        ],
        'content_modification' => [
            'name' => 'Content Modification and Updates',
            'retention_period' => 730,
            'privacy_level' => 'standard',
        ],
        'data_access' => [
            'name' => 'Data Access, Viewing or Downloading',
            'retention_period' => 730,
            'privacy_level' => 'standard',
        ],
        'blockchain_activity' => [
            'name' => 'Blockchain or NFT Related Activity',
            'retention_period' => 730,
            'privacy_level' => 'standard',
        ],
        'media_management' => [
            'name' => 'File Upload, Media and Asset Management',
            'retention_period' => 730,
            'privacy_level' => 'standard',
        ],
        'platform_usage' => [
            'name' => 'General Platform Usage/Interaction',
            'retention_period' => 730,
            'privacy_level' => 'standard',
        ],
        'system_interaction' => [
            'name' => 'System Interactions and UI Operations',
            'retention_period' => 730,
            'privacy_level' => 'standard',
        ],
        'notification_management' => [
            'name' => 'Notification Management and User Interactions',
            'retention_period' => 730,
            'privacy_level' => 'standard',
        ],
        'ai_processing' => [
            'name' => 'AI Processing and Analysis Activities',
            'retention_period' => 730,
            'privacy_level' => 'standard',
        ],
        'egi_trait_management' => [
            'name' => 'EGI Trait Management (Creation, Modification)',
            'retention_period' => 730,
            'privacy_level' => 'standard',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Retention Period
    |--------------------------------------------------------------------------
    |
    | Retention period di default se categoria non trovata (7 years per compliance GDPR)
    |
    */
    'default_retention_days' => 2555, // 7 years

    /*
    |--------------------------------------------------------------------------
    | Default Privacy Level
    |--------------------------------------------------------------------------
    |
    | Privacy level di default se categoria non trovata
    |
    */
    'default_privacy_level' => 'standard',
];

