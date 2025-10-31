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


        // ====================================================
        // GENERIC
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
        // AUTH
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
        // ROUTING
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
        // DATABASE
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


        // ====================================================
        // USE
        // ====================================================

        'USE_QUERY_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.use_query_failed',
            'user_message_key' => 'error-manager::errors_2.user.use_query_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],

        'USE_EMBEDDING_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.use_embedding_failed',
            'user_message_key' => 'error-manager::errors_2.user.use_embedding_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],

        'USE_AUDIT_SAVE_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.use_audit_save_failed',
            'user_message_key' => 'error-manager::errors_2.user.use_audit_save_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'log-only',
        ],


        // ====================================================
        // NATAN
        // ====================================================

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


        // ====================================================
        // RAG
        // ====================================================

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


        // ====================================================
        // GDPR
        // ====================================================

        'GDPR_AUDIT_LOG_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_activity_log_error',
            'user_message_key' => 'error-manager::errors.user.gdpr_activity_log_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'GDPR_AUDIT_STATS_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_audit_stats_error',
            'user_message_key' => 'error-manager::errors.user.gdpr_audit_stats_error',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'sweet-alert',
        ],

        'GDPR_AUDIT_PURGE_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_audit_purge_error',
            'user_message_key' => 'error-manager::errors.user.gdpr_audit_purge_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
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

        'CONSENT_HISTORY_RECORDING_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_consent_history_failed',
            'user_message_key' => 'error-manager::errors.user.gdpr_consent_history_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
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
        // GDPR_EXPORT
        // ====================================================

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

        'GDPR_EXPORT_FILE_NOT_FOUND_ON_DISK' => [
            'type' => 'error',
            'blocking' => 'blocking',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_export_file_not_found_on_disk',
            'user_message_key' => 'error-manager::errors.user.gdpr_export_file_not_found_on_disk',
            'http_status_code' => 404,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'sweet-alert',
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


        // ====================================================
        // GDPR_RESTRICTIONS
        // ====================================================

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

        'GDPR_EXPIRED_RESTRICTION_PROCESS_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_expired_restriction_process_error',
            'user_message_key' => 'error-manager::errors.user.gdpr_expired_restriction_process_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'log-only',
        ],

        'GDPR_NOTIFICATION_CLASS_INVALID' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_notification_class_invalid',
            'user_message_key' => 'error-manager::errors.user.gdpr_notification_class_invalid',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'log-only',
        ],

        'GDPR_RESTRICTION_NOTIFICATION_ERROR' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors.dev.gdpr_restriction_notification_error',
            'user_message_key' => 'error-manager::errors.user.gdpr_restriction_notification_error',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => false,
            'msg_to' => 'log-only',
        ],


        // ====================================================
        // BLOCKCHAIN
        // ====================================================

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

        'ALGORAND_ACCOUNT_CREATE_FAILED' => [
            'type' => 'error',
            'blocking' => 'semi-blocking',
            'dev_message_key' => 'error-manager::errors_2.dev.algorand_account_create_failed',
            'user_message_key' => 'error-manager::errors_2.user.algorand_account_create_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => true,
            'notify_slack' => true,
            'msg_to' => 'toast',
        ],

        'ALGORAND_ACCOUNT_INFO_FAILED' => [
            'type' => 'error',
            'blocking' => 'not',
            'dev_message_key' => 'error-manager::errors_2.dev.algorand_account_info_failed',
            'user_message_key' => 'error-manager::errors_2.user.algorand_account_info_failed',
            'http_status_code' => 500,
            'devTeam_email_need' => false,
            'notify_slack' => false,
            'msg_to' => 'toast',
        ],

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

    ]
];
