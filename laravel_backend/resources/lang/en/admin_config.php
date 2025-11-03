<?php

/**
 * @package Resources\Lang\En
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-03
 * @purpose Admin configuration translations (tenant-specific)
 */

return [
    'title' => 'Tenant Configurations',
    'subtitle' => 'Manage tenant-specific configurations for :tenant',
    'no_tenant' => 'No tenant associated with your account.',
    'tenant_not_found' => 'Tenant not found.',
    'saved_successfully' => 'Configurations saved successfully.',
    
    // Sections
    'ai_section' => 'AI Configurations',
    'scraping_section' => 'Web Scraping Configurations',
    'embeddings_section' => 'Embeddings Configurations',
    'notifications_section' => 'Notifications',
    
    // AI Settings
    'ai_default_model' => 'Default AI Model',
    'ai_default_model_help' => 'Default AI model to use for conversations.',
    'ai_temperature' => 'Temperature',
    'ai_temperature_help' => 'Value from 0.0 (deterministic) to 2.0 (very creative). Recommended: 0.7',
    'ai_max_tokens' => 'Max Tokens',
    'ai_max_tokens_help' => 'Maximum number of tokens in the response. Range: 1-32000',
    
    // Scraping Settings
    'scraping_enabled' => 'Enable Automatic Web Scraping',
    'scraping_interval' => 'Scraping Interval (hours)',
    'scraping_interval_help' => 'Interval in hours between automatic scrapings.',
    
    // Embeddings Settings
    'embeddings_enabled' => 'Enable Embeddings Generation',
    'embeddings_model' => 'Embeddings Model',
    'embeddings_model_help' => 'Model to use for generating document embeddings.',
    
    // Notifications Settings
    'notifications_email' => 'Email Notifications',
    'notifications_slack' => 'Slack Notifications',
    
    // Actions
    'save' => 'Save Configurations',
    'cancel' => 'Cancel',
];

