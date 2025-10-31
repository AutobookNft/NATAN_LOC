<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Ultra Error Manager Configuration
    |--------------------------------------------------------------------------
    |
    | Defines error types, handlers, default behaviors, and specific error codes.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Log Handler Configuration
    |--------------------------------------------------------------------------
    */
    'log_handler' => [
        // Puoi sovrascrivere il percorso del file di log dedicato di UEM.
        // Se non specificato, il default sarà 'storage/logs/error_manager.log'.
        // 'path' => storage_path('logs/uem_errors.log'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Handlers
    |--------------------------------------------------------------------------
    | Handlers automatically registered. Order can matter for some logic.
    | Assumes handlers have been refactored for DI.
    */
    'default_handlers' => [
        // Order Suggestion: Log first, then notify, then prepare UI/recovery
        \Ultra\ErrorManager\Handlers\LogHandler::class,
        \Ultra\ErrorManager\Handlers\DatabaseLogHandler::class, // Log to DB
        \Ultra\ErrorManager\Handlers\EmailNotificationHandler::class, // Notify Devs
        \Ultra\ErrorManager\Handlers\SlackNotificationHandler::class, // Notify Slack
        \Ultra\ErrorManager\Handlers\UserInterfaceHandler::class, // Prepare UI flash messages
        \Ultra\ErrorManager\Handlers\RecoveryActionHandler::class, // Attempt recovery
        // Simulation handler (conditionally added by Service Provider if not production)
        // \Ultra\ErrorManager\Handlers\ErrorSimulationHandler::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Notification Settings
    |--------------------------------------------------------------------------
    */
    'email_notification' => [
        'enabled' => env('ERROR_EMAIL_NOTIFICATIONS_ENABLED', true),
        'to' => env('ERROR_EMAIL_RECIPIENT', 'devteam@example.com'),
        'from' => [ /* ... */],
        'subject_prefix' => env('ERROR_EMAIL_SUBJECT_PREFIX', '[UEM Error] '),

        // --- NUOVE OPZIONI GDPR ---
        'include_ip_address' => env('ERROR_EMAIL_INCLUDE_IP', false),        // Default: NO
        'include_user_agent' => env('ERROR_EMAIL_INCLUDE_UA', false),       // Default: NO
        'include_user_details' => env('ERROR_EMAIL_INCLUDE_USER', false),    // Default: NO (Include ID, Name, Email)
        'include_context' => env('ERROR_EMAIL_INCLUDE_CONTEXT', true),       // Default: YES (ma verrà sanitizzato)
        'include_trace' => env('ERROR_EMAIL_INCLUDE_TRACE', false),         // Default: NO (Le tracce possono essere lunghe/sensibili)
        'context_sensitive_keys' => [ // Lista specifica per email, può differire da DB
            'password',
            'secret',
            'token',
            'auth',
            'key',
            'credentials',
            'authorization',
            'php_auth_user',
            'php_auth_pw',
            'credit_card',
            'creditcard',
            'card_number',
            'cvv',
            'cvc',
            'api_key',
            'secret_key',
            'access_token',
            'refresh_token',
            // Aggiungere chiavi specifiche se necessario
        ],
        'trace_max_lines' => env('ERROR_EMAIL_TRACE_LINES', 30), // Limita lunghezza trace inviata
    ],

    /*
    |--------------------------------------------------------------------------
    | Slack Notification Settings
    |--------------------------------------------------------------------------
    */
    'slack_notification' => [
        'enabled' => env('ERROR_SLACK_NOTIFICATIONS_ENABLED', false),
        'webhook_url' => env('ERROR_SLACK_WEBHOOK_URL'),
        'channel' => env('ERROR_SLACK_CHANNEL', '#error-alerts'),
        'username' => env('ERROR_SLACK_USERNAME', 'UEM Error Bot'),
        'icon_emoji' => env('ERROR_SLACK_ICON', ':boom:'),

        // --- NUOVE OPZIONI GDPR ---
        'include_ip_address' => env('ERROR_SLACK_INCLUDE_IP', false),       // Default: NO
        'include_user_details' => env('ERROR_SLACK_INCLUDE_USER', false),   // Default: NO (Just ID maybe?)
        'include_context' => env('ERROR_SLACK_INCLUDE_CONTEXT', true),      // Default: YES (sanitized)
        'include_trace_snippet' => env('ERROR_SLACK_INCLUDE_TRACE', false), // Default: NO (Trace can be very long for Slack)
        'context_sensitive_keys' => [ // Lista per Slack
            'password',
            'secret',
            'token',
            'auth',
            'key',
            'credentials',
            'authorization',
            'php_auth_user',
            'php_auth_pw',
            'credit_card',
            'creditcard',
            'card_number',
            'cvv',
            'cvc',
            'api_key',
            'secret_key',
            'access_token',
            'refresh_token',
            // Aggiungere chiavi specifiche se necessario
        ],
        'context_max_length' => env('ERROR_SLACK_CONTEXT_LENGTH', 1500), // Limit context length in Slack message
        'trace_max_lines' => env('ERROR_SLACK_TRACE_LINES', 10), // Limit trace lines in Slack message
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration (UEM Specific)
    |--------------------------------------------------------------------------
    | Settings affecting logging handlers (LogHandler, DatabaseLogHandler).
    */
    'logging' => [
        // Note: Main log channel is configured in ULM, not here.
        // 'channel' => env('ERROR_LOG_CHANNEL', 'stack'), // Redundant if using ULM properly
        'detailed_context_in_log' => env('ERROR_LOG_DETAILED_CONTEXT', true), // Affects standard LogHandler context
    ],

    /*
     |--------------------------------------------------------------------------
     | Database Logging Configuration
     |--------------------------------------------------------------------------
     */
    'database_logging' => [
        'enabled' => env('ERROR_DB_LOGGING_ENABLED', true), // Enable DB logging by default
        'include_trace' => env('ERROR_DB_LOG_INCLUDE_TRACE', true), // Log stack traces to DB
        'max_trace_length' => env('ERROR_DB_LOG_MAX_TRACE_LENGTH', 10000), // Max chars for DB trace

        /**
         * 🛡️ Sensitive Keys for Context Redaction.
         * Keys listed here (case-insensitive) will have their values
         * replaced with '[REDACTED]' before the context is saved to the database log.
         * Add any application-specific keys containing PII or secrets.
         */
        'sensitive_keys' => [
            // Defaults (from DatabaseLogHandler)
            'password',
            'secret',
            'token',
            'auth',
            'key',
            'credentials',
            'authorization',
            'php_auth_user',
            'php_auth_pw',
            'credit_card',
            'creditcard', // Variations
            'card_number',
            'cvv',
            'cvc',
            'api_key',
            'secret_key',
            'access_token',
            'refresh_token',
            // Aggiungi qui chiavi specifiche di FlorenceEGI se necessario
            // 'wallet_private_key',
            // 'user_personal_identifier',
            // 'financial_details',
        ],

    ],


    /*
    |--------------------------------------------------------------------------
    | UI Error Display
    |--------------------------------------------------------------------------
    */
    'ui' => [
        'default_display_mode' => env('ERROR_UI_DEFAULT_DISPLAY', 'sweet-alert'), // 'div', 'sweet-alert', 'toast'
        'show_error_codes' => env('ERROR_UI_SHOW_CODES', false), // Show codes like [E_...] to users?
        'generic_error_message' => 'error-manager::errors.user.generic_error', // Translation key for generic messages
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Type Definitions
    |--------------------------------------------------------------------------
    | Defines behavior associated with error severity levels.
    */
    'error_types' => [
        'critical' => [
            'log_level' => 'critical', // Maps to PSR LogLevel
            'notify_team' => true, // Default: Should Email/Slack handlers trigger?
            'http_status' => 500, // Default HTTP status
        ],
        'error' => [
            'log_level' => 'error',
            'notify_team' => false,
            'http_status' => 400, // Often client errors or recoverable server issues
        ],
        'warning' => [
            'log_level' => 'warning',
            'notify_team' => false,
            'http_status' => 400, // Often user input validation
        ],
        'notice' => [
            'log_level' => 'notice',
            'notify_team' => false,
            'http_status' => 200, // Not typically an "error" status
        ],
        // Consider adding 'info' if needed
    ],

    /*
    |--------------------------------------------------------------------------
    | Blocking Level Definitions
    |--------------------------------------------------------------------------
    | Defines impact on application flow.
    */
    'blocking_levels' => [
        'blocking' => [
            'terminate_request' => true, // Should middleware stop request propagation? (UEM itself doesn't enforce this directly)
            'clear_session' => false, // Example: Should session be cleared?
        ],
        'semi-blocking' => [
            'terminate_request' => false, // Allows request to potentially complete
            'flash_session' => true, // Should UI handler flash message?
        ],
        'not' => [ // Non-blocking
            'terminate_request' => false,
            'flash_session' => true, // Still might want to inform user
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Error Configuration
    |--------------------------------------------------------------------------
    | Used if 'UNDEFINED_ERROR_CODE' itself is not defined. Should always exist.
    */
    'fallback_error' => [
        'type' => 'critical', // Treat any fallback situation as critical
        'blocking' => 'blocking',
        'dev_message_key' => 'error-manager::errors.dev.fatal_fallback_failure', // Use the fatal key here
        'user_message_key' => 'error-manager::errors.user.fatal_fallback_failure',
        'http_status_code' => 500,
        'devTeam_email_need' => true,
        'msg_to' => 'sweet-alert', // Show prominent alert
        'notify_slack' => true, // Also notify slack if configured
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Definitions (Code => Configuration)
    |--------------------------------------------------------------------------
    */
    'errors' => [

        // ====================================================
        // META / Generic Fallbacks
        // ====================================================
        'UNDEFINED_ERROR_CODE' => [
            'type' => 'critical',
            'blocking' => 'blocking', // Treat undefined code as blocking
            'dev_message_key' => 'error-manager::errors.dev.undefined_error_code',
            'user_message_key' => 'error-manager::errors.user.unexpected_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true, // Notify Slack too
            'msg_to' => 'sweet-alert',
        ],
        'FATAL_FALLBACK_FAILURE' => [ // Only used if fallback_error itself fails
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.fatal_fallback_failure',
            'user_message_key' => 'error-manager::errors.user.fatal_fallback_failure',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],
        // FALLBACK_ERROR is defined above in 'fallback_error' key
        'UNEXPECTED_ERROR' => [ // Generic catch-all from middleware mapping
            'type' => 'critical',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.unexpected_error',
            'user_message_key' => 'error-manager::errors.user.unexpected_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],
        'GENERIC_SERVER_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.generic_server_error',
            'user_message_key' => 'error-manager::errors.user.generic_server_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],
        'JSON_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.json_error',
            'user_message_key' => 'error-manager::errors.user.json_error',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],
        'INVALID_INPUT' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.invalid_input',
            'user_message_key' => 'error-manager::errors.user.invalid_input',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],

        // ====================================================
        // Authentication & Authorization Errors (Mapped from Middleware)
        // ====================================================
        'AUTHENTICATION_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.authentication_error',
            'user_message_key' => 'error-manager::errors.user.authentication_error',
            'http_status_code' => 401,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert', // Or redirect
        ],
        'AUTHORIZATION_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.authorization_error',
            'user_message_key' => 'error-manager::errors.user.authorization_error',
            'http_status_code' => 403,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],
        'CSRF_TOKEN_MISMATCH' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.csrf_token_mismatch',
            'user_message_key' => 'error-manager::errors.user.csrf_token_mismatch',
            'http_status_code' => 419,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert', // Inform user to refresh
        ],

        // ====================================================
        // Routing & Request Errors (Mapped from Middleware)
        // ====================================================
        'ROUTE_NOT_FOUND' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.route_not_found',
            'user_message_key' => 'error-manager::errors.user.route_not_found',
            'http_status_code' => 404,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'log-only', // Let Laravel handle 404 page
        ],

        'RESOURCE_NOT_FOUND' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.resource_not_found',
            'user_message_key' => 'error-manager::errors.user.unexpected_error',
            'http_status_code' => 404,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert', // Inform user resource is missing
        ],

        'METHOD_NOT_ALLOWED' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.method_not_allowed',
            'user_message_key' => 'error-manager::errors.user.method_not_allowed',
            'http_status_code' => 405,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'log-only', // Let Laravel handle 405 page
        ],
        'TOO_MANY_REQUESTS' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.too_many_requests',
            'user_message_key' => 'error-manager::errors.user.too_many_requests',
            'http_status_code' => 429,
            'devTeam_email_need' => false,
            'notify_slack' => true, // Might indicate an attack or config issue
            'msg_to' => 'sweet-alert',
        ],

        // ====================================================
        // Database / Model Errors (Mapped + Specifics)
        // ====================================================
        'DATABASE_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.database_error',
            'user_message_key' => 'error-manager::errors.user.database_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],
        'RECORD_NOT_FOUND' => [
            'type' => 'error', // Or warning depending on context
            'blocking' => 'blocking', // Usually stops the current action
            'dev_message_key' => 'error-manager::errors.dev.record_not_found',
            'user_message_key' => 'error-manager::errors.user.record_not_found',
            'http_status_code' => 404,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],
        'RECORD_EGI_NOT_FOUND_IN_RESERVATION_CONTROLLER' => [
            'type' => 'warning',
            'blocking' => 'not', // Usually stops the current action
            'dev_message_key' => 'error-manager::errors.dev.record_not_found_egi_in_reservation_controller',
            'user_message_key' => '',
            'http_status_code' => 200, // Not a real HTTP error, just a warning
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'log-only',
        ],
        'ERROR_DURING_CREATE_EGI_RECORD' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.error_during_create_egi_record',
            'user_message_key' => 'error-manager::errors.user.error_during_create_egi_record',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        // ====================================================
        // REGISTRATION VALIDATION ERRORS - Add to config/error-manager.php
        // ====================================================

        'REGISTRATION_EMAIL_ALREADY_EXISTS' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.registration_email_already_exists',
            'user_message_key' => 'error-manager::errors.user.registration_email_already_exists',
            'http_status_code' => 422,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div', // Mostra nel form
        ],

        'REGISTRATION_PASSWORD_TOO_WEAK' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.registration_password_too_weak',
            'user_message_key' => 'error-manager::errors.user.registration_password_too_weak',
            'http_status_code' => 422,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],

        'REGISTRATION_PASSWORD_CONFIRMATION_MISMATCH' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.registration_password_confirmation_mismatch',
            'user_message_key' => 'error-manager::errors.user.registration_password_confirmation_mismatch',
            'http_status_code' => 422,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],

        'REGISTRATION_INVALID_EMAIL_FORMAT' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.registration_invalid_email_format',
            'user_message_key' => 'error-manager::errors.user.registration_invalid_email_format',
            'http_status_code' => 422,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],

        'REGISTRATION_REQUIRED_FIELD_MISSING' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.registration_required_field_missing',
            'user_message_key' => 'error-manager::errors.user.registration_required_field_missing',
            'http_status_code' => 422,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],

        'REGISTRATION_VALIDATION_COMPREHENSIVE_FAILED' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.registration_validation_comprehensive_failed',
            'user_message_key' => 'error-manager::errors.user.registration_validation_comprehensive_failed',
            'http_status_code' => 422,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],

        // ====================================================
        // Validation Errors (Mapped + Specifics)
        // ====================================================
        'VALIDATION_ERROR' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.validation_error',
            'user_message_key' => 'error-manager::errors.user.validation_error',
            'http_status_code' => 422,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div', // Usually shown inline with form fields
        ],
        'INVALID_IMAGE_STRUCTURE' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.invalid_image_structure',
            'user_message_key' => 'error-manager::errors.user.invalid_image_structure',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],
        'MIME_TYPE_NOT_ALLOWED' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.mime_type_not_allowed',
            'user_message_key' => 'error-manager::errors.user.mime_type_not_allowed',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],
        'MAX_FILE_SIZE' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.max_file_size',
            'user_message_key' => 'error-manager::errors.user.max_file_size',
            'http_status_code' => 413, // Payload Too Large
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],
        'INVALID_FILE_EXTENSION' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.invalid_file_extension',
            'user_message_key' => 'error-manager::errors.user.invalid_file_extension',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],
        'INVALID_FILE_NAME' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.invalid_file_name',
            'user_message_key' => 'error-manager::errors.user.invalid_file_name',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],
        'INVALID_FILE_PDF' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.invalid_file_pdf',
            'user_message_key' => 'error-manager::errors.user.invalid_file_pdf',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],
        'INVALID_FILE' => [ // More generic file issue?
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.invalid_file',
            'user_message_key' => 'error-manager::errors.user.invalid_file',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],
        'INVALID_FILE_VALIDATION' => [ // Specific validation context
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.invalid_file_validation',
            'user_message_key' => 'error-manager::errors.user.invalid_file_validation',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],

        // ====================================================
        // UUM (Upload) Related Errors (Esistenti - verified/adjusted)
        // ====================================================
        'VIRUS_FOUND' => [
            'type' => 'error', // Changed from warning, this is a security event
            'blocking' => 'blocking', // Stop processing this file
            'dev_message_key' => 'error-manager::errors.dev.virus_found',
            'user_message_key' => 'error-manager::errors.user.virus_found',
            'http_status_code' => 422, // Unprocessable Entity
            'devTeam_email_need' => false, // May become true if frequent/unexpected
            'notify_slack' => true, // Good to know about virus alerts
            'msg_to' => 'sweet-alert',
        ],
        'SCAN_ERROR' => [
            'type' => 'warning', // Scan failed, not necessarily insecure
            'blocking' => 'semi-blocking', // Allow retry potentially
            'dev_message_key' => 'error-manager::errors.dev.scan_error',
            'user_message_key' => 'error-manager::errors.user.scan_error',
            'http_status_code' => 500, // Service unavailable?
            'devTeam_email_need' => true, // If scanner service is down
            'notify_slack' => true,
            'msg_to' => 'div',
            'recovery_action' => 'retry_scan', // Defined recovery
        ],
        'TEMP_FILE_NOT_FOUND' => [
            'type' => 'error', // Changed from warning, indicates logic flaw
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.temp_file_not_found',
            'user_message_key' => 'error-manager::errors.user.temp_file_not_found',
            'http_status_code' => 404,
            'devTeam_email_need' => true, // Investigate why temp file is missing
            'notify_slack' => true,
            'msg_to' => 'div',
        ],
        'FILE_NOT_FOUND' => [ // Generic file not found
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.file_not_found',
            'user_message_key' => 'error-manager::errors.user.file_not_found',
            'http_status_code' => 404,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],
        'ERROR_GETTING_PRESIGNED_URL' => [
            'type' => 'error', // Changed from critical, maybe recoverable network issue
            'blocking' => 'semi-blocking', // Allow retry
            'dev_message_key' => 'error-manager::errors.dev.error_getting_presigned_url',
            'user_message_key' => 'error-manager::errors.user.error_getting_presigned_url',
            'http_status_code' => 500,
            'devTeam_email_need' => true, // If storage provider is down
            'notify_slack' => true,
            'msg_to' => 'div',
            'recovery_action' => 'retry_presigned',
        ],
        'ERROR_DURING_FILE_UPLOAD' => [
            'type' => 'error', // Changed from critical, network issues happen
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.error_during_file_upload',
            'user_message_key' => 'error-manager::errors.user.error_during_file_upload',
            'http_status_code' => 500, // Or maybe client-related? Needs context.
            'devTeam_email_need' => true, // If persistent
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
            'recovery_action' => 'retry_upload',
        ],
        'ERROR_DELETING_LOCAL_TEMP_FILE' => [
            'type' => 'warning', // Changed from critical, cleanup can be retried
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.error_deleting_local_temp_file',
            'user_message_key' => null, // Internal issue
            'http_status_code' => 500,
            'devTeam_email_need' => false, // Unless very frequent
            'notify_slack' => false,
            'msg_to' => 'log-only',
            'recovery_action' => 'schedule_cleanup',
        ],
        'ERROR_DELETING_EXT_TEMP_FILE' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.error_deleting_ext_temp_file',
            'user_message_key' => null,
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'log-only',
            'recovery_action' => 'schedule_cleanup',
        ],
        'UNABLE_TO_SAVE_BOT_FILE' => [
            'type' => 'critical', // If bot relies on this, it's critical
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.unable_to_save_bot_file',
            'user_message_key' => 'error-manager::errors.user.unable_to_save_bot_file',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'div',
        ],
        'UNABLE_TO_CREATE_DIRECTORY' => [
            'type' => 'critical', // Filesystem permission issue?
            'blocking' => 'blocking', // Uploads likely blocked
            'dev_message_key' => 'error-manager::errors.dev.unable_to_create_directory',
            'user_message_key' => 'error-manager::errors.user.generic_internal_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'log-only',
            'recovery_action' => 'create_temp_directory',
        ],
        'UNABLE_TO_CHANGE_PERMISSIONS' => [
            'type' => 'critical',
            'blocking' => 'not', // May not block immediately but needs fixing
            'dev_message_key' => 'error-manager::errors.dev.unable_to_change_permissions',
            'user_message_key' => 'error-manager::errors.user.generic_internal_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'log-only',
        ],
        'IMPOSSIBLE_SAVE_FILE' => [
            'type' => 'critical', // File saving failed entirely
            'blocking' => 'semi-blocking', // User needs to know
            'dev_message_key' => 'error-manager::errors.dev.impossible_save_file',
            'user_message_key' => 'error-manager::errors.user.impossible_save_file',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],
        'ERROR_SAVING_FILE_METADATA' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.error_saving_file_metadata',
            'user_message_key' => 'error-manager::errors.user.error_saving_file_metadata',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'div',
            'recovery_action' => 'retry_metadata_save',
        ],
        'ACL_SETTING_ERROR' => [
            'type' => 'critical', // Security related
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.acl_setting_error',
            'user_message_key' => 'error-manager::errors.user.acl_setting_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'div',
        ],
        'ERROR_DURING_FILE_NAME_ENCRYPTION' => [
            'type' => 'critical', // Security related
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.error_during_file_name_encryption',
            'user_message_key' => 'error-manager::errors.user.error_during_file_name_encryption',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'div',
        ],

        // ====================================================
        // UCM (Config) Related Errors (Esistenti - verified/adjusted)
        // ====================================================
        'UCM_DUPLICATE_KEY' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.ucm_duplicate_key',
            'user_message_key' => 'error-manager::errors.user.ucm_duplicate_key',
            'http_status_code' => 422, // Unprocessable entity seems appropriate
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],
        'UCM_CREATE_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.ucm_create_failed',
            'user_message_key' => 'error-manager::errors.user.ucm_create_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],
        'UCM_UPDATE_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.ucm_update_failed',
            'user_message_key' => 'error-manager::errors.user.ucm_update_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],
        'UCM_NOT_FOUND' => [
            'type' => 'error', // Could be expected if key is optional
            'blocking' => 'not', // Changed to non-blocking, logic should handle null
            'dev_message_key' => 'error-manager::errors.dev.ucm_not_found',
            'user_message_key' => 'error-manager::errors.user.ucm_not_found', // Maybe a generic "setting not found"?
            'http_status_code' => 404,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'log-only', // Log it, but don't bother user usually
        ],
        'UCM_DELETE_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking', // Failed to delete, might leave inconsistent state
            'dev_message_key' => 'error-manager::errors.dev.ucm_delete_failed',
            'user_message_key' => 'error-manager::errors.user.ucm_delete_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],
        'UCM_AUDIT_NOT_FOUND' => [
            'type' => 'notice', // Changed from info, less noisy
            'blocking' => 'not', // Non-blocking
            'dev_message_key' => 'error-manager::errors.dev.ucm_audit_not_found',
            'user_message_key' => 'error-manager::errors.user.ucm_audit_not_found',
            'http_status_code' => 404, // Consistent not found
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div', // Show in context if needed
        ],

        // ====================================================
        // UTM (Translation) Errors (Nuovi - Verified/Adjusted)
        // ====================================================
        'UTM_LOAD_FAILED' => [
            'type' => 'error', // Changed from warning, failure to load lang file is an error
            'blocking' => 'not', // But might fallback to default language
            'dev_message_key' => 'error-manager::errors.dev.utm_load_failed',
            'user_message_key' => null, // Internal issue, user sees fallback lang
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true, // Let devs know quickly
            'msg_to' => 'log-only',
        ],
        'UTM_INVALID_LOCALE' => [
            'type' => 'warning', // Invalid locale requested
            'blocking' => 'not', // System likely falls back to default
            'dev_message_key' => 'error-manager::errors.dev.utm_invalid_locale',
            'user_message_key' => null, // User sees default language content
            'http_status_code' => 400, // Bad request potentially (depending on source)
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'log-only',
        ],

        // ====================================================
        // UEM Internal Handler Errors (Nuovi - Verified/Adjusted)
        // ====================================================
        'UEM_EMAIL_SEND_FAILED' => [
            'type' => 'critical', // Changed from error - failure to notify IS critical
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.uem_email_send_failed',
            'user_message_key' => null,
            'http_status_code' => 500,
            'devTeam_email_need' => false, // Avoid loop - logged by handler
            'notify_slack' => true, // Try alternative notification
            'msg_to' => 'log-only',
        ],
        'UEM_SLACK_SEND_FAILED' => [
            'type' => 'critical', // Changed from error
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.uem_slack_send_failed',
            'user_message_key' => null,
            'http_status_code' => 500,
            'devTeam_email_need' => true, // Try email if slack failed
            'notify_slack' => false, // Avoid loop
            'msg_to' => 'log-only',
        ],
        'UEM_RECOVERY_ACTION_FAILED' => [
            'type' => 'error', // Changed from warning - recovery failure IS an error
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.uem_recovery_action_failed',
            'user_message_key' => null, // User sees original error message
            'http_status_code' => 500,
            'devTeam_email_need' => true, // Need to know why recovery failed
            'notify_slack' => true,
            'msg_to' => 'log-only',
        ],

        'UEM_USER_UNAUTHENTICATED' => [
            'type' => 'auth', // O 'error' se preferisci
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.user_unauthenticated_access', // Chiave per messaggio tecnico
            'user_message_key' => 'error-manager::errors.user.user_unauthenticated_access', // Chiave per messaggio utente
            'http_status_code' => 401, // Unauthorized
            'devTeam_email_need' => false, // A meno che non sia un fallimento inaspettato del middleware
            'notify_slack' => false,
            'msg_to' => 'json', // Solitamente per API
        ],

        'UEM_SET_CURRENT_COLLECTION_FORBIDDEN' => [
            'type' => 'security', // O 'error'
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.set_current_collection_forbidden',
            'user_message_key' => 'error-manager::errors.user.set_current_collection_forbidden',
            'http_status_code' => 403, // Forbidden
            'devTeam_email_need' => true, // Potrebbe indicare un tentativo di accesso anomalo
            'notify_slack' => true,
            'msg_to' => 'json',
        ],

        'UEM_SET_CURRENT_COLLECTION_FAILED' => [
            'type' => 'critical', // Un fallimento nel salvare il DB è solitamente critico
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.set_current_collection_failed',
            'user_message_key' => 'error-manager::errors.user.set_current_collection_failed',
            'http_status_code' => 500, // Internal Server Error
            'devTeam_email_need' => true, // Notifica sempre per errori 500
            'notify_slack' => true,
            'msg_to' => 'json',
        ],

        // ====================================================
        // EGI Upload Specific Errors
        // ====================================================
        'EGI_AUTH_REQUIRED' => [ // User not authenticated attempting upload
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.egi_auth_required', // "Authentication required for EGI upload."
            'user_message_key' => 'error-manager::errors.user.egi_auth_required', // "Please log in to upload an EGI."
            'http_status_code' => 401,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert', // Or redirect to login
        ],
        'EGI_UNAUTHORIZED_ACCESS' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.egi_unauthorized_access',
            'user_message_key' => 'error-manager::errors.user.egi_unauthorized_access',
            'http_status_code' => 401,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'log-only', // Redirect diretto senza SweetAlert
        ],
        'EGI_FILE_INPUT_ERROR' => [ // Problem with the 'file' part of the request (missing, invalid upload)
            'type' => 'warning',
            'blocking' => 'blocking', // Stop the process
            'dev_message_key' => 'error-manager::errors.dev.egi_file_input_error', // "Invalid or missing 'file' input. Upload error code: :code"
            'user_message_key' => 'error-manager::errors.user.egi_file_input_error', // "Please select a valid file to upload."
            'http_status_code' => 400, // Bad Request
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div', // Show near the file input
        ],

        'EGI_PAGE_ACCESS_NOTICE' => [
            'type' => 'notice',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.egi_page_access_notice',
            'user_message_key' => null, // No user message needed
            'http_status_code' => 200,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'log-only', // Solo log, nessuna visualizzazione all'utente
        ],

        'EGI_PAGE_RENDERING_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.egi_page_rendering_error',
            'user_message_key' => 'error-manager::errors.user.egi_page_rendering_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true, // Notifica il team via email
            'notify_slack' => true, // Notifica anche su Slack se configurato
            'msg_to' => 'sweet-alert', // Mostra un alert all'utente
        ],

        'EGI_UPDATE_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.egi_update_failed',
            'user_message_key' => 'error-manager::errors.user.egi_update_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'EGI_DELETE_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.egi_delete_failed',
            'user_message_key' => 'error-manager::errors.user.egi_delete_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'EGI_MINTED_CANNOT_DELETE' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.egi_minted_cannot_delete',
            'user_message_key' => 'error-manager::errors.user.egi_minted_cannot_delete',
            'http_status_code' => 403,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'EGI_TRAIT_DELETE_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.egi_trait_delete_failed',
            'user_message_key' => 'error-manager::errors.user.egi_trait_delete_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        // Traits API specific errors
        'TRAITS_CATEGORIES_LOAD_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.traits_categories_load_failed',
            'user_message_key' => 'error-manager::errors.user.traits_categories_load_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'TRAITS_UNAUTHORIZED_ACCESS' => [
            'type' => 'security',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.traits_unauthorized_access',
            'user_message_key' => 'error-manager::errors.user.traits_unauthorized_access',
            'http_status_code' => 403,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'TRAITS_EGI_PUBLISHED' => [
            'type' => 'validation',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.traits_egi_published',
            'user_message_key' => 'error-manager::errors.user.traits_egi_published',
            'http_status_code' => 403,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'TRAITS_EGI_MINTED' => [
            'type' => 'validation',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.traits_egi_minted',
            'user_message_key' => 'error-manager::errors.user.traits_egi_minted',
            'http_status_code' => 403,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'UTILITY_EGI_MINTED' => [
            'type' => 'validation',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.utility_egi_minted',
            'user_message_key' => 'error-manager::errors.user.utility_egi_minted',
            'http_status_code' => 403,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'EGI_METADATA_IMMUTABLE' => [
            'type' => 'validation',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.egi_metadata_immutable',
            'user_message_key' => 'error-manager::errors.user.egi_metadata_immutable',
            'http_status_code' => 403,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'TRAITS_NO_DATA_PROVIDED' => [
            'type' => 'validation',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.traits_no_data_provided',
            'user_message_key' => 'error-manager::errors.user.traits_no_data_provided',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'TRAITS_SAVE_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.traits_save_failed',
            'user_message_key' => 'error-manager::errors.user.traits_save_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'TRAITS_ADD_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.traits_add_failed',
            'user_message_key' => 'error-manager::errors.user.traits_add_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'EGI_NOT_FOUND' => [
            'type' => 'validation',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.egi_not_found',
            'user_message_key' => 'error-manager::errors.user.egi_not_found',
            'http_status_code' => 404,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'TRAIT_NOT_FOUND' => [
            'type' => 'validation',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.trait_not_found',
            'user_message_key' => 'error-manager::errors.user.trait_not_found',
            'http_status_code' => 404,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // ====================================================
        // Errori specifici per Collection CRUD Operations
        // ====================================================

        'COLLECTION_UPDATE_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.collection_update_failed',
            'user_message_key' => 'error-manager::errors.user.collection_update_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'COLLECTION_DELETE_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.collection_delete_failed',
            'user_message_key' => 'error-manager::errors.user.collection_delete_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        // ====================================================
        // Errori specifici per la validazione EGI
        // ====================================================
        'INVALID_EGI_FILE' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.invalid_egi_file',
            'user_message_key' => 'error-manager::errors.user.invalid_egi_file',
            'http_status_code' => 422, // Unprocessable Entity
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div', // Mostra errori di validazione in un div
        ],


        // ====================================================
        // Errori specifici per l'elaborazione EGI
        // ====================================================

        'ERROR_DURING_EGI_PROCESSING' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.error_during_egi_processing',
            'user_message_key' => 'error-manager::errors.user.error_during_egi_processing',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'EGI_VALIDATION_FAILED' => [ // Metadata validation failed ($request->validate())
            'type' => 'warning',
            'blocking' => 'semi-blocking', // Allow user to correct and resubmit
            'dev_message_key' => 'error-manager::errors.dev.egi_validation_failed', // "EGI metadata validation failed." (Details in context/response)
            'user_message_key' => 'error-manager::errors.user.egi_validation_failed', // "Please correct the highlighted fields."
            'http_status_code' => 422, // Unprocessable Entity (standard for validation errors)
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div', // Display errors near form fields
        ],
        'EGI_COLLECTION_INIT_ERROR' => [ // Failure during findOrCreateDefaultCollection (critical part)
            'type' => 'critical',
            'blocking' => 'blocking', // Cannot proceed without collection context
            'dev_message_key' => 'error-manager::errors.dev.egi_collection_init_error', // "Critical error initializing default collection for user :user_id."
            'user_message_key' => 'error-manager::errors.user.egi_collection_init_error', // "Could not prepare your collection. Please contact support."
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],
        'EGI_CRYPTO_ERROR' => [ // Failure during filename encryption
            'type' => 'critical', // Security / Data integrity related
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.egi_crypto_error', // "Failed to encrypt filename: :filename"
            'user_message_key' => 'error-manager::errors.user.generic_internal_error', // Generic user message
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],
        'EGI_DB_ERROR' => [ // Specific database error during Egi model save/update
            'type' => 'critical',
            'blocking' => 'blocking', // Transaction will likely rollback
            'dev_message_key' => 'error-manager::errors.dev.egi_db_error', // "Database error processing EGI :egi_id for collection :collection_id."
            'user_message_key' => 'error-manager::errors.user.generic_internal_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],
        'EGI_STORAGE_CRITICAL_FAILURE' => [ // Failure saving to a critical disk
            'type' => 'critical',
            'blocking' => 'blocking', // Transaction will likely rollback
            'dev_message_key' => 'error-manager::errors.dev.egi_storage_critical_failure', // "Critical failure saving EGI :egi_id file to disk(s): :disks"
            'user_message_key' => 'error-manager::errors.user.egi_storage_failure', // "Failed to securely store the EGI file. Please try again or contact support."
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],
        'EGI_STORAGE_CONFIG_ERROR' => [ // Fallback disk 'local' is not configured
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.egi_storage_config_error', // "'local' storage disk required for fallback is not configured."
            'user_message_key' => 'error-manager::errors.user.generic_internal_error', // Config error
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'log-only', // Or sweet-alert if needed
        ],
        'EGI_UNEXPECTED_ERROR' => [ // Catch-all for other unexpected errors in the EGI handler flow
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.egi_unexpected_error', // "Unexpected error during EGI processing for file :original_filename."
            'user_message_key' => 'error-manager::errors.user.egi_unexpected_error', // "An unexpected error occurred while processing your EGI."
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        // ====================================================
        // PA Acts Tokenization Error Codes
        // ====================================================
        'PA_ACT_AUTH_REQUIRED' => [ // User not authenticated attempting PA act upload
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.pa_act_auth_required',
            'user_message_key' => 'error-manager::errors_2.user.pa_act_auth_required',
            'http_status_code' => 401,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert', // Redirect to login
        ],

        'PA_ACT_ROLE_REQUIRED' => [ // User lacks PA entity role
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.pa_act_role_required',
            'user_message_key' => 'error-manager::errors_2.user.pa_act_role_required',
            'http_status_code' => 403,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'PA_ACT_VALIDATION_FAILED' => [ // Metadata validation failed
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.pa_act_validation_failed',
            'user_message_key' => 'error-manager::errors_2.user.pa_act_validation_failed',
            'http_status_code' => 422,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div', // Show near form fields
        ],

        'PA_ACT_INVALID_FILE' => [ // Invalid file type or size
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.pa_act_invalid_file',
            'user_message_key' => 'error-manager::errors_2.user.pa_act_invalid_file',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],

        'PA_ACT_INVALID_SIGNATURE' => [ // QES/PAdES signature validation failed
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.pa_act_invalid_signature',
            'user_message_key' => 'error-manager::errors_2.user.pa_act_invalid_signature',
            'http_status_code' => 400,
            'devTeam_email_need' => true, // Log signature failures for security
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'PA_ACT_COLLECTION_FAILED' => [ // Collection (fascicolo) creation/management failed
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.pa_act_collection_failed',
            'user_message_key' => 'error-manager::errors_2.user.pa_act_collection_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'PA_ACT_UPLOAD_FAILED' => [ // General upload failure
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.pa_act_upload_failed',
            'user_message_key' => 'error-manager::errors_2.user.pa_act_upload_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'PA_ACT_BLOCKCHAIN_ANCHOR_FAILED' => [ // Blockchain anchoring failed
            'type' => 'critical',
            'blocking' => 'not', // Non-blocking: document saved, anchoring retry later
            'dev_message_key' => 'error-manager::errors_2.dev.pa_act_blockchain_anchor_failed',
            'user_message_key' => 'error-manager::errors_2.user.pa_act_blockchain_anchor_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'log-only', // User sees warning in UI, not blocking
        ],

        'PA_ACT_MERKLE_VERIFICATION_FAILED' => [ // Merkle proof verification failed (public verification)
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.pa_act_merkle_verification_failed',
            'user_message_key' => 'error-manager::errors.user.pa_act_merkle_verification_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true, // Critical: blockchain data integrity issue
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        // ====================================================
        // Image Optimization Error Codes
        // ====================================================
        'IMAGE_OPTIMIZATION_INVALID_FILE' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.image_optimization_invalid_file',
            'user_message_key' => 'error-manager::errors.user.image_optimization_invalid_file',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'IMAGE_OPTIMIZATION_UNSUPPORTED_FORMAT' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.image_optimization_unsupported_format',
            'user_message_key' => 'error-manager::errors.user.image_optimization_unsupported_format',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'IMAGE_OPTIMIZATION_PROCESSING_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.image_optimization_processing_failed',
            'user_message_key' => 'error-manager::errors.user.image_optimization_processing_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'IMAGE_OPTIMIZATION_STORAGE_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.image_optimization_storage_failed',
            'user_message_key' => 'error-manager::errors.user.image_optimization_storage_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'IMAGE_OPTIMIZATION_IMAGICK_UNAVAILABLE' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.image_optimization_imagick_unavailable',
            'user_message_key' => 'error-manager::errors.user.image_optimization_imagick_unavailable',
            'http_status_code' => 503,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'IMAGE_OPTIMIZATION_CONVERSION_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.image_optimization_conversion_failed',
            'user_message_key' => 'error-manager::errors.user.image_optimization_conversion_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'IMAGE_OPTIMIZATION_VARIANT_CREATION_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.image_optimization_variant_creation_failed',
            'user_message_key' => 'error-manager::errors.user.image_optimization_variant_creation_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'IMAGE_OPTIMIZATION_FILE_TOO_LARGE' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.image_optimization_file_too_large',
            'user_message_key' => 'error-manager::errors.user.image_optimization_file_too_large',
            'http_status_code' => 413,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'IMAGE_OPTIMIZATION_INVALID_DIMENSIONS' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.image_optimization_invalid_dimensions',
            'user_message_key' => 'error-manager::errors.user.image_optimization_invalid_dimensions',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // ====================================================
        // System / Environment Errors (Esistenti - Verified/Adjusted)
        // ====================================================
        'IMAGICK_NOT_AVAILABLE' => [
            'type' => 'critical',
            'blocking' => 'blocking', // If image processing is core
            'dev_message_key' => 'error-manager::errors.dev.imagick_not_available',
            'user_message_key' => 'error-manager::errors.user.imagick_not_available', // Inform user nicely
            'http_status_code' => 500, // Misconfiguration
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],
        'SERVER_LIMITS_RESTRICTIVE' => [ // Example: PHP memory limit, upload size etc. detected low
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.server_limits_restrictive', // E.g., "PHP memory_limit is low (:limit)"
            'user_message_key' => null, // Not a user error
            'http_status_code' => 500, // Reflects potential future issue
            'devTeam_email_need' => true, // Ops/Dev team needs to adjust server config
            'notify_slack' => true,
            'msg_to' => 'log-only',
        ],

        // ====================================================
        // Errori specifici per la creazione e gestione Wallet
        // ====================================================

        'WALLET_CREATION_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.wallet_creation_failed',
            'user_message_key' => 'error-manager::errors.user.wallet_creation_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'WALLET_QUOTA_CHECK_ERROR' => [
            'type' => 'error',
            'blocking' => 'not', // Non-blocking, just log
            'dev_message_key' => 'error-manager::errors.dev.wallet_quota_check_error',
            'user_message_key' => null, // No user-visible message needed
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'log-only',
        ],

        'WALLET_INSUFFICIENT_QUOTA' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.wallet_insufficient_quota',
            'user_message_key' => 'error-manager::errors.user.wallet_insufficient_quota',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],

        'WALLET_ADDRESS_INVALID' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.wallet_address_invalid',
            'user_message_key' => 'error-manager::errors.user.wallet_address_invalid',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],

        'WALLET_NOT_FOUND' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.wallet_not_found',
            'user_message_key' => 'error-manager::errors.user.wallet_not_found',
            'http_status_code' => 404,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],

        'WALLET_ALREADY_EXISTS' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.wallet_already_exists',
            'user_message_key' => 'error-manager::errors.user.wallet_already_exists',
            'http_status_code' => 409, // Conflict
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],
        'WALLET_INVALID_SECRET' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.wallet_invalid_secret',
            'user_message_key' => 'error-manager::errors.user.wallet_invalid_secret',
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'http_status_code' => 401,
            'msg_to' => 'sweet-alert',
        ],

        'WALLET_VALIDATION_FAILED' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.wallet_validation_failed',
            'user_message_key' => 'error-manager::errors.user.wallet_validation_failed',
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'http_status_code' => 422,
            'msg_to' => 'div',
        ],

        'WALLET_CONNECTION_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.wallet_connection_failed',
            'user_message_key' => 'error-manager::errors.user.wallet_connection_failed',
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'http_status_code' => 422,
            'msg_to' => 'sweet-alert',
        ],

        'WALLET_DISCONNECT_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.wallet_disconnect_failed',
            'user_message_key' => 'error-manager::errors.user.wallet_disconnect_failed',
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'http_status_code' => 422,
            'msg_to' => 'toast',
        ],

        // ====================================================
        // Errori specifici per la gestione delle collezioni
        // ====================================================

        'COLLECTION_CREATION_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.collection_creation_failed',
            'user_message_key' => 'error-manager::errors.user.collection_creation_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'COLLECTION_FIND_CREATE_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.collection_find_create_failed',
            'user_message_key' => 'error-manager::errors.user.collection_find_create_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'AUTH_REQUIRED' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.auth_required',
            'user_message_key' => 'error-manager::errors.user.auth_required',
            'http_status_code' => 401,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // ====================================================
        // ENHANCED REGISTRATION ERROR CODES - Add to config/error-manager.php
        // ====================================================

        // Enhanced Registration with Ecosystem Setup
        'ENHANCED_REGISTRATION_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.enhanced_registration_failed',
            'user_message_key' => 'error-manager::errors.user.enhanced_registration_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'REGISTRATION_USER_CREATION_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.registration_user_creation_failed',
            'user_message_key' => 'error-manager::errors.user.registration_user_creation_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'REGISTRATION_COLLECTION_CREATION_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.registration_collection_creation_failed',
            'user_message_key' => 'error-manager::errors.user.registration_collection_creation_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'REGISTRATION_WALLET_SETUP_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.registration_wallet_setup_failed',
            'user_message_key' => 'error-manager::errors.user.registration_wallet_setup_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'REGISTRATION_ROLE_ASSIGNMENT_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.registration_role_assignment_failed',
            'user_message_key' => 'error-manager::errors.user.registration_role_assignment_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'REGISTRATION_GDPR_CONSENT_FAILED' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.registration_gdpr_consent_failed',
            'user_message_key' => 'error-manager::errors.user.registration_gdpr_consent_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'REGISTRATION_ECOSYSTEM_SETUP_INCOMPLETE' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.registration_ecosystem_setup_incomplete',
            'user_message_key' => 'error-manager::errors.user.registration_ecosystem_setup_incomplete',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'REGISTRATION_VALIDATION_ENHANCED_FAILED' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.registration_validation_enhanced_failed',
            'user_message_key' => 'error-manager::errors.user.registration_validation_enhanced_failed',
            'http_status_code' => 422,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],

        'REGISTRATION_USER_TYPE_INVALID' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.registration_user_type_invalid',
            'user_message_key' => 'error-manager::errors.user.registration_user_type_invalid',
            'http_status_code' => 422,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],

        'REGISTRATION_RATE_LIMIT_EXCEEDED' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.registration_rate_limit_exceeded',
            'user_message_key' => 'error-manager::errors.user.registration_rate_limit_exceeded',
            'http_status_code' => 429,
            'devTeam_email_need' => false,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'REGISTRATION_PAGE_LOAD_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.registration_page_load_errorUndefined variable $user',
            'user_message_key' => 'error-manager::errors.user.registration_page_load_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // ====================================================
        // EGI MODULE ERROR CODES - Add to config/error-manager.php in 'errors' array
        // ====================================================

        // EGI Upload Handler Service-Based Architecture Errors
        'EGI_COLLECTION_SERVICE_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.egi_collection_service_error',
            'user_message_key' => 'error-manager::errors.user.egi_collection_service_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'EGI_WALLET_SERVICE_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.egi_wallet_service_error',
            'user_message_key' => 'error-manager::errors.user.egi_wallet_service_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'EGI_ROLE_SERVICE_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.egi_role_service_error',
            'user_message_key' => 'error-manager::errors.user.egi_role_service_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'EGI_SERVICE_INTEGRATION_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.egi_service_integration_error',
            'user_message_key' => 'error-manager::errors.user.egi_service_integration_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'EGI_ENHANCED_AUTHENTICATION_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.egi_enhanced_authentication_error',
            'user_message_key' => 'error-manager::errors.user.egi_enhanced_authentication_error',
            'http_status_code' => 401,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'EGI_FILE_INPUT_VALIDATION_ERROR' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.egi_file_input_validation_error',
            'user_message_key' => 'error-manager::errors.user.egi_file_input_validation_error',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],

        'EGI_METADATA_VALIDATION_ERROR' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.egi_metadata_validation_error',
            'user_message_key' => 'error-manager::errors.user.egi_metadata_validation_error',
            'http_status_code' => 422,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],

        'EGI_DATA_PREPARATION_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.egi_data_preparation_error',
            'user_message_key' => 'error-manager::errors.user.egi_data_preparation_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'EGI_RECORD_CREATION_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.egi_record_creation_error',
            'user_message_key' => 'error-manager::errors.user.egi_record_creation_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'EGI_FILE_STORAGE_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.egi_file_storage_error',
            'user_message_key' => 'error-manager::errors.user.egi_file_storage_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'EGI_CACHE_INVALIDATION_ERROR' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.egi_cache_invalidation_error',
            'user_message_key' => 'error-manager::errors.user.egi_cache_invalidation_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'log-only',
        ],

        // Collection Service Enhanced Errors
        'COLLECTION_CREATION_ENHANCED_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.collection_creation_enhanced_error',
            'user_message_key' => 'error-manager::errors.user.collection_creation_enhanced_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'COLLECTION_VALIDATION_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.collection_validation_error',
            'user_message_key' => 'error-manager::errors.user.collection_validation_error',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'COLLECTION_LIMIT_EXCEEDED_ERROR' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.collection_limit_exceeded_error',
            'user_message_key' => 'error-manager::errors.user.collection_limit_exceeded_error',
            'http_status_code' => 429,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'COLLECTION_WALLET_ATTACHMENT_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.collection_wallet_attachment_failed',
            'user_message_key' => 'error-manager::errors.user.collection_wallet_attachment_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'log-only',
        ],

        'COLLECTION_ROLE_ASSIGNMENT_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.collection_role_assignment_failed',
            'user_message_key' => 'error-manager::errors.user.collection_role_assignment_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'log-only',
        ],

        'COLLECTION_OWNERSHIP_MISMATCH_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.collection_ownership_mismatch_error',
            'user_message_key' => 'error-manager::errors.user.collection_ownership_mismatch_error',
            'http_status_code' => 403,
            'devTeam_email_need' => false,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'COLLECTION_CURRENT_UPDATE_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.collection_current_update_error',
            'user_message_key' => 'error-manager::errors.user.collection_current_update_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'USER_CURRENT_COLLECTION_UPDATE_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.user_current_collection_update_failed',
            'user_message_key' => 'error-manager::errors.user.user_current_collection_update_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'USER_CURRENT_COLLECTION_VALIDATION_FAILED' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.user_current_collection_validation_failed',
            'user_message_key' => 'error-manager::errors.user.user_current_collection_validation_failed',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // Enhanced Storage Errors
        'EGI_STORAGE_DISK_CONFIG_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.egi_storage_disk_config_error',
            'user_message_key' => 'error-manager::errors.user.egi_storage_disk_config_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'EGI_STORAGE_EMERGENCY_FALLBACK_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.egi_storage_emergency_fallback_failed',
            'user_message_key' => 'error-manager::errors.user.egi_storage_emergency_fallback_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'EGI_TEMP_FILE_READ_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.egi_temp_file_read_error',
            'user_message_key' => 'error-manager::errors.user.egi_temp_file_read_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        // Enhanced Authentication & Session Errors
        'EGI_SESSION_AUTH_INVALID' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.egi_session_auth_invalid',
            'user_message_key' => 'error-manager::errors.user.egi_session_auth_invalid',
            'http_status_code' => 401,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'EGI_WALLET_AUTH_MISMATCH' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.egi_wallet_auth_mismatch',
            'user_message_key' => 'error-manager::errors.user.egi_wallet_auth_mismatch',
            'http_status_code' => 401,
            'devTeam_email_need' => false,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        // ====================================================
        // Errori specifici per la gestione dei like
        // ====================================================

        'AUTH_REQUIRED_FOR_LIKE' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.auth_required_for_like',
            'user_message_key' => 'error-manager::errors.user.auth_required_for_like',
            'http_status_code' => 401,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'RESERVATION_STATUS_AUTH_REQUIRED' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.reservation_status_auth_required',
            'user_message_key' => 'error-manager::errors.user.reservation_status_auth_required',
            'http_status_code' => 401,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'LIKE_TOGGLE_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.like_toggle_failed',
            'user_message_key' => 'error-manager::errors.user.like_toggle_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],


        // ====================================================
        // Errori specifici per la gestione delle prenotazioni
        // ====================================================

        'RESERVATION_EGI_NOT_AVAILABLE' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'errors.dev.reservation_egi_not_available',
            'user_message_key' => 'errors.user.reservation_egi_not_available',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert'
        ],
        'RESERVATION_AMOUNT_TOO_LOW' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'errors.dev.reservation_amount_too_low',
            'user_message_key' => 'errors.user.reservation_amount_too_low',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast'
        ],
        'RESERVATION_RELAUNCH_AMOUNT_TOO_LOW' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'errors.dev.reservation_relaunch_amount_too_low',
            'user_message_key' => 'errors.user.reservation_relaunch_amount_too_low',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert'
        ],
        'RESERVATION_UNAUTHORIZED' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'errors.dev.reservation_unauthorized',
            'user_message_key' => 'errors.user.reservation_unauthorized',
            'http_status_code' => 401,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert'
        ],
        'RESERVATION_CERTIFICATE_GENERATION_FAILED' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'errors.dev.reservation_certificate_generation_failed',
            'user_message_key' => 'errors.user.reservation_certificate_generation_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert'
        ],
        'RESERVATION_CERTIFICATE_NOT_FOUND' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'errors.dev.reservation_certificate_not_found',
            'user_message_key' => 'errors.user.reservation_certificate_not_found',
            'http_status_code' => 404,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div'
        ],
        'RESERVATION_ALREADY_EXISTS' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'errors.dev.reservation_already_exists',
            'user_message_key' => 'errors.user.reservation_already_exists',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast'
        ],
        'RESERVATION_CANCEL_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'errors.dev.reservation_cancel_failed',
            'user_message_key' => 'errors.user.reservation_cancel_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast'
        ],
        'RESERVATION_UNAUTHORIZED_CANCEL' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'errors.dev.reservation_unauthorized_cancel',
            'user_message_key' => 'errors.user.reservation_unauthorized_cancel',
            'http_status_code' => 403,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast'
        ],

        'RESERVATION_NOTIFICATION_SEND_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.reservation_notification_send_error',
            'user_message_key' => 'error-manager::errors.user.reservation_notification_send_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'RESERVATION_NOTIFICATION_RESPONSE_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.reservation_notification_response_error',
            'user_message_key' => 'error-manager::errors.user.reservation_notification_response_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'RESERVATION_NOTIFICATION_ARCHIVE_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.reservation_notification_archive_error',
            'user_message_key' => 'error-manager::errors.user.reservation_notification_archive_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'RESERVATION_NOTIFICATION_BULK_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.reservation_notification_bulk_error',
            'user_message_key' => 'error-manager::errors.user.reservation_notification_bulk_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'CERTIFICATE_GENERATION_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking', // impedisce l'emissione nel flusso corrente
            'dev_message_key'  => 'error-manager::errors.dev.certificate_generation_failed',
            'user_message_key' => 'error-manager::errors.user.certificate_generation_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'json',
        ],

        'CERTIFICATE_GENERATION_FAILED_POST_MINT' => [
            'type' => 'warning',
            'blocking' => 'not', // Mint already completed successfully
            'dev_message_key'  => 'error-manager::errors.dev.certificate_generation_failed_post_mint',
            'user_message_key' => 'error-manager::errors.user.certificate_generation_failed_post_mint',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'log', // Non blocca l'utente, solo log interno
        ],

        'BLOCKCHAIN_CERTIFICATE_GENERATION_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key'  => 'error-manager::errors.dev.blockchain_certificate_generation_failed',
            'user_message_key' => 'error-manager::errors.user.blockchain_certificate_generation_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'json',
        ],

        'CERTIFICATE_ANCHOR_FAILED' => [
            'type' => 'warning',
            'blocking' => 'not', // il certificato resta valido ma in stato "pending_anchor"
            'dev_message_key'  => 'error-manager::errors.dev.certificate_anchor_failed',
            'user_message_key' => 'error-manager::errors.user.certificate_anchor_failed',
            'http_status_code' => 502, // failure provider/indexer esterno
            'devTeam_email_need' => false,
            'notify_slack' => true,
            'msg_to' => 'json',
        ],

        'CERTIFICATE_PAYLOAD_VALIDATION_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key'  => 'error-manager::errors.dev.certificate_payload_validation_error',
            'user_message_key' => 'error-manager::errors.user.certificate_payload_validation_error',
            'http_status_code' => 422,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'json',
        ],

        'CERTIFICATE_VERIFICATION_TAMPERED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking', // blocca download/verifica positiva
            'dev_message_key'  => 'error-manager::errors.dev.certificate_verification_tampered',
            'user_message_key' => 'error-manager::errors.user.certificate_verification_tampered',
            'http_status_code' => 409, // integrità in conflitto
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'json',
        ],

        'CERTIFICATE_ANCHOR_TX_NOT_FOUND' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key'  => 'error-manager::errors.dev.certificate_anchor_tx_not_found',
            'user_message_key' => 'error-manager::errors.user.certificate_anchor_tx_not_found',
            'http_status_code' => 404,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'json',
        ],

        'CERTIFICATE_REVOKE_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key'  => 'error-manager::errors.dev.certificate_revoke_failed',
            'user_message_key' => 'error-manager::errors.user.certificate_revoke_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'json',
        ],

        // ====================================================
        // Currency & Multi-Currency System Errors
        // ====================================================

        'CURRENCY_EXCHANGE_SERVICE_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.currency_exchange_service_failed',
            'user_message_key' => 'error-manager::errors.user.currency_exchange_service_failed',
            'http_status_code' => 503,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
            'recovery_action' => 'retry_currency_service',
        ],

        'CURRENCY_UNSUPPORTED' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.currency_unsupported',
            'user_message_key' => 'error-manager::errors.user.currency_unsupported',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],

        'CURRENCY_RATE_EXPIRED' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.currency_rate_expired',
            'user_message_key' => 'error-manager::errors.user.currency_rate_expired',
            'http_status_code' => 503,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'log-only',
        ],

        'CURRENCY_CONVERSION_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.currency_conversion_failed',
            'user_message_key' => 'error-manager::errors.user.currency_conversion_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'RESERVATION_RELAUNCH_INSUFFICIENT_AMOUNT' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.reservation_relaunch_insufficient_amount',
            'user_message_key' => 'error-manager::errors.user.reservation_relaunch_insufficient_amount',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'EGI_NOT_RESERVABLE' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.egi_not_reservable',
            'user_message_key' => 'error-manager::errors.user.egi_not_reservable',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'USER_CURRENCY_UPDATE_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.user_currency_update_failed',
            'user_message_key' => 'error-manager::errors.user.user_currency_update_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'RESERVATION_STATUS_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'errors.dev.reservation_status_failed',
            'user_message_key' => 'errors.user.reservation_status_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast'
        ],
        'RESERVATION_UNKNOWN_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'errors.dev.reservation_unknown_error',
            'user_message_key' => 'errors.user.reservation_unknown_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert'
        ],

        // --- STATISTICS ERRORS ---
        'STATISTICS_CALCULATION_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.statistics_calculation_failed',
            'user_message_key' => 'error-manager::errors.user.statistics_calculation_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],
        'ICON_NOT_FOUND' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.icon_not_found',
            'user_message_key' => 'error-manager::errors.user.icon_not_found',
            'http_status_code' => 404,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],
        'ICON_RETRIEVAL_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.icon_retrieval_failed',
            'user_message_key' => 'error-manager::errors.user.icon_retrieval_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'fallback',
        ],
        'STATISTICS_CACHE_CLEAR_FAILED' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.statistics_cache_clear_failed',
            'user_message_key' => 'error-manager::errors.user.statistics_cache_clear_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'STATISTICS_SUMMARY_FAILED' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.statistics_summary_failed',
            'user_message_key' => 'error-manager::errors.user.statistics_summary_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'GDPR_CONSENT_REQUIRED' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_consent_required',
            'user_message_key' => 'error-manager::errors.user.gdpr_consent_required',
            'http_status_code' => 403,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],
        'GDPR_CONSENT_UPDATE_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_consent_update_error',
            'user_message_key' => 'error-manager::errors.user.gdpr_consent_update_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],
        'GDPR_CONSENT_SAVE_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_consent_save_error',
            'user_message_key' => 'error-manager::errors.user.gdpr_consent_save_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],
        'GDPR_CONSENT_LOAD_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_consent_load_error',
            'user_message_key' => 'error-manager::errors.user.gdpr_consent_load_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // Cookie Consent specific errors
        'COOKIE_CONSENT_STATUS_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.cookie_consent_status_error',
            'user_message_key' => 'error-manager::errors.user.cookie_consent_status_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'COOKIE_CONSENT_SAVE_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.cookie_consent_save_error',
            'user_message_key' => 'error-manager::errors.user.cookie_consent_save_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        // GDPR Data Export errors
        'GDPR_EXPORT_REQUEST_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_export_request_error',
            'user_message_key' => 'error-manager::errors.user.gdpr_export_request_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],
        'GDPR_EXPORT_LIMIT_REACHED' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_export_limit_reached',
            'user_message_key' => 'error-manager::errors.user.gdpr_export_limit_reached',
            'http_status_code' => 429,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],
        'GDPR_EXPORT_CREATE_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_export_create_error',
            'user_message_key' => 'error-manager::errors.user.gdpr_export_create_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],
        'GDPR_EXPORT_DOWNLOAD_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_export_download_error',
            'user_message_key' => 'error-manager::errors.user.gdpr_export_download_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],
        'GDPR_EXPORT_STATUS_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_export_status_error',
            'user_message_key' => 'error-manager::errors.user.gdpr_export_status_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],
        'GDPR_EXPORT_PROCESSING_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_export_processing_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_export_processing_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        // GDPR Processing Restriction errors
        'GDPR_PROCESSING_RESTRICTED' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_processing_restricted',
            'user_message_key' => 'error-manager::errors.user.gdpr_processing_restricted',
            'http_status_code' => 403,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],
        'GDPR_PROCESSING_LIMIT_VIEW_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_processing_limit_view_error',
            'user_message_key' => 'error-manager::errors.user.gdpr_processing_limit_view_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],
        'GDPR_PROCESSING_RESTRICTION_CREATE_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_processing_restriction_create_error',
            'user_message_key' => 'error-manager::errors.user.gdpr_processing_restriction_create_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],
        'GDPR_PROCESSING_RESTRICTION_REMOVE_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_processing_restriction_remove_error',
            'user_message_key' => 'error-manager::errors.user.gdpr_processing_restriction_remove_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],
        'GDPR_PROCESSING_RESTRICTION_LIMIT_REACHED' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_processing_restriction_limit_reached',
            'user_message_key' => 'error-manager::errors.user.gdpr_processing_restriction_limit_reached',
            'http_status_code' => 429,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // GDPR Account Deletion errors
        'GDPR_DELETION_REQUEST_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_deletion_request_error',
            'user_message_key' => 'error-manager::errors.user.gdpr_deletion_request_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],
        'GDPR_DELETION_CANCELLATION_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_deletion_cancellation_error',
            'user_message_key' => 'error-manager::errors.user.gdpr_deletion_cancellation_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],
        'GDPR_DELETION_PROCESSING_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_deletion_processing_error',
            'user_message_key' => 'error-manager::errors.user.gdpr_deletion_processing_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        // GDPR Breach Report errors
        'GDPR_BREACH_REPORT_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_breach_report_error',
            'user_message_key' => 'error-manager::errors.user.gdpr_breach_report_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],
        'GDPR_BREACH_EVIDENCE_UPLOAD_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_breach_evidence_upload_error',
            'user_message_key' => 'error-manager::errors.user.gdpr_breach_evidence_upload_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],
        // GDPR Activity Log errors
        'GDPR_ACTIVITY_LOG_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_activity_log_error',
            'user_message_key' => 'error-manager::errors.user.gdpr_activity_log_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],
        // GDPR Security errors
        'GDPR_ENHANCED_SECURITY_REQUIRED' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_enhanced_security_required',
            'user_message_key' => 'error-manager::errors.user.gdpr_enhanced_security_required',
            'http_status_code' => 403,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],
        'GDPR_CRITICAL_SECURITY_REQUIRED' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_critical_security_required',
            'user_message_key' => 'error-manager::errors.user.gdpr_critical_security_required',
            'http_status_code' => 403,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // ====================================================
        // GDPR Consent Management Errors
        // ====================================================
        'GDPR_CONSENT_PAGE_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_consent_page_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_consent_page_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'GDPR_CONSENT_PREFERENCES_PAGE_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_consent_preferences_page_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_consent_preferences_page_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'GDPR_CONSENT_UPDATE_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_consent_update_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_consent_update_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'GDPR_SERVICE_UNAVAILABLE' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_service_unavailable',
            'user_message_key' => 'error-manager::errors.user.gdpr_service_unavailable',
            'http_status_code' => 503,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'GDPR_CONSENT_HISTORY_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_consent_history_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_consent_history_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // ====================================================
        // GDPR Data Export Errors
        // ====================================================
        'GDPR_EXPORT_PAGE_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_export_page_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_export_page_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'GDPR_EXPORT_GENERATION_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_export_generation_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_export_generation_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'GDPR_EXPORT_NOT_FOUND' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_export_not_found',
            'user_message_key' => 'error-manager::errors.user.gdpr_export_not_found',
            'http_status_code' => 404,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'GDPR_EXPORT_HISTORY_FAILED' => [
            'type' => 'error',
            'blocking' => 'not', // Non-blocking since we return empty collection
            'dev_message_key' => 'error-manager::errors.dev.gdpr_export_history_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_export_history_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'log-only', // User gets empty data, error is logged
        ],

        'GDPR_EXPORT_NOT_READY' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_export_not_ready',
            'user_message_key' => 'error-manager::errors.user.gdpr_export_not_ready',
            'http_status_code' => 422,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'GDPR_EXPORT_EXPIRED' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_export_expired',
            'user_message_key' => 'error-manager::errors.user.gdpr_export_expired',
            'http_status_code' => 410, // Gone
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'GDPR_EXPORT_FILE_NOT_FOUND' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_export_file_not_found',
            'user_message_key' => 'error-manager::errors.user.gdpr_export_file_not_found',
            'http_status_code' => 404,
            'devTeam_email_need' => true, // File should exist if status is completed
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'GDPR_EXPORT_CLEANUP_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_export_cleanup_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_export_cleanup_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'log-only',
        ],

        'GDPR_EXPORT_INVALID_FORMAT' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_export_invalid_format',
            'user_message_key' => 'error-manager::errors.user.gdpr_export_invalid_format',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],

        'GDPR_EXPORT_INVALID_CATEGORIES' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_export_invalid_categories',
            'user_message_key' => 'error-manager::errors.user.gdpr_export_invalid_categories',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],

        'GDPR_EXPORT_DOWNLOAD_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_export_download_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_export_download_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        // ====================================================
        // GDPR Personal Data Management Errors
        // ====================================================
        'GDPR_EDIT_DATA_PAGE_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_edit_data_page_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_edit_data_page_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'GDPR_PERSONAL_DATA_UPDATE_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_personal_data_update_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_personal_data_update_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'GDPR_RECTIFICATION_REQUEST_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_rectification_request_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_rectification_request_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        // ====================================================
        // GDPR Processing Limitation Errors
        // ====================================================
        'GDPR_PROCESSING_LIMITS_UPDATE_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_processing_limits_update_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_processing_limits_update_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        // ====================================================
        // GDPR Account Deletion Errors
        // ====================================================
        'GDPR_DELETE_ACCOUNT_PAGE_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_delete_account_page_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_delete_account_page_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'GDPR_DELETION_REQUEST_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_deletion_request_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_deletion_request_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'GDPR_ACCOUNT_DELETION_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_account_deletion_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_account_deletion_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        // ====================================================
        // GDPR Activity Log Errors
        // ====================================================
        'GDPR_ACTIVITY_LOG_PAGE_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_activity_log_page_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_activity_log_page_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'GDPR_ACTIVITY_LOG_EXPORT_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_activity_log_export_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_activity_log_export_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // ====================================================
        // GDPR Breach Reporting Errors
        // ====================================================
        'GDPR_BREACH_REPORT_PAGE_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_breach_report_page_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_breach_report_page_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'GDPR_BREACH_REPORT_SUBMISSION_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_breach_report_submission_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_breach_report_submission_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'GDPR_BREACH_REPORT_ACCESS_DENIED' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_breach_report_access_denied',
            'user_message_key' => 'error-manager::errors.user.gdpr_breach_report_access_denied',
            'http_status_code' => 403,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'GDPR_BREACH_REPORT_STATUS_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_breach_report_status_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_breach_report_status_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // ====================================================
        // GDPR Privacy Policy & Transparency Errors
        // ====================================================
        'GDPR_PRIVACY_POLICY_PAGE_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_privacy_policy_page_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_privacy_policy_page_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'GDPR_PRIVACY_POLICY_CHANGELOG_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_privacy_policy_changelog_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_privacy_policy_changelog_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'GDPR_DATA_PROCESSING_INFO_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_data_processing_info_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_data_processing_info_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // ====================================================
        // GDPR DPO Contact & Support Errors
        // ====================================================
        'GDPR_DPO_CONTACT_PAGE_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_dpo_contact_page_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_dpo_contact_page_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'GDPR_DPO_MESSAGE_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_dpo_message_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_dpo_message_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // ====================================================
        // GDPR API Errors
        // ====================================================
        'GDPR_API_CONSENT_STATUS_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_api_consent_status_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_api_consent_status_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'json',
        ],

        'GDPR_API_PROCESSING_LIMITS_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_api_processing_limits_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_api_processing_limits_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'json',
        ],

        'GDPR_API_EXPORT_STATUS_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_api_export_status_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_api_export_status_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'json',
        ],

        // ====================================================
        // GDPR Legacy Method Errors
        // ====================================================
        'GDPR_LEGACY_DATA_DOWNLOAD_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_legacy_data_download_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_legacy_data_download_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'GDPR_NOTIFICATION_DISPATCH_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_notification_dispatch_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_notification_dispatch_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'GDPR_NOTIFICATION_PERSISTENCE_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_notification_persistence_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_notification_persistence_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'PERMISSION_BASED_REGISTRATION_FAILED' => [
            'type' => 'server_error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.permission_based_registration_failed',
            'user_message_key' => 'error-manager::errors.user.permission_based_registration_failed_user',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'notifiable' => true,
            'notifications' => ['log', 'db'],
            'msg_to' => 'toast',
            'log_level' => 'error',
            'category' => 'registration',
            'sensitive_keys' => ['password', 'password_confirmation', 'registration_ip', 'user_agent'],
        ],

        'ALGORAND_WALLET_GENERATION_FAILED' => [
            'type' => 'server_error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.algorand_wallet_generation_failed',
            'user_message_key' => 'error-manager::errors.user.algorand_wallet_generation_failed_user',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'notifiable' => true,
            'notifications' => ['log', 'db'],
            'msg_to' => 'toast',
            'log_level' => 'error',
            'category' => 'registration',
        ],

        'ECOSYSTEM_SETUP_FAILED' => [
            'type' => 'server_error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.ecosystem_setup_failed',
            'user_message_key' => 'error-manager::errors.user.ecosystem_setup_failed_user',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'notifiable' => true,
            'notifications' => ['log', 'db'],
            'msg_to' => 'toast',
            'log_level' => 'error',
            'category' => 'registration',
            'sensitive_keys' => ['user_id', 'collection_id'],
        ],

        'USER_DOMAIN_INITIALIZATION_FAILED' => [
            'type' => 'server_error',
            'blocking' => 'blocking', // Non blocking - l'utente può completare i domini dopo
            'dev_message_key' => 'error-manager::errors.dev.user_domain_initialization_failed',
            'user_message_key' => 'error-manager::errors.user.user_domain_initialization_failed_user',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'notifiable' => true,
            'notifications' => ['log'],
            'msg_to' => 'toast',
            'log_level' => 'warning',
            'category' => 'registration',
        ],

        'GDPR_VIOLATION_ATTEMPT' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_violation_attempt',
            'user_message_key' => 'error-manager::errors.user.generic_internal_error', // Non dare dettagli specifici all'utente
            'http_status_code' => 500, // Errore di configurazione/logica interna
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'GDPR_NOTIFICATION_SEND_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_notification_send_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_notification_send_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'GDPR_VIOLATION_ATTEMPT' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_violation_attempt',
            'user_message_key' => 'error-manager::errors.user.gdpr_violation_attempt',
            'http_status_code' => 403,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'ROLE_ASSIGNMENT_FAILED' => [
            'type' => 'server_error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.role_assignment_failed',
            'user_message_key' => 'error-manager::errors.user.role_assignment_failed_user',
            'http_status_code' => 403,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'notifiable' => true,
            'notifications' => ['log', 'db'],
            'msg_to' => 'toast',
            'log_level' => 'error',
            'category' => 'registration',
        ],

        'REGISTRATION_PAGE_LOAD_ERROR' => [
            'type' => 'server_error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.registration_page_load_error',
            'user_message_key' => 'error-manager::errors.user.registration_page_load_error_user',
            'http_status_code' => 403,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'notifiable' => true,
            'notifications' => ['log'],
            'msg_to' => 'toast',
            'log_level' => 'warning',
            'category' => 'ui',
        ],

        'PERSONAL_DATA_VIEW_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.personal_data_view_failed',
            'user_message_key' => 'error-manager::errors.user.personal_data_view_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'PERSONAL_DATA_UPDATE_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.personal_data_update_failed',
            'user_message_key' => 'error-manager::errors.user.personal_data_update_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'PERSONAL_DATA_EXPORT_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.personal_data_export_failed',
            'user_message_key' => 'error-manager::errors.user.personal_data_export_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'PERSONAL_DATA_DELETION_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.personal_data_deletion_failed',
            'user_message_key' => 'error-manager::errors.user.personal_data_deletion_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'GDPR_EXPORT_RATE_LIMIT' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_export_rate_limit',
            'user_message_key' => 'error-manager::errors.user.gdpr_export_rate_limit',
            'http_status_code' => 429,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        // ====================================================
        // LEGAL SYSTEM ERRORS
        // ====================================================
        'LEGAL_CONTENT_LOAD_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.legal_content_load_failed',
            'user_message_key' => 'error-manager::errors.user.legal_content_load_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'TERMS_ACCEPTANCE_CHECK_FAILED' => [
            'type' => 'error',
            'blocking' => 'not', // Non blocca la richiesta, il metodo ritorna 'false' come default
            'dev_message_key' => 'error-manager::errors.dev.terms_acceptance_check_failed',
            'user_message_key' => 'error-manager::errors.user.generic_error', // Non mostriamo un errore specifico all'utente
            'http_status_code' => 200, // La pagina viene caricata, l'errore è di sottofondo
            'devTeam_email_need' => true, // Notifica il team perché è un errore di compliance importante
            'notify_slack' => true,
            'msg_to' => 'toast', // Un avviso non invadente, se necessario
        ],

        // ====================================================
        // Biography System Error Codes
        // ====================================================
        'BIOGRAPHY_INDEX_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.biography_index_failed',
            'user_message_key' => 'error-manager::errors.user.biography_index_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'BIOGRAPHY_VALIDATION_FAILED' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.biography_validation_failed',
            'user_message_key' => 'error-manager::errors.user.biography_validation_failed',
            'http_status_code' => 422,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],

        'BIOGRAPHY_CREATE_FAILED' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.biography_create_failed',
            'user_message_key' => 'error-manager::errors.user.biography_create_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'BIOGRAPHY_ACCESS_DENIED' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.biography_access_denied',
            'user_message_key' => 'error-manager::errors.user.biography_access_denied',
            'http_status_code' => 403,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'BIOGRAPHY_SHOW_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.biography_show_failed',
            'user_message_key' => 'error-manager::errors.user.biography_show_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'BIOGRAPHY_UPDATE_DENIED' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.biography_update_denied',
            'user_message_key' => 'error-manager::errors.user.biography_update_denied',
            'http_status_code' => 403,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'BIOGRAPHY_UPDATE_FAILED' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.biography_update_failed',
            'user_message_key' => 'error-manager::errors.user.biography_update_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'BIOGRAPHY_TYPE_CHANGE_INVALID' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.biography_type_change_invalid',
            'user_message_key' => 'error-manager::errors.user.biography_type_change_invalid',
            'http_status_code' => 422,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],

        'BIOGRAPHY_DELETE_DENIED' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.biography_delete_denied',
            'user_message_key' => 'error-manager::errors.user.biography_delete_denied',
            'http_status_code' => 403,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'BIOGRAPHY_DELETE_FAILED' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.biography_delete_failed',
            'user_message_key' => 'error-manager::errors.user.biography_delete_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        // Additional Biography Chapter Error Codes (for future BiographyChapterController)
        'BIOGRAPHY_CHAPTER_VALIDATION_FAILED' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.biography_chapter_validation_failed',
            'user_message_key' => 'error-manager::errors.user.biography_chapter_validation_failed',
            'http_status_code' => 422,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],

        'BIOGRAPHY_CHAPTER_CREATE_FAILED' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.biography_chapter_create_failed',
            'user_message_key' => 'error-manager::errors.user.biography_chapter_create_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'BIOGRAPHY_CHAPTER_ACCESS_DENIED' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.biography_chapter_access_denied',
            'user_message_key' => 'error-manager::errors.user.biography_chapter_access_denied',
            'http_status_code' => 403,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'BIOGRAPHY_CHAPTER_UPDATE_FAILED' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.biography_chapter_update_failed',
            'user_message_key' => 'error-manager::errors.user.biography_chapter_update_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'BIOGRAPHY_CHAPTER_DELETE_FAILED' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.biography_chapter_delete_failed',
            'user_message_key' => 'error-manager::errors.user.biography_chapter_delete_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'BIOGRAPHY_CHAPTER_REORDER_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.biography_chapter_reorder_failed',
            'user_message_key' => 'error-manager::errors.user.biography_chapter_reorder_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // Biography Media Error Codes
        'BIOGRAPHY_MEDIA_UPLOAD_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.biography_media_upload_failed',
            'user_message_key' => 'error-manager::errors.user.biography_media_upload_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'BIOGRAPHY_MEDIA_VALIDATION_FAILED' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.biography_media_validation_failed',
            'user_message_key' => 'error-manager::errors.user.biography_media_validation_failed',
            'http_status_code' => 422,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],

        'BIOGRAPHY_MEDIA_DELETE_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.biography_media_delete_failed',
            'user_message_key' => 'error-manager::errors.user.biography_media_delete_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // Biography Chapter System Error Codes
        'BIOGRAPHY_CHAPTER_INDEX_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.biography_chapter_index_failed',
            'user_message_key' => 'error-manager::errors.user.biography_chapter_index_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'BIOGRAPHY_CHAPTER_SHOW_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.biography_chapter_show_failed',
            'user_message_key' => 'error-manager::errors.user.biography_chapter_show_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // ====================================================
        // Multi-Currency System Error Codes
        // ====================================================

        'CURRENCY_EXCHANGE_SERVICE_UNAVAILABLE' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.currency_exchange_service_unavailable',
            'user_message_key' => 'error-manager::errors.user.currency_exchange_service_unavailable',
            'http_status_code' => 503,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'log-only',
        ],

        'CURRENCY_RATE_CACHE_ERROR' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.currency_rate_cache_error',
            'user_message_key' => null,
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'log-only',
        ],

        'CURRENCY_INVALID_RATE_DATA' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.currency_invalid_rate_data',
            'user_message_key' => 'error-manager::errors.user.currency_invalid_rate_data',
            'http_status_code' => 502,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'json',
        ],

        'CURRENCY_CONVERSION_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.currency_conversion_error',
            'user_message_key' => 'error-manager::errors.user.currency_conversion_error',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'json',
        ],

        'CURRENCY_UNSUPPORTED_CURRENCY' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.currency_unsupported_currency',
            'user_message_key' => 'error-manager::errors.user.currency_unsupported_currency',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'json',
        ],

        'USER_PREFERENCE_UPDATE_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.user_preference_update_failed',
            'user_message_key' => 'error-manager::errors.user.user_preference_update_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'json',
        ],

        'CURRENCY_CONVERSION_VALIDATION_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.currency_conversion_validation_error',
            'user_message_key' => 'error-manager::errors.user.currency_conversion_validation_error',
            'http_status_code' => 422,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'json',
        ],

        'USER_PREFERENCE_FETCH_ERROR' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.user_preference_fetch_error',
            'user_message_key' => 'error-manager::errors.user.user_preference_fetch_error',
            'http_status_code' => 404,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'json',
        ],

        'CURRENCY_PREFERENCE_VALIDATION_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.currency_preference_validation_error',
            'user_message_key' => 'error-manager::errors.user.currency_preference_validation_error',
            'http_status_code' => 422,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'json',
        ],

        // Payment Distribution Service Error Codes
        'PAYMENT_DISTRIBUTION_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.payment_distribution_error',
            'user_message_key' => 'error-manager::errors.user.payment_distribution_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'json',
        ],

        'RESERVATION_NOT_ACTIVE' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.reservation_not_active',
            'user_message_key' => 'error-manager::errors.user.reservation_not_active',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'json',
        ],

        'PAYMENT_NOT_EXECUTED' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.payment_not_executed',
            'user_message_key' => 'error-manager::errors.user.payment_not_executed',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'json',
        ],

        'INVALID_AMOUNT' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.invalid_amount',
            'user_message_key' => 'error-manager::errors.user.invalid_amount',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'json',
        ],

        'COLLECTION_NOT_FOUND' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.collection_not_found',
            'user_message_key' => 'error-manager::errors.user.collection_not_found',
            'http_status_code' => 404,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'json',
        ],

        'DISTRIBUTIONS_ALREADY_EXIST' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.distributions_already_exist',
            'user_message_key' => 'error-manager::errors.user.distributions_already_exist',
            'http_status_code' => 409,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'json',
        ],

        'NO_WALLETS_FOUND' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.no_wallets_found',
            'user_message_key' => 'error-manager::errors.user.no_wallets_found',
            'http_status_code' => 404,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'json',
        ],

        'INVALID_MINT_PERCENTAGES' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.invalid_mint_percentages',
            'user_message_key' => 'error-manager::errors.user.invalid_mint_percentages',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'json',
        ],

        'USER_ACTIVITY_LOGGING_FAILED' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.user_activity_logging_failed',
            'user_message_key' => 'error-manager::errors.user.user_activity_logging_failed',
            'http_status_code' => 200,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'none',
        ],

        // ====================================================
        // INVITATION SYSTEM
        // ====================================================
        'INVITATION_CREATION_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.invitation_creation_error',
            'user_message_key' => 'error-manager::errors.user.invitation_creation_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'INVITATION_USER_NOT_FOUND' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.invitation_user_not_found',
            'user_message_key' => 'error-manager::errors.user.invitation_user_not_found',
            'http_status_code' => 404,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // ====================================================
        // WALLET NOTIFICATION ERRORS
        // ====================================================
        'WALLET_NOTIFICATION_INVALID_ACTION' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.wallet_notification_invalid_action',
            'user_message_key' => 'error-manager::errors.user.wallet_notification_invalid_action',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'json',
        ],

        'WALLET_NOTIFICATION_NOT_FOUND' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.wallet_notification_not_found',
            'user_message_key' => 'error-manager::errors.user.wallet_notification_not_found',
            'http_status_code' => 404,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'json',
        ],

        'WALLET_NOTIFICATION_PROCESSING_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.wallet_notification_processing_error',
            'user_message_key' => 'error-manager::errors.user.wallet_notification_processing_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'json',
        ],

        'WALLET_NOTIFICATION_UNAUTHORIZED' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.wallet_notification_unauthorized',
            'user_message_key' => 'error-manager::errors.user.wallet_notification_unauthorized',
            'http_status_code' => 403,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'json',
        ],

        'WALLET_NOTIFICATION_FETCH_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.wallet_notification_fetch_error',
            'user_message_key' => 'error-manager::errors.user.wallet_notification_fetch_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // Invitation Response Error Codes
        'INVITATION_RESPONSE_INVALID_ACTION' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.invitation_response_invalid_action',
            'user_message_key' => 'error-manager::errors.user.invitation_response_invalid_action',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'json',
        ],

        'INVITATION_RESPONSE_SYSTEM_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.invitation_response_system_error',
            'user_message_key' => 'error-manager::errors.user.invitation_response_system_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'json',
        ],

        'INVITATION_ARCHIVE_NOT_FOUND' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.invitation_archive_not_found',
            'user_message_key' => 'error-manager::errors.user.invitation_archive_not_found',
            'http_status_code' => 404,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'json',
        ],

        'INVITATION_ARCHIVE_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.invitation_archive_error',
            'user_message_key' => 'error-manager::errors.user.invitation_archive_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'json',
        ],

        // ====================================================
        // COA SYSTEM ERRORS - Services Layer
        // ====================================================

        // SerialGenerator Service Errors
        'COA_SERIAL_GENERATION_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_serial_generation_error',
            'user_message_key' => 'error-manager::errors.user.coa_serial_generation_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'COA_SERIAL_VALIDATION_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_serial_validation_error',
            'user_message_key' => 'error-manager::errors.user.coa_serial_validation_error',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'COA_SERIAL_UNIQUENESS_CHECK_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_serial_uniqueness_check_error',
            'user_message_key' => 'error-manager::errors.user.coa_serial_uniqueness_check_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'COA_SERIAL_PARSE_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_serial_parse_error',
            'user_message_key' => 'error-manager::errors.user.coa_serial_parse_error',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'COA_SERIAL_STATISTICS_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_serial_statistics_error',
            'user_message_key' => 'error-manager::errors.user.coa_serial_statistics_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'COA_SERIAL_YEARS_QUERY_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_serial_years_query_error',
            'user_message_key' => 'error-manager::errors.user.coa_serial_years_query_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // HashingService Errors
        'COA_HASH_GENERATION_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_hash_generation_error',
            'user_message_key' => 'error-manager::errors.user.coa_hash_generation_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'COA_TRAITS_HASH_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_traits_hash_error',
            'user_message_key' => 'error-manager::errors.user.coa_traits_hash_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'COA_HASH_VERIFICATION_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_hash_verification_error',
            'user_message_key' => 'error-manager::errors.user.coa_hash_verification_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'COA_MULTI_HASH_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_multi_hash_error',
            'user_message_key' => 'error-manager::errors.user.coa_multi_hash_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'COA_MULTI_HASH_VERIFICATION_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_multi_hash_verification_error',
            'user_message_key' => 'error-manager::errors.user.coa_multi_hash_verification_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'COA_FILE_HASH_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_file_hash_error',
            'user_message_key' => 'error-manager::errors.user.coa_file_hash_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'COA_HASH_FORMAT_VALIDATION_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_hash_format_validation_error',
            'user_message_key' => 'error-manager::errors.user.coa_hash_format_validation_error',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // SignatureService Errors
        'COA_DIGITAL_SIGNATURE_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_digital_signature_error',
            'user_message_key' => 'error-manager::errors.user.coa_digital_signature_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'COA_PHYSICAL_SIGNATURE_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_physical_signature_error',
            'user_message_key' => 'error-manager::errors.user.coa_physical_signature_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'COA_DIGITAL_SIGNATURE_VERIFY_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_digital_signature_verify_error',
            'user_message_key' => 'error-manager::errors.user.coa_digital_signature_verify_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'COA_GET_SIGNATURES_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_get_signatures_error',
            'user_message_key' => 'error-manager::errors.user.coa_get_signatures_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'COA_AUTHOR_SIGN_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.coa_author_sign_error',
            'user_message_key' => 'error-manager::errors_2.user.coa_author_sign_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        // TraitsSnapshotService Errors
        'COA_TRAITS_VERSION_CREATE_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_traits_version_create_error',
            'user_message_key' => 'error-manager::errors.user.coa_traits_version_create_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'COA_SNAPSHOT_CREATE_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_snapshot_create_error',
            'user_message_key' => 'error-manager::errors.user.coa_snapshot_create_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'COA_SNAPSHOT_INTEGRITY_CHECK_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_snapshot_integrity_check_error',
            'user_message_key' => 'error-manager::errors.user.coa_snapshot_integrity_check_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        // CoaIssueService Errors
        'COA_ISSUE_CERTIFICATE_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_issue_certificate_error',
            'user_message_key' => 'error-manager::errors.user.coa_issue_certificate_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'COA_REISSUE_CERTIFICATE_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_reissue_certificate_error',
            'user_message_key' => 'error-manager::errors.user.coa_reissue_certificate_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'COA_ISSUE_VALIDATION_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_issue_validation_error',
            'user_message_key' => 'error-manager::errors.user.coa_issue_validation_error',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // CoaRevocationService Errors
        'COA_REVOKE_CERTIFICATE_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_revoke_certificate_error',
            'user_message_key' => 'error-manager::errors.user.coa_revoke_certificate_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'COA_REVOKE_VALIDATION_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_revoke_validation_error',
            'user_message_key' => 'error-manager::errors.user.coa_revoke_validation_error',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'COA_REVOKE_HISTORY_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_revoke_history_error',
            'user_message_key' => 'error-manager::errors.user.coa_revoke_history_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'COA_BATCH_REVOKE_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_batch_revoke_error',
            'user_message_key' => 'error-manager::errors.user.coa_batch_revoke_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        // AnnexService Errors
        'COA_ANNEX_CREATE_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_annex_create_error',
            'user_message_key' => 'error-manager::errors.user.coa_annex_create_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'COA_ANNEX_UPDATE_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_annex_update_error',
            'user_message_key' => 'error-manager::errors.user.coa_annex_update_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'COA_GET_ANNEXES_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_get_annexes_error',
            'user_message_key' => 'error-manager::errors.user.coa_get_annexes_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // BundleService Errors
        'COA_BUNDLE_CREATE_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_bundle_create_error',
            'user_message_key' => 'error-manager::errors.user.coa_bundle_create_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'COA_BUNDLE_ESTIMATE_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_bundle_estimate_error',
            'user_message_key' => 'error-manager::errors.user.coa_bundle_estimate_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // CoaAddendumService Errors
        'COA_ADDENDUM_CREATE_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_addendum_create_error',
            'user_message_key' => 'error-manager::errors.user.coa_addendum_create_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'COA_POLICY_CREATE_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_policy_create_error',
            'user_message_key' => 'error-manager::errors.user.coa_policy_create_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'COA_GET_ADDENDUMS_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_get_addendums_error',
            'user_message_key' => 'error-manager::errors.user.coa_get_addendums_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'COA_GET_POLICIES_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_get_policies_error',
            'user_message_key' => 'error-manager::errors.user.coa_get_policies_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'COA_ADDENDUM_INTEGRITY_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_addendum_integrity_error',
            'user_message_key' => 'error-manager::errors.user.coa_addendum_integrity_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'COA_POLICY_INTEGRITY_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_policy_integrity_error',
            'user_message_key' => 'error-manager::errors.user.coa_policy_integrity_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        // VerifyPageService Errors
        'VERIFY_DATA_GENERATION_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.verify_data_generation_error',
            'user_message_key' => 'error-manager::errors.user.verify_data_generation_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'VERIFY_PAGE_GENERATION_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.verify_page_generation_error',
            'user_message_key' => 'error-manager::errors.user.verify_page_generation_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'VERIFY_PUBLIC_ANNEXES_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.verify_public_annexes_error',
            'user_message_key' => 'error-manager::errors.user.verify_public_annexes_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'VERIFY_QR_GENERATION_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.verify_qr_generation_error',
            'user_message_key' => 'error-manager::errors.user.verify_qr_generation_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // ====================================================
        // COA SYSTEM ERRORS - Base Controllers
        // ====================================================

        // CoaController Errors
        'COA_INDEX_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_index_error',
            'user_message_key' => 'error-manager::errors.user.coa_index_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'COA_SHOW_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_show_error',
            'user_message_key' => 'error-manager::errors.user.coa_show_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'COA_ISSUE_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_issue_error',
            'user_message_key' => 'error-manager::errors.user.coa_issue_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'COA_REISSUE_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_reissue_error',
            'user_message_key' => 'error-manager::errors.user.coa_reissue_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'COA_REVOKE_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_revoke_error',
            'user_message_key' => 'error-manager::errors.user.coa_revoke_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'COA_BUNDLE_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_bundle_error',
            'user_message_key' => 'error-manager::errors.user.coa_bundle_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'COA_STATISTICS_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_statistics_error',
            'user_message_key' => 'error-manager::errors.user.coa_statistics_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // VerifyController Errors
        'VERIFY_CERTIFICATE_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.verify_certificate_error',
            'user_message_key' => 'error-manager::errors.user.verify_certificate_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'VERIFY_PAGE_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.verify_page_error',
            'user_message_key' => 'error-manager::errors.user.verify_page_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'VERIFY_HASH_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.verify_hash_error',
            'user_message_key' => 'error-manager::errors.user.verify_hash_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'VERIFY_ANNEXES_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.verify_annexes_error',
            'user_message_key' => 'error-manager::errors.user.verify_annexes_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'VERIFY_QR_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.verify_qr_error',
            'user_message_key' => 'error-manager::errors.user.verify_qr_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'VERIFY_BATCH_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.verify_batch_error',
            'user_message_key' => 'error-manager::errors.user.verify_batch_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'VERIFY_STATS_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.verify_stats_error',
            'user_message_key' => 'error-manager::errors.user.verify_stats_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // AnnexController Errors
        'ANNEX_INDEX_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.annex_index_error',
            'user_message_key' => 'error-manager::errors.user.annex_index_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'ANNEX_SHOW_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.annex_show_error',
            'user_message_key' => 'error-manager::errors.user.annex_show_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'ANNEX_CREATE_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.annex_create_error',
            'user_message_key' => 'error-manager::errors.user.annex_create_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'ANNEX_UPDATE_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.annex_update_error',
            'user_message_key' => 'error-manager::errors.user.annex_update_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'ANNEX_HISTORY_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.annex_history_error',
            'user_message_key' => 'error-manager::errors.user.annex_history_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'ANNEX_INTEGRITY_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.annex_integrity_error',
            'user_message_key' => 'error-manager::errors.user.annex_integrity_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'ANNEX_TYPES_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.annex_types_error',
            'user_message_key' => 'error-manager::errors.user.annex_types_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // ====================================================
        // COA PRO SYSTEM ERRORS - Addendum Controller
        // ====================================================

        'ADDENDUM_LIST_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.addendum_list_error',
            'user_message_key' => 'error-manager::errors.user.addendum_list_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'ADDENDUM_NOT_FOUND' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.addendum_not_found',
            'user_message_key' => 'error-manager::errors.user.addendum_not_found',
            'http_status_code' => 404,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'ADDENDUM_DETAIL_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.addendum_detail_error',
            'user_message_key' => 'error-manager::errors.user.addendum_detail_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'ADDENDUM_CREATION_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.addendum_creation_error',
            'user_message_key' => 'error-manager::errors.user.addendum_creation_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'ADDENDUM_NOT_EDITABLE' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.addendum_not_editable',
            'user_message_key' => 'error-manager::errors.user.addendum_not_editable',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'ADDENDUM_UPDATE_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.addendum_update_error',
            'user_message_key' => 'error-manager::errors.user.addendum_update_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'ADDENDUM_NOT_REVISABLE' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.addendum_not_revisable',
            'user_message_key' => 'error-manager::errors.user.addendum_not_revisable',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'ADDENDUM_REVISION_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.addendum_revision_error',
            'user_message_key' => 'error-manager::errors.user.addendum_revision_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'ADDENDUM_NOT_PUBLISHABLE' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.addendum_not_publishable',
            'user_message_key' => 'error-manager::errors.user.addendum_not_publishable',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'ADDENDUM_PUBLISH_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.addendum_publish_error',
            'user_message_key' => 'error-manager::errors.user.addendum_publish_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'ADDENDUM_HISTORY_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.addendum_history_error',
            'user_message_key' => 'error-manager::errors.user.addendum_history_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'ADDENDUM_ARCHIVE_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.addendum_archive_error',
            'user_message_key' => 'error-manager::errors.user.addendum_archive_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'RARITY_POLICIES_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.rarity_policies_error',
            'user_message_key' => 'error-manager::errors.user.rarity_policies_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // ====================================================
        // COA PDF Generation & Download Errors
        // ====================================================
        'COA_PDF_CORE_GENERATION_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_pdf_core_generation_error',
            'user_message_key' => 'error-manager::errors.user.coa_pdf_core_generation_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],
        'COA_PDF_BUNDLE_GENERATION_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_pdf_bundle_generation_error',
            'user_message_key' => 'error-manager::errors.user.coa_pdf_bundle_generation_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],
        'COA_PDF_URL_GENERATION_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_pdf_url_generation_error',
            'user_message_key' => 'error-manager::errors.user.coa_pdf_url_generation_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],
        'COA_PDF_INTEGRITY_CHECK_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_pdf_integrity_check_error',
            'user_message_key' => 'error-manager::errors.user.coa_pdf_integrity_check_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],
        'COA_PDF_INTEGRITY_FAILURE' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_pdf_integrity_failure',
            'user_message_key' => 'error-manager::errors.user.coa_pdf_integrity_failure',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        // Controller-level legacy codes (present in repo)
        'COA_PDF_GENERATION_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_pdf_generation_error',
            'user_message_key' => 'error-manager::errors.user.coa_pdf_generation_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],
        'COA_PDF_DOWNLOAD_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_pdf_download_error',
            'user_message_key' => 'error-manager::errors.user.coa_pdf_download_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],
        'COA_PDF_CHECK_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_pdf_check_error',
            'user_message_key' => 'error-manager::errors.user.coa_pdf_check_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],

        // Newly instrumented UEM codes from CoaPdfService
        'COA_PDF_METADATA_PERSIST_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_pdf_metadata_persist_error',
            'user_message_key' => 'error-manager::errors.user.coa_pdf_metadata_persist_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],
        'COA_PDF_SIGNATURE_PIPELINE_ERROR' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_pdf_signature_pipeline_error',
            'user_message_key' => 'error-manager::errors.user.coa_pdf_signature_pipeline_error',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],
        'COA_PDF_QR_GENERATION_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_pdf_qr_generation_error',
            'user_message_key' => 'error-manager::errors.user.coa_pdf_qr_generation_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],
        'COA_PDF_QR_METADATA_PERSIST_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_pdf_qr_metadata_persist_error',
            'user_message_key' => 'error-manager::errors.user.coa_pdf_qr_metadata_persist_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],
        'COA_PDF_SIGNATURE_METADATA_PARSE_ERROR' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_pdf_signature_metadata_parse_error',
            'user_message_key' => 'error-manager::errors.user.coa_pdf_signature_metadata_parse_error',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'div',
        ],
        'COA_PDF_VALIDITY_AUTHOR_DIRECT_ERROR' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_pdf_validity_author_direct_error',
            'user_message_key' => 'error-manager::errors.user.coa_pdf_validity_author_direct_error',
            'http_status_code' => 200,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'log-only',
        ],
        'COA_PDF_VALIDITY_AUTHOR_TRAITS_ERROR' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_pdf_validity_author_traits_error',
            'user_message_key' => 'error-manager::errors.user.coa_pdf_validity_author_traits_error',
            'http_status_code' => 200,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'log-only',
        ],
        'COA_PDF_VALIDITY_CREATION_DATE_DIRECT_ERROR' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_pdf_validity_creation_date_direct_error',
            'user_message_key' => 'error-manager::errors.user.coa_pdf_validity_creation_date_direct_error',
            'http_status_code' => 200,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'log-only',
        ],
        'COA_PDF_VALIDITY_YEAR_EXTRACT_ERROR' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_pdf_validity_year_extract_error',
            'user_message_key' => 'error-manager::errors.user.coa_pdf_validity_year_extract_error',
            'http_status_code' => 200,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'log-only',
        ],
        'COA_PDF_VALIDITY_ISSUE_PLACE_DIRECT_ERROR' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_pdf_validity_issue_place_direct_error',
            'user_message_key' => 'error-manager::errors.user.coa_pdf_validity_issue_place_direct_error',
            'http_status_code' => 200,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'log-only',
        ],
        'COA_PDF_VALIDITY_ISSUER_LOCATION_CHECK_ERROR' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_pdf_validity_issuer_location_check_error',
            'user_message_key' => 'error-manager::errors.user.coa_pdf_validity_issuer_location_check_error',
            'http_status_code' => 200,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'log-only',
        ],
        'COA_PDF_VALIDITY_ISSUE_LOCATION_FROM_USER_ERROR' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_pdf_validity_issue_location_from_user_error',
            'user_message_key' => 'error-manager::errors.user.coa_pdf_validity_issue_location_from_user_error',
            'http_status_code' => 200,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'log-only',
        ],
        'COA_PDF_COATRAITS_ACCESS_ERROR' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.coa_pdf_coatraits_access_error',
            'user_message_key' => 'error-manager::errors.user.coa_pdf_coatraits_access_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        // Early-exit validity missing requirements (each must log via UEM)
        'COA_PDF_VALIDITY_STATUS_INVALID' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_pdf_validity_status_invalid',
            'user_message_key' => 'error-manager::errors.user.coa_pdf_validity_status_invalid',
            'http_status_code' => 200,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],
        'COA_PDF_VALIDITY_MISSING_TRAITS' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_pdf_validity_missing_traits',
            'user_message_key' => 'error-manager::errors.user.coa_pdf_validity_missing_traits',
            'http_status_code' => 200,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],
        'COA_PDF_VALIDITY_MISSING_AUTHOR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_pdf_validity_missing_author',
            'user_message_key' => 'error-manager::errors.user.coa_pdf_validity_missing_author',
            'http_status_code' => 200,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],
        'COA_PDF_VALIDITY_MISSING_CREATION_DATE' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_pdf_validity_missing_creation_date',
            'user_message_key' => 'error-manager::errors.user.coa_pdf_validity_missing_creation_date',
            'http_status_code' => 200,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],
        'COA_PDF_VALIDITY_MISSING_ISSUE_PLACE' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_pdf_validity_missing_issue_place',
            'user_message_key' => 'error-manager::errors.user.coa_pdf_validity_missing_issue_place',
            'http_status_code' => 200,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // CoA location update failures
        'COA_UPDATE_LOCATION_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_update_location_error',
            'user_message_key' => 'error-manager::errors.user.coa_update_location_error',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        // ====================================================
        // COA QES/TSA Sandbox Errors (mock + orchestrator)
        // ====================================================
        'COA_QES_AUTHOR_SIGN_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_qes_author_sign_failed',
            'user_message_key' => 'error-manager::errors.user.coa_qes_author_sign_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'COA_QES_AUTHOR_SIGN_EXCEPTION' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_qes_author_sign_exception',
            'user_message_key' => 'error-manager::errors.user.coa_qes_author_sign_exception',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'COA_QES_INSPECTOR_SIGN_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_qes_inspector_sign_failed',
            'user_message_key' => 'error-manager::errors.user.coa_qes_inspector_sign_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'COA_QES_INSPECTOR_SIGN_EXCEPTION' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_qes_inspector_sign_exception',
            'user_message_key' => 'error-manager::errors.user.coa_qes_inspector_sign_exception',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'COA_QES_TIMESTAMP_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_qes_timestamp_failed',
            'user_message_key' => 'error-manager::errors.user.coa_qes_timestamp_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'COA_QES_TIMESTAMP_EXCEPTION' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_qes_timestamp_exception',
            'user_message_key' => 'error-manager::errors.user.coa_qes_timestamp_exception',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'COA_QES_VERIFY_EXCEPTION' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_qes_verify_exception',
            'user_message_key' => 'error-manager::errors.user.coa_qes_verify_exception',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'COA_QES_MOCK_SIGN_ERROR' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_qes_mock_sign_error',
            'user_message_key' => 'error-manager::errors.user.coa_qes_mock_sign_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'COA_QES_MOCK_COSIGN_ERROR' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_qes_mock_cosign_error',
            'user_message_key' => 'error-manager::errors.user.coa_qes_mock_cosign_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'COA_QES_MOCK_TS_ERROR' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_qes_mock_ts_error',
            'user_message_key' => 'error-manager::errors.user.coa_qes_mock_ts_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'COA_QES_MOCK_VERIFY_ERROR' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.coa_qes_mock_verify_error',
            'user_message_key' => 'error-manager::errors.user.coa_qes_mock_verify_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        // ====================================================
        // ProfileImageController Error Codes
        // ====================================================
        'PROFILE_IMAGE_UPLOAD_VALIDATION_ERROR' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.profile_image_upload_validation_error',
            'user_message_key' => 'error-manager::errors_2.user.profile_image_upload_validation_error',
            'http_status_code' => 422,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'PROFILE_IMAGE_UPLOAD_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.profile_image_upload_error',
            'user_message_key' => 'error-manager::errors_2.user.profile_image_upload_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'PROFILE_SET_CURRENT_IMAGE_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.profile_set_current_image_error',
            'user_message_key' => 'error-manager::errors_2.user.profile_set_current_image_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'PROFILE_IMAGE_DELETE_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.profile_image_delete_error',
            'user_message_key' => 'error-manager::errors_2.user.profile_image_delete_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'PROFILE_BANNER_UPLOAD_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.profile_banner_upload_error',
            'user_message_key' => 'error-manager::errors_2.user.profile_banner_upload_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'PROFILE_SET_CURRENT_BANNER_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.profile_set_current_banner_error',
            'user_message_key' => 'error-manager::errors_2.user.profile_set_current_banner_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'PROFILE_BANNER_DELETE_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.profile_banner_delete_error',
            'user_message_key' => 'error-manager::errors_2.user.profile_banner_delete_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        // ====================================================
        // PA/Enterprise System Errors
        // ====================================================

        'PA_DASHBOARD_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.pa_dashboard_error',
            'user_message_key' => 'error-manager::errors_2.user.pa_dashboard_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'PA_DASHBOARD_QUICKSTATS_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.pa_dashboard_quickstats_error',
            'user_message_key' => 'error-manager::errors_2.user.pa_dashboard_quickstats_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'log-only',
        ],

        'PA_HERITAGE_LIST_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.pa_heritage_list_error',
            'user_message_key' => 'error-manager::errors_2.user.pa_heritage_list_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        // ====================================================
        // PA Projects System Errors (FASE 4)
        // ====================================================

        'PROJECT_INDEX_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.project_index_error',
            'user_message_key' => 'error-manager::errors_2.user.project_index_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'PROJECT_CREATE_PAGE_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.project_create_page_error',
            'user_message_key' => 'error-manager::errors_2.user.project_create_page_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'PROJECT_CREATE_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.project_create_failed',
            'user_message_key' => 'error-manager::errors_2.user.project_create_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'PROJECT_SHOW_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.project_show_error',
            'user_message_key' => 'error-manager::errors_2.user.project_show_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'PROJECT_EDIT_PAGE_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.project_edit_page_error',
            'user_message_key' => 'error-manager::errors_2.user.project_edit_page_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'PROJECT_UPDATE_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.project_update_failed',
            'user_message_key' => 'error-manager::errors_2.user.project_update_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'PROJECT_DELETE_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.project_delete_failed',
            'user_message_key' => 'error-manager::errors_2.user.project_delete_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'DOCUMENT_PROCESSING_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.document_processing_failed',
            'user_message_key' => 'error-manager::errors_2.user.document_processing_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'PROJECT_RAG_SEARCH_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.project_rag_search_failed',
            'user_message_key' => 'error-manager::errors_2.user.project_rag_search_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'RAG_CONTEXT_RETRIEVAL_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.rag_context_retrieval_failed',
            'user_message_key' => 'error-manager::errors_2.user.rag_context_retrieval_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'RAG_EMBEDDING_GENERATION_FAILED' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.rag_embedding_generation_failed',
            'user_message_key' => 'error-manager::errors_2.user.rag_embedding_generation_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'PA_HERITAGE_DETAIL_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.pa_heritage_detail_error',
            'user_message_key' => 'error-manager::errors_2.user.pa_heritage_detail_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        // ====================================================
        // BLOCKCHAIN MINTING ERRORS
        // ====================================================
        'MINT_CHECKOUT_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.mint_checkout_error',
            'user_message_key' => 'error-manager::errors_2.user.mint_checkout_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'CERTIFICATE_REGENERATION_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.certificate_regeneration_failed',
            'user_message_key' => 'error-manager::errors_2.user.certificate_regeneration_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'MINT_PROCESS_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.mint_process_error',
            'user_message_key' => 'error-manager::errors_2.user.mint_process_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],
        'REAL_BLOCKCHAIN_MINT_FAILED' => [
            'type' => 'critical',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.real_blockchain_mint_failed',
            'user_message_key' => 'error-manager::errors_2.user.real_blockchain_mint_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        // AREA 2.2.1 - Payment Distribution Service (Mint-based) Error Codes
        'MINT_DISTRIBUTION_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.mint_distribution_error',
            'user_message_key' => 'error-manager::errors_2.user.mint_distribution_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],
        'MINT_NOT_COMPLETED' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.mint_not_completed',
            'user_message_key' => 'error-manager::errors_2.user.mint_not_completed',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'INVALID_PAYMENT_AMOUNT' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.invalid_payment_amount',
            'user_message_key' => 'error-manager::errors_2.user.invalid_payment_amount',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        // AREA 2.2.2 - EgiMintingService Integration Error Codes
        'EGI_MINT_WITH_PAYMENT_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.egi_mint_with_payment_failed',
            'user_message_key' => 'error-manager::errors_2.user.egi_mint_with_payment_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],
        'MINT_DISTRIBUTION_PARTIAL_FAILURE' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.mint_distribution_partial_failure',
            'user_message_key' => 'error-manager::errors_2.user.mint_distribution_partial_failure',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],
        'MINT_DISTRIBUTION_RETRY_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.mint_distribution_retry_failed',
            'user_message_key' => 'error-manager::errors_2.user.mint_distribution_retry_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        // AREA 2.2.3 - EgiPurchaseWorkflowService Error Codes (P1 Compliance Fix)
        'CERTIFICATE_GENERATION_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.certificate_generation_failed',
            'user_message_key' => 'error-manager::errors_2.user.certificate_generation_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],

        // EgiMintingService Error Codes (P1 Compliance Fix)
        'EGI_TRANSFER_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.egi_transfer_failed',
            'user_message_key' => 'error-manager::errors_2.user.egi_transfer_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],
        'EGI_MINTING_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.egi_minting_failed',
            'user_message_key' => 'error-manager::errors_2.user.egi_minting_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        // ====================================================
        // DUAL ARCHITECTURE - EGI Living / SmartContract Errors
        // ====================================================
        'SMART_CONTRACT_DEPLOY_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.smart_contract_deploy_failed',
            'user_message_key' => 'error-manager::errors_2.user.smart_contract_deploy_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],
        'EGI_MINTING_ORCHESTRATOR_FAILED' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.egi_minting_orchestrator_failed',
            'user_message_key' => 'error-manager::errors_2.user.egi_minting_orchestrator_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],
        'PRE_MINT_CREATE_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.pre_mint_create_failed',
            'user_message_key' => 'error-manager::errors_2.user.pre_mint_create_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'PRE_MINT_PROMOTE_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.pre_mint_promote_failed',
            'user_message_key' => 'error-manager::errors_2.user.pre_mint_promote_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'ORACLE_POLL_FAILED' => [
            'type' => 'critical',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.oracle_poll_failed',
            'user_message_key' => 'error-manager::errors_2.user.oracle_poll_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'log',
        ],
        'LIVING_SUBSCRIPTION_CREATE_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.living_subscription_create_failed',
            'user_message_key' => 'error-manager::errors_2.user.living_subscription_create_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'LIVING_SUBSCRIPTION_PAYMENT_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.living_subscription_payment_failed',
            'user_message_key' => 'error-manager::errors_2.user.living_subscription_payment_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],
        'LIVING_SUBSCRIPTION_CANCEL_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.living_subscription_cancel_failed',
            'user_message_key' => 'error-manager::errors_2.user.living_subscription_cancel_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        // ====================================================
        // AREA 2.3.1 - Analytics Errors
        // ====================================================
        'ANALYTICS_DISTRIBUTION_SUMMARY_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.analytics_distribution_summary_error',
            'user_message_key' => 'error-manager::errors_2.user.analytics_distribution_summary_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'ANALYTICS_REVENUE_BREAKDOWN_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.analytics_revenue_breakdown_error',
            'user_message_key' => 'error-manager::errors_2.user.analytics_revenue_breakdown_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'ANALYTICS_WALLET_PERFORMANCE_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.analytics_wallet_performance_error',
            'user_message_key' => 'error-manager::errors_2.user.analytics_wallet_performance_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'ANALYTICS_MINT_VS_RESERVATION_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.analytics_mint_vs_reservation_error',
            'user_message_key' => 'error-manager::errors_2.user.analytics_mint_vs_reservation_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        // ========================================
        // ALGORAND SERVICE - MICROSERVICE ERRORS
        // ========================================
        'MICROSERVICE_NOT_REACHABLE' => [
            'type' => 'critical',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.microservice_not_reachable',
            'user_message_key' => 'error-manager::errors_2.user.microservice_not_reachable',
            'http_status_code' => 503,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],
        'MICROSERVICE_NOT_FOUND' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.microservice_not_found',
            'user_message_key' => 'error-manager::errors_2.user.microservice_not_found',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],
        'MICROSERVICE_AUTO_START_ATTEMPT' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.microservice_auto_start_attempt',
            'user_message_key' => 'error-manager::errors_2.user.microservice_auto_start_attempt',
            'http_status_code' => 503,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],
        'MICROSERVICE_AUTO_STARTED_SUCCESS' => [
            'type' => 'alert',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.microservice_auto_started_success',
            'user_message_key' => 'error-manager::errors_2.user.microservice_auto_started_success',
            'http_status_code' => 200,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],
        'MICROSERVICE_HEALTH_CHECK_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.microservice_health_check_failed',
            'user_message_key' => 'error-manager::errors_2.user.microservice_health_check_failed',
            'http_status_code' => 503,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],
        'MICROSERVICE_AUTO_START_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.microservice_auto_start_failed',
            'user_message_key' => 'error-manager::errors_2.user.microservice_auto_start_failed',
            'http_status_code' => 503,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],
        'MICROSERVICE_AUTO_START_ERROR' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.microservice_auto_start_error',
            'user_message_key' => 'error-manager::errors_2.user.microservice_auto_start_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],
        'MICROSERVICE_NOT_AVAILABLE' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.microservice_not_available',
            'user_message_key' => 'error-manager::errors_2.user.microservice_not_available',
            'http_status_code' => 503,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],
        'MICROSERVICE_CALL_RETRY_EXHAUSTED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.microservice_call_retry_exhausted',
            'user_message_key' => 'error-manager::errors_2.user.microservice_call_retry_exhausted',
            'http_status_code' => 503,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],

        // ========================================
        // ALGORAND SERVICE - BLOCKCHAIN OPERATIONS
        // ========================================
        'BLOCKCHAIN_MINT_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.blockchain_mint_failed',
            'user_message_key' => 'error-manager::errors_2.user.blockchain_mint_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],
        'BLOCKCHAIN_TRANSFER_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.blockchain_transfer_failed',
            'user_message_key' => 'error-manager::errors_2.user.blockchain_transfer_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],
        'BLOCKCHAIN_ANCHOR_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.blockchain_anchor_failed',
            'user_message_key' => 'error-manager::errors_2.user.blockchain_anchor_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],
        'ACCOUNT_INFO_RETRIEVAL_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.account_info_retrieval_failed',
            'user_message_key' => 'error-manager::errors_2.user.account_info_retrieval_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'NETWORK_STATUS_CHECK_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.network_status_check_failed',
            'user_message_key' => 'error-manager::errors_2.user.network_status_check_failed',
            'http_status_code' => 503,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'TREASURY_STATUS_CHECK_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.treasury_status_check_failed',
            'user_message_key' => 'error-manager::errors_2.user.treasury_status_check_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        // === MINT CONTROLLER ERROR CODES ===
        'MINT_CHECKOUT_MISSING_PARAMS' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.mint_checkout_missing_params',
            'user_message_key' => 'error-manager::errors_2.user.mint_checkout_missing_params',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'MINT_CHECKOUT_UNAUTHORIZED' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.mint_checkout_unauthorized',
            'user_message_key' => 'error-manager::errors_2.user.mint_checkout_unauthorized',
            'http_status_code' => 403,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'MINT_CHECKOUT_INVALID_RESERVATION' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.mint_checkout_invalid_reservation',
            'user_message_key' => 'error-manager::errors_2.user.mint_checkout_invalid_reservation',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'MINT_UNAUTHORIZED' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.mint_unauthorized',
            'user_message_key' => 'error-manager::errors_2.user.mint_unauthorized',
            'http_status_code' => 403,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'MINT_INVALID_RESERVATION' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.mint_invalid_reservation',
            'user_message_key' => 'error-manager::errors_2.user.mint_invalid_reservation',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'MINT_ALREADY_MINTED' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.mint_already_minted',
            'user_message_key' => 'error-manager::errors_2.user.mint_already_minted',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'MINT_PAYMENT_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.mint_payment_failed',
            'user_message_key' => 'error-manager::errors_2.user.mint_payment_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],
        'MINT_VALIDATION_ERROR' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.mint_validation_error',
            'user_message_key' => 'error-manager::errors_2.user.mint_validation_error',
            'http_status_code' => 422,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'DIRECT_MINT_NOT_AVAILABLE' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.direct_mint_not_available',
            'user_message_key' => 'error-manager::errors_2.user.direct_mint_not_available',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'DIRECT_MINT_NOT_AVAILABLE_RACE' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.direct_mint_not_available_race',
            'user_message_key' => 'error-manager::errors_2.user.direct_mint_not_available_race',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'DIRECT_MINT_ALREADY_MINTED' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.direct_mint_already_minted',
            'user_message_key' => 'error-manager::errors_2.user.direct_mint_already_minted',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'DIRECT_MINT_PAYMENT_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.direct_mint_payment_failed',
            'user_message_key' => 'error-manager::errors_2.user.direct_mint_payment_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],
        'DIRECT_MINT_VALIDATION_ERROR' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.direct_mint_validation_error',
            'user_message_key' => 'error-manager::errors_2.user.direct_mint_validation_error',
            'http_status_code' => 422,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'MINT_BLOCKED_MICROSERVICE_UNAVAILABLE' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.mint_blocked_microservice_unavailable',
            'user_message_key' => 'error-manager::errors_2.user.mint_blocked_microservice_unavailable',
            'http_status_code' => 503,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],
        'MINT_BLOCKED_WORKER_UNAVAILABLE' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.mint_blocked_worker_unavailable',
            'user_message_key' => 'error-manager::errors_2.user.mint_blocked_worker_unavailable',
            'http_status_code' => 503,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],
        'WORKER_STATUS_CHECK_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.worker_status_check_failed',
            'user_message_key' => 'error-manager::errors_2.user.worker_status_check_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'log',
        ],
        'WORKER_MANUAL_START_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.worker_manual_start_failed',
            'user_message_key' => 'error-manager::errors_2.user.worker_manual_start_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],
        'QUEUE_WORKER_CHECK_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.queue_worker_check_failed',
            'user_message_key' => 'error-manager::errors_2.user.queue_worker_check_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'log',
        ],
        'QUEUE_WORKER_AUTOSTART_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.queue_worker_autostart_failed',
            'user_message_key' => 'error-manager::errors_2.user.queue_worker_autostart_failed',
            'http_status_code' => 503,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],
        'QUEUE_WORKER_AUTOSTART_EXCEPTION' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.queue_worker_autostart_exception',
            'user_message_key' => 'error-manager::errors_2.user.queue_worker_autostart_exception',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],
        'TREASURY_FUNDS_CHECK_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.treasury_funds_check_failed',
            'user_message_key' => 'error-manager::errors_2.user.treasury_funds_check_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'log',
        ],
        'MINT_BLOCKED_INSUFFICIENT_FUNDS' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.mint_blocked_insufficient_funds',
            'user_message_key' => 'error-manager::errors_2.user.mint_blocked_insufficient_funds',
            'http_status_code' => 503,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],

        // === Mint Status API Errors ===
        'MINT_STATUS_UNAUTHORIZED' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.mint_status_unauthorized',
            'user_message_key' => 'error-manager::errors_2.user.mint_status_unauthorized',
            'http_status_code' => 403,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'log',
        ],
        'MINT_STATUS_EGI_NOT_FOUND' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.mint_status_egi_not_found',
            'user_message_key' => 'error-manager::errors_2.user.mint_status_egi_not_found',
            'http_status_code' => 404,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'log',
        ],
        'MINT_STATUS_CHECK_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.mint_status_check_error',
            'user_message_key' => 'error-manager::errors_2.user.mint_status_check_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'log',
        ],

        // === Blockchain-Specific Errors (User-Friendly UEM) ===
        'BLOCKCHAIN_INSUFFICIENT_TREASURY_FUNDS' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.blockchain_insufficient_treasury_funds',
            'user_message_key' => 'error-manager::errors_2.user.blockchain_insufficient_treasury_funds',
            'http_status_code' => 503,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],
        'BLOCKCHAIN_ACCOUNT_NOT_FOUND' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.blockchain_account_not_found',
            'user_message_key' => 'error-manager::errors_2.user.blockchain_account_not_found',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'BLOCKCHAIN_ASSET_OPTIN_REQUIRED' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.blockchain_asset_optin_required',
            'user_message_key' => 'error-manager::errors_2.user.blockchain_asset_optin_required',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'BLOCKCHAIN_NETWORK_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.blockchain_network_error',
            'user_message_key' => 'error-manager::errors_2.user.blockchain_network_error',
            'http_status_code' => 503,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],
        'BLOCKCHAIN_TRANSACTION_POOL_ERROR' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.blockchain_transaction_pool_error',
            'user_message_key' => 'error-manager::errors_2.user.blockchain_transaction_pool_error',
            'http_status_code' => 503,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        // ====================================================
        // Dual Architecture EGI - Auto-Mint & Pre-Mint Errors
        // ====================================================
        'DUAL_ARCH_AUTO_MINT_UNAUTHORIZED' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.dual_arch_auto_mint_unauthorized',
            'user_message_key' => 'error-manager::errors_2.user.dual_arch_auto_mint_unauthorized',
            'http_status_code' => 403,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'json',
        ],

        'DUAL_ARCH_NOT_PRE_MINT' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.dual_arch_not_pre_mint',
            'user_message_key' => 'error-manager::errors_2.user.dual_arch_not_pre_mint',
            'http_status_code' => 400,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'json',
        ],

        'DUAL_ARCH_SMART_CONTRACT_DISABLED' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.dual_arch_smart_contract_disabled',
            'user_message_key' => 'error-manager::errors_2.user.dual_arch_smart_contract_disabled',
            'http_status_code' => 503,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'json',
        ],

        'DUAL_ARCH_AUTO_MINT_FAILED' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.dual_arch_auto_mint_failed',
            'user_message_key' => 'error-manager::errors_2.user.dual_arch_auto_mint_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'json',
        ],

        'DUAL_ARCH_AI_ANALYSIS_FAILED' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.dual_arch_ai_analysis_failed',
            'user_message_key' => 'error-manager::errors_2.user.dual_arch_ai_analysis_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'json',
        ],

        'DUAL_ARCH_PROMOTION_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.dual_arch_promotion_failed',
            'user_message_key' => 'error-manager::errors_2.user.dual_arch_promotion_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'json',
        ],

        // === KMS ERRORS ===
        'KMS_DEK_GENERATION_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.kms_dek_generation_failed',
            'user_message_key' => 'error-manager::errors_2.user.kms_dek_generation_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],

        'KMS_DEK_ENCRYPTION_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.kms_dek_encryption_failed',
            'user_message_key' => 'error-manager::errors_2.user.kms_dek_encryption_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],

        'KMS_DEK_DECRYPTION_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.kms_dek_decryption_failed',
            'user_message_key' => 'error-manager::errors_2.user.kms_dek_decryption_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],

        'KMS_SECURE_ENCRYPT_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.kms_secure_encrypt_failed',
            'user_message_key' => 'error-manager::errors_2.user.kms_secure_encrypt_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],

        'KMS_SECURE_DECRYPT_FAILED' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.kms_secure_decrypt_failed',
            'user_message_key' => 'error-manager::errors_2.user.kms_secure_decrypt_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],

        'KMS_PROVIDER_UNAVAILABLE' => [
            'type' => 'critical',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.kms_provider_unavailable',
            'user_message_key' => 'error-manager::errors_2.user.kms_provider_unavailable',
            'http_status_code' => 503,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],

        'KMS_CONFIGURATION_INVALID' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.kms_configuration_invalid',
            'user_message_key' => 'error-manager::errors_2.user.kms_configuration_invalid',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],

        'KMS_AUDIT_LOG_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.kms_audit_log_failed',
            'user_message_key' => 'error-manager::errors_2.user.kms_audit_log_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'KMS_UNAVAILABLE' => [
            'type' => 'critical',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.kms_unavailable',
            'user_message_key' => 'error-manager::errors_2.user.kms_unavailable',
            'http_status_code' => 503,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],

        // ====================================================
        // N.A.T.A.N. WEB SEARCH ERRORS (v3.0)
        // ====================================================

        'WEB_SEARCH_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.web_search_failed',
            'user_message_key' => 'error-manager::errors_2.user.web_search_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'WEB_SEARCH_SANITIZATION_FAILED' => [
            'type' => 'critical',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.web_search_sanitization_failed',
            'user_message_key' => 'error-manager::errors_2.user.web_search_sanitization_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
        ],

        'NORMATIVE_MONITORING_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.normative_monitoring_failed',
            'user_message_key' => 'error-manager::errors_2.user.normative_monitoring_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'NORMATIVE_NOTIFICATION_FAILED' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.normative_notification_failed',
            'user_message_key' => 'error-manager::errors_2.user.normative_notification_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'FUNDING_SEARCH_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.funding_search_failed',
            'user_message_key' => 'error-manager::errors_2.user.funding_search_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'COMPETITOR_BENCHMARK_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.competitor_benchmark_failed',
            'user_message_key' => 'error-manager::errors_2.user.competitor_benchmark_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        // N.A.T.A.N. CHAT HISTORY ERRORS (v3.1)
        'NATAN_HISTORY_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.natan_history_failed',
            'user_message_key' => 'error-manager::errors_2.user.natan_history_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'NATAN_SESSION_RETRIEVAL_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.natan_session_retrieval_failed',
            'user_message_key' => 'error-manager::errors_2.user.natan_session_retrieval_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'NATAN_SESSION_DELETE_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.natan_session_delete_failed',
            'user_message_key' => 'error-manager::errors_2.user.natan_session_delete_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'NATAN_CONFIG_INDEX_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.natan_config_index_error',
            'user_message_key' => 'error-manager::errors_2.user.natan_config_index_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'NATAN_CONFIG_UPDATE_ERROR' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.natan_config_update_error',
            'user_message_key' => 'error-manager::errors_2.user.natan_config_update_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'NATAN_CONFIG_RESET_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.natan_config_reset_error',
            'user_message_key' => 'error-manager::errors_2.user.natan_config_reset_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        // ===========================
        // AI CREDITS ERRORS (Task 5)
        // ===========================

        'AI_CREDITS_INSUFFICIENT' => [
            'type' => 'warning',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.ai_credits_insufficient',
            'user_message_key' => 'error-manager::errors_2.user.ai_credits_insufficient',
            'http_status_code' => 402, // Payment Required
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'AI_CREDITS_DEDUCT_FAILED' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.ai_credits_deduct_failed',
            'user_message_key' => 'error-manager::errors_2.user.ai_credits_deduct_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],

        'AI_CREDITS_REFUND_FAILED' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.ai_credits_refund_failed',
            'user_message_key' => 'error-manager::errors_2.user.ai_credits_refund_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],

        'AI_CREDITS_CALCULATION_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.ai_credits_calculation_failed',
            'user_message_key' => 'error-manager::errors_2.user.ai_credits_calculation_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'log',
        ],

        'AI_CREDITS_ESTIMATION_FAILED' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.ai_credits_estimation_failed',
            'user_message_key' => 'error-manager::errors_2.user.ai_credits_estimation_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'log',
        ],

        // === N.A.T.A.N. CHAT ERRORS ===
        'NATAN_AI_CONSENT_REQUIRED' => [
            'type' => 'warning',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.natan_ai_consent_required',
            'user_message_key' => 'error-manager::errors_2.user.natan_ai_consent_required',
            'http_status_code' => 403,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'NATAN_MESSAGE_PROCESSING_FAILED' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.natan_message_processing_failed',
            'user_message_key' => 'error-manager::errors_2.user.natan_message_processing_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],

        'NATAN_QUERY_PROCESSING_FAILED' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.natan_query_processing_failed',
            'user_message_key' => 'error-manager::errors_2.user.natan_query_processing_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'NATAN_SESSION_DELETE_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.natan_session_delete_failed',
            'user_message_key' => 'error-manager::errors_2.user.natan_session_delete_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'NATAN_SEARCH_PREVIEW_FAILED' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.natan_search_preview_failed',
            'user_message_key' => 'error-manager::errors_2.user.natan_search_preview_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'NATAN_ANALYSIS_FAILED' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.natan_analysis_failed',
            'user_message_key' => 'error-manager::errors_2.user.natan_analysis_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],

        'NATAN_SUGGESTIONS_FAILED' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.natan_suggestions_failed',
            'user_message_key' => 'error-manager::errors_2.user.natan_suggestions_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'NATAN_HISTORY_ACCESS_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.natan_history_access_failed',
            'user_message_key' => 'error-manager::errors_2.user.natan_history_access_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'NATAN_SESSION_ACCESS_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.natan_session_access_failed',
            'user_message_key' => 'error-manager::errors_2.user.natan_session_access_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'NATAN_CHUNKING_PROGRESS_FAILED' => [
            'type' => 'warning',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.natan_chunking_progress_failed',
            'user_message_key' => 'error-manager::errors_2.user.natan_chunking_progress_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

        'NATAN_CHUNKING_FINAL_FAILED' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.natan_chunking_final_failed',
            'user_message_key' => 'error-manager::errors_2.user.natan_chunking_final_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],

        'ANTHROPIC_MODEL_FALLBACK' => [
            'type' => 'warning',
            'blocking' => 'non-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.anthropic_model_fallback',
            'user_message_key' => 'error-manager::errors_2.user.anthropic_model_fallback',
            'http_status_code' => 200,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'log',
        ],

        'NATAN_API_CALL_FAILED' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.natan_api_call_failed',
            'user_message_key' => 'error-manager::errors_2.user.natan_api_call_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],

        'NATAN_CHUNKING_MAX_RETRIES' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.natan_chunking_max_retries',
            'user_message_key' => 'error-manager::errors_2.user.natan_chunking_max_retries',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],

        'NATAN_JOB_SESSION_NOT_FOUND' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.natan_job_session_not_found',
            'user_message_key' => 'error-manager::errors_2.user.natan_job_session_not_found',
            'http_status_code' => 404,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],

        'NATAN_JOB_ANALYSIS_FAILED' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.natan_job_analysis_failed',
            'user_message_key' => 'error-manager::errors_2.user.natan_job_analysis_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],

        'NATAN_JOB_USER_NOT_FOUND' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.natan_job_user_not_found',
            'user_message_key' => 'error-manager::errors_2.user.natan_job_user_not_found',
            'http_status_code' => 404,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],

    ]
];
