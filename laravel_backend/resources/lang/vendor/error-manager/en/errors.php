<?php

declare(strict_types=1);

/**
 * @package App\Lang\Vendor\ErrorManager
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-01-20
 * @purpose English translations for error-manager::errors.*
 */

return [
    'dev' => [
        // Generic errors
        'undefined_error_code' => 'Undefined error code: :code',
        'fatal_fallback_failure' => 'Fatal error in error management system. Unable to use fallback.',
        'unexpected_error' => 'Unexpected error: :message',
        'generic_server_error' => 'Generic server error. Details: :message',
        'json_error' => 'JSON parsing error: :message',
        'invalid_input' => 'Invalid input: :field = :value',

        // Auth errors
        'authentication_error' => 'Authentication error. User not authenticated.',
        'authorization_error' => 'Authorization error. User :user_id does not have required permissions for :action',
        'csrf_token_mismatch' => 'CSRF token invalid or expired.',

        // Routing errors
        'route_not_found' => 'Route not found: :route',
        'resource_not_found' => 'Resource not found: :resource with ID :id',
        'method_not_allowed' => 'HTTP method not allowed: :method on route :route',
        'too_many_requests' => 'Too many requests from IP :ip. Rate limit exceeded.',

        // Database errors
        'database_error' => 'Database error: :message',
        'record_not_found' => 'Record not found in table :table with conditions :conditions',

        // GDPR errors
        'gdpr_activity_log_error' => 'Error logging GDPR activity for user :user_id: :message',
        'gdpr_audit_stats_error' => 'Error calculating GDPR statistics for user :user_id: :message',
        'gdpr_audit_purge_error' => 'Error purging obsolete GDPR logs: :message',
        'gdpr_violation_attempt' => 'GDPR violation attempt detected. User: :user_id, Action: :action, Details: :details',
        'gdpr_notification_dispatch_failed' => 'Error dispatching GDPR notification: :message',
        'gdpr_consent_history_failed' => 'Error recording consent history for user :user_id: :message',
        'terms_acceptance_check_failed' => 'Error checking terms acceptance for user :user_id: :message',

        // GDPR Export errors
        'gdpr_export_history_failed' => 'Error retrieving GDPR export history for user :user_id: :message',
        'gdpr_export_invalid_format' => 'Invalid export format requested: :format. Supported formats: :supported',
        'gdpr_export_invalid_categories' => 'Invalid export categories requested: :categories. Available categories: :available',
        'gdpr_export_generation_failed' => 'Error generating GDPR export for user :user_id: :message',
        'gdpr_export_processing_failed' => 'Error processing GDPR export ID :export_id: :message',
        'gdpr_export_not_ready' => 'GDPR export ID :export_id not ready yet. Current status: :status',
        'gdpr_export_expired' => 'GDPR export ID :export_id expired on :expired_at',
        'gdpr_export_file_not_found' => 'GDPR export file ID :export_id not found in database',
        'gdpr_export_file_not_found_on_disk' => 'GDPR export file ID :export_id not found on disk. Expected path: :path',
        'gdpr_export_download_failed' => 'Error downloading GDPR export ID :export_id: :message',
        'gdpr_export_cleanup_failed' => 'Error cleaning up expired export files: :message',

        // GDPR Processing Restrictions errors
        'gdpr_processing_restriction_limit_reached' => 'GDPR processing restriction limit reached for user :user_id. Maximum allowed: :limit',
        'gdpr_processing_restriction_create_error' => 'Error creating GDPR processing restriction for user :user_id: :message',
        'gdpr_processing_restriction_remove_error' => 'Error removing GDPR processing restriction ID :restriction_id: :message',
        'gdpr_expired_restriction_process_error' => 'Error processing expired GDPR restrictions: :message',
        'gdpr_notification_class_invalid' => 'Invalid GDPR notification class: :class. Available classes: :available',
        'gdpr_restriction_notification_error' => 'Error sending notification for GDPR restriction ID :restriction_id: :message',
    ],

    'user' => [
        // Generic errors
        'undefined_error_code' => 'An unexpected error occurred. Our team has been notified.',
        'fatal_fallback_failure' => 'A critical error occurred. Our technical team has been alerted.',
        'unexpected_error' => 'An unexpected error occurred. Please try again later or contact support.',
        'generic_server_error' => 'A server problem occurred. Our technical team has been notified.',
        'json_error' => 'Error processing data. Please try again.',
        'invalid_input' => 'The data entered is invalid. Please check the fields and try again.',

        // Auth errors
        'authentication_error' => 'You are not authenticated. Please log in to continue.',
        'authorization_error' => 'You do not have the necessary permissions to perform this action.',
        'csrf_token_mismatch' => 'Your session has expired. Please reload the page and try again.',

        // Routing errors
        'route_not_found' => 'Page not found.',
        'resource_not_found' => 'Resource not found.',
        'method_not_allowed' => 'Operation not allowed.',
        'too_many_requests' => 'You have made too many requests. Please wait a few minutes before trying again.',

        // Database errors
        'database_error' => 'A temporary problem occurred. Please try again in a moment.',
        'record_not_found' => 'The requested resource was not found.',

        // GDPR errors
        'gdpr_activity_log_error' => 'A problem occurred while logging the activity. The action was still completed.',
        'gdpr_audit_stats_error' => 'A problem occurred while calculating statistics. Please try again later.',
        'gdpr_audit_purge_error' => 'A problem occurred while cleaning up data. Our team has been notified.',
        'generic_internal_error' => 'An internal error occurred. Our team has been notified and will resolve the issue as soon as possible.',
        'gdpr_notification_dispatch_failed' => 'A problem occurred while sending the notification. Please try again later.',
        'gdpr_consent_history_failed' => 'A problem occurred while recording consent history. The action was still completed.',
        'generic_error' => 'An error occurred. Please try again later.',

        // GDPR Export errors
        'gdpr_export_history_failed' => 'Unable to retrieve export history. Please try again later.',
        'gdpr_export_invalid_format' => 'The requested format is invalid. Please choose a supported format.',
        'gdpr_export_invalid_categories' => 'The selected categories are invalid. Please check and try again.',
        'gdpr_export_generation_failed' => 'A problem occurred while generating the data export. Our team has been notified.',
        'gdpr_export_processing_failed' => 'A problem occurred while processing the export. Please try again later.',
        'gdpr_export_not_ready' => 'The export is still being processed. Please wait a moment and try again.',
        'gdpr_export_expired' => 'The requested export is no longer available. Please request a new export.',
        'gdpr_export_file_not_found' => 'The requested export file was not found. Please request a new export.',
        'gdpr_export_file_not_found_on_disk' => 'The export file is no longer available. Please request a new export.',
        'gdpr_export_download_failed' => 'A problem occurred while downloading the file. Please try again later.',
        'gdpr_export_cleanup_failed' => 'A problem occurred while cleaning up files. Our team has been notified.',

        // GDPR Processing Restrictions errors
        'gdpr_processing_restriction_limit_reached' => 'You have reached the maximum number of processing restrictions allowed. Remove an existing restriction to create a new one.',
        'gdpr_processing_restriction_create_error' => 'A problem occurred while creating the restriction. Please try again later.',
        'gdpr_processing_restriction_remove_error' => 'A problem occurred while removing the restriction. Please try again later.',
        'gdpr_expired_restriction_process_error' => 'A problem occurred while processing expired restrictions. Our team has been notified.',
        'gdpr_notification_class_invalid' => 'A problem occurred in notification configuration. Our team has been notified.',
        'gdpr_restriction_notification_error' => 'A problem occurred while sending the notification. Our team has been notified.',
    ],
];

