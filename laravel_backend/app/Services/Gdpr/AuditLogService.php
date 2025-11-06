<?php

namespace App\Services\Gdpr;

use App\Models\User;
use App\Models\UserActivity;
use App\Models\SecurityEvent;
use App\Models\GdprAuditLog;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Ultra\UltraLogManager\UltraLogManager;
use Ultra\ErrorManager\Interfaces\ErrorManagerInterface;
use Carbon\Carbon;
use App\Enums\Gdpr\GdprActivityCategory;

/**
 * @Oracode Service: Audit Trail Management
 * 🎯 Purpose: Comprehensive audit logging for GDPR compliance
 * 🛡️ Privacy: Tracks all user actions with privacy-safe storage
 * 🧱 Core Logic: Records, retrieves, and exports audit trails
 *
 * @package App\Services
 * @author Padmin D. Curtis (for Fabio Cherici)
 * @version 1.0.0
 * @date 2025-05-22
 */
class AuditLogService {
    /**
     * Logger instance for service operations
     * @var UltraLogManager
     */
    protected UltraLogManager $logger;

    /**
     * Error manager for robust error handling
     * @var ErrorManagerInterface
     */
    protected ErrorManagerInterface $errorManager;

    /**
     * Audit log retention period in days
     * @var int
     */
    protected int $retentionDays = 2555; // 7 years for legal compliance

    /**
     * Activity categories for classification
     * @var array
     */
    protected array $activityCategories = [];

    /**
     * Sensitive data fields that need special handling
     * @var array
     */
    protected array $sensitiveFields = [
        'password',
        'token',
        'secret',
        'private_key',
        'credit_card',
        'ssn',
        'passport',
        'driver_license',
        'bank_account'
    ];

    /**
     * Constructor with dependency injection
     *
     * @param UltraLogManager $logger
     * @param ErrorManagerInterface $errorManager
     * @privacy-safe All injected dependencies handle GDPR compliance
     */
    public function __construct(
        UltraLogManager $logger,
        ErrorManagerInterface $errorManager
    ) {
        $this->logger = $logger;
        $this->errorManager = $errorManager;
        // GDPR Config: Get activity categories with safe fallback
        $this->activityCategories = config('gdpr.activity_categories', []);
        
        // Validate config loaded
        if (empty($this->activityCategories)) {
            $this->logger->warning('[AuditLogService] GDPR activity_categories config is empty, using defaults', [
                'log_category' => 'AUDIT_SERVICE_CONFIG_WARNING'
            ]);
        }
    }

    /**
     * Log user action with full context
     *
     * @param User $user
     * @param string $action
     * @param array $context
     * @param GdprActivityCategory $category
     * @return UserActivity
     * @privacy-safe Logs action for specified user only
     */
    public function logUserAction(
        User $user,
        string $action,
        array $context = [],
        GdprActivityCategory $category
    ): UserActivity {
        try {
            $this->logger->debug('Audit Log Service: Logging user action', [
                'user_id' => $user->id,
                'action' => $action,
                'category' => $category,
                'log_category' => 'AUDIT_SERVICE_OPERATION'
            ]);

            // Sanitize sensitive data from context
            $sanitizedContext = $this->sanitizeContext($context);

            // Get request metadata
            $requestMetadata = $this->getRequestMetadata();

            /* Determine retention period based on category
            *  GdprActivityCategory $categories
            */
            if (!isset($this->activityCategories[$category->value])) {
                $this->logger->warning('Audit Log Service: Unknown category, using default retention', [
                    'category' => $category->value,
                    'log_category' => 'AUDIT_SERVICE_WARNING'
                ]);
            }
            $retentionDays = $this->activityCategories[$category->value]['retention_period'] ?? $this->retentionDays;

            $activity = UserActivity::create([
                'user_id' => $user->id,
                'action' => $action,
                'category' => $category->value,
                'context' => $sanitizedContext,
                'metadata' => $requestMetadata,
                'ip_address' => $this->maskIpAddress($requestMetadata['ip_address']),
                'user_agent' => $requestMetadata['user_agent'],
                'session_id' => $requestMetadata['session_id'],
                'expires_at' => now()->addDays($retentionDays),
                'privacy_level' => $this->activityCategories[$category->value]['privacy_level'] ?? 'standard'
            ]);

            // Log critical actions immediately to ULM
            if (in_array($category, ['gdpr_actions', 'security_events', 'authentication'])) {
                $this->logger->info('Critical user action logged', [
                    'user_id' => $user->id,
                    'action' => $action,
                    'category' => $category,
                    'activity_id' => $activity->id,
                    'log_category' => 'AUDIT_CRITICAL_ACTION'
                ]);
            }

            return $activity;
        } catch (\Exception $e) {
            $this->logger->error('Audit Log Service: Failed to log user action', [
                'user_id' => $user->id,
                'action' => $action,
                'error' => $e->getMessage(),
                'log_category' => 'AUDIT_SERVICE_ERROR'
            ]);

            throw $e;
        }
    }

    /**
     * Log security event
     *
     * @param User|null $user
     * @param string $eventType
     * @param array $details
     * @param string $severity
     * @return SecurityEvent
     * @privacy-safe Logs security event with appropriate privacy level
     */
    public function logSecurityEvent(
        ?User $user,
        string $eventType,
        array $details = [],
        string $severity = 'medium'
    ): SecurityEvent {
        try {
            $this->logger->warning('Audit Log Service: Logging security event', [
                'user_id' => $user?->id,
                'event_type' => $eventType,
                'severity' => $severity,
                'log_category' => 'AUDIT_SERVICE_SECURITY'
            ]);

            $requestMetadata = $this->getRequestMetadata();
            $sanitizedDetails = $this->sanitizeContext($details);

            $securityEvent = SecurityEvent::create([
                'user_id' => $user?->id,
                'event_type' => $eventType,
                'severity' => $severity,
                'details' => $sanitizedDetails,
                'ip_address' => $this->maskIpAddress($requestMetadata['ip_address']),
                'user_agent' => $requestMetadata['user_agent'],
                'metadata' => $requestMetadata,
                'expires_at' => now()->addDays($this->retentionDays) // 7 years for security events
            ]);

            // Log high/critical severity events to ULM immediately
            if (in_array($severity, ['high', 'critical'])) {
                $this->logger->warning('High severity security event', [
                    'user_id' => $user?->id,
                    'event_type' => $eventType,
                    'severity' => $severity,
                    'security_event_id' => $securityEvent->id,
                    'log_category' => 'AUDIT_SECURITY_CRITICAL'
                ]);
            }

            return $securityEvent;
        } catch (\Exception $e) {
            $this->logger->error('Audit Log Service: Failed to log security event', [
                'user_id' => $user?->id,
                'event_type' => $eventType,
                'error' => $e->getMessage(),
                'log_category' => 'AUDIT_SERVICE_ERROR'
            ]);

            throw $e;
        }
    }

    /**
     * Log GDPR-specific action
     *
     * @param User $user
     * @param string $gdprAction
     * @param array $details
     * @param string $legalBasis
     * @return GdprAuditLog
     * @privacy-safe Logs GDPR action with full compliance tracking
     */
    public function logGdprAction(
        User $user,
        string $gdprAction,
        array $details = [],
        string $legalBasis = 'user_request'
    ): GdprAuditLog {
        try {
            $this->logger->info('Audit Log Service: Logging GDPR action', [
                'user_id' => $user->id,
                'gdpr_action' => $gdprAction,
                'legal_basis' => $legalBasis,
                'log_category' => 'AUDIT_SERVICE_GDPR'
            ]);

            $requestMetadata = $this->getRequestMetadata();
            $sanitizedDetails = $this->sanitizeContext($details);

            $gdprLog = GdprAuditLog::create([
                'user_id' => $user->id,
                'action_type' => $gdprAction, // Corretto: usa action_type
                'category' => 'gdpr_actions', // Corretto: usa valore enum valido
                'description' => "GDPR action: {$gdprAction}",
                'legal_basis' => $legalBasis,
                'context_data' => $sanitizedDetails, // Corretto: usa context_data
                'ip_address' => $this->maskIpAddress($requestMetadata['ip_address']),
                'user_agent' => $requestMetadata['user_agent'],
            ]);

            // All GDPR actions are critical and logged to ULM
            $this->logger->info('GDPR action recorded', [
                'user_id' => $user->id,
                'gdpr_action' => $gdprAction,
                'legal_basis' => $legalBasis,
                'gdpr_log_id' => $gdprLog->id,
                'log_category' => 'AUDIT_GDPR_ACTION'
            ]);

            // Also create user activity record
            $this->logUserAction($user, "gdpr_{$gdprAction}", $details, GdprActivityCategory::GDPR_ACTIONS);

            return $gdprLog;
        } catch (\Exception $e) {
            $this->logger->error('Audit Log Service: Failed to log GDPR action', [
                'user_id' => $user->id,
                'gdpr_action' => $gdprAction,
                'error' => $e->getMessage(),
                'log_category' => 'AUDIT_SERVICE_ERROR'
            ]);

            throw $e;
        }
    }

    /**
     * Get user's activity log
     *
     * @param User $user
     * @param int $limit
     * @param array $categories
     * @param Carbon|null $dateFrom
     * @param Carbon|null $dateTo
     * @return Collection
     * @privacy-safe Returns user's own activity log only
     */
    public function getUserActivityLog(
        User $user,
        int $perPage = 50,
        array $categories = [],
        ?Carbon $dateFrom = null,
        ?Carbon $dateTo = null
    ) {
        try {
            $this->logger->info('Audit Log Service: Getting user activity log', [
                'user_id' => $user->id,
                'per_page' => $perPage,
                'categories' => $categories,
                'log_category' => 'AUDIT_SERVICE_OPERATION'
            ]);

            $query = $user->activities()->orderBy('created_at', 'desc');

            if (!empty($categories)) {
                $query->whereIn('category', $categories);
            }

            if ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->where('created_at', '<=', $dateTo);
            }

            // Return paginated results - view uses ->links() for pagination
            return $query->paginate($perPage);
        } catch (\Exception $e) {
            $this->logger->error('Audit Log Service: Failed to get activity log', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'log_category' => 'AUDIT_SERVICE_ERROR'
            ]);

            throw $e;
        }
    }

    /**
     * Get user's activity statistics
     *
     * @param User $user
     * @return array
     * @privacy-safe Returns statistics for user's own activities
     */
    public function getUserActivityStats(User $user): array {
        try {
            $this->logger->info('Audit Log Service: Getting user activity statistics', [
                'user_id' => $user->id,
                'log_category' => 'AUDIT_SERVICE_OPERATION'
            ]);

            $cacheKey = "user_activity_stats_{$user->id}";

            return Cache::remember($cacheKey, 300, function () use ($user) {
                $stats = [
                    'total_activities' => $user->activities()->count(),
                    'categories' => [],
                    'recent_activity' => $user->activities()
                        ->where('created_at', '>=', now()->subDays(30))
                        ->count(),
                    'most_active_day' => null,
                    'privacy_levels' => []
                ];

                // Category breakdown
                foreach ($this->activityCategories as $category => $config) {
                    $count = $user->activities()->where('category', $category)->count();
                    $stats['categories'][$category] = [
                        'count' => $count,
                        'name' => $config['name'],
                        'percentage' => $stats['total_activities'] > 0
                            ? round(($count / $stats['total_activities']) * 100, 1)
                            : 0
                    ];
                }

                // Privacy level breakdown
                $privacyLevels = $user->activities()
                    ->select('privacy_level', DB::raw('count(*) as count'))
                    ->groupBy('privacy_level')
                    ->pluck('count', 'privacy_level')
                    ->toArray();

                foreach (['standard', 'high', 'critical', 'immutable'] as $level) {
                    $stats['privacy_levels'][$level] = $privacyLevels[$level] ?? 0;
                }

                // Most active day (last 90 days)
                $mostActiveDay = $user->activities()
                    ->where('created_at', '>=', now()->subDays(90))
                    ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                    ->groupBy('date')
                    ->orderBy('count', 'desc')
                    ->first();

                if ($mostActiveDay) {
                    $stats['most_active_day'] = [
                        'date' => $mostActiveDay->date,
                        'activity_count' => $mostActiveDay->count
                    ];
                }

                return $stats;
            });
        } catch (\Exception $e) {
            $this->logger->error('Audit Log Service: Failed to get activity statistics', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'log_category' => 'AUDIT_SERVICE_ERROR'
            ]);

            throw $e;
        }
    }

    /**
     * Export user's activity log
     *
     * @param User $user
     * @param array $options
     * @return StreamedResponse
     * @privacy-safe Exports user's own activity log only
     */
    public function exportUserActivityLog(User $user, array $options = []): StreamedResponse {
        try {
            $this->logger->info('Audit Log Service: Exporting user activity log', [
                'user_id' => $user->id,
                'format' => $options['format'] ?? 'csv',
                'log_category' => 'AUDIT_SERVICE_EXPORT'
            ]);

            $format = $options['format'] ?? 'csv';
            $dateFrom = isset($options['date_from']) ? Carbon::parse($options['date_from']) : null;
            $dateTo = isset($options['date_to']) ? Carbon::parse($options['date_to']) : null;

            // Get activities for export (no limit for full export)
            $activities = $this->getUserActivityLogForExport($user, $dateFrom, $dateTo);

            // Log the export action
            $this->logUserAction($user, 'activity_log_exported', [
                'format' => $format,
                'date_range' => [
                    'from' => $dateFrom?->toDateString(),
                    'to' => $dateTo?->toDateString()
                ],
                'total_records' => $activities->count()
            ],  GdprActivityCategory::DATA_ACCESS);

            $fileName = "activity_log_{$user->id}_" . now()->format('Y-m-d_H-i-s') . ".{$format}";

            return response()->streamDownload(function () use ($activities, $format) {
                if ($format === 'json') {
                    echo json_encode($activities->toArray(), JSON_PRETTY_PRINT);
                } else {
                    // CSV format
                    $output = fopen('php://output', 'w');

                    // CSV headers
                    fputcsv($output, [
                        'Timestamp',
                        'Action',
                        'Category',
                        'Privacy Level',
                        'IP Address',
                        'Session ID',
                        'Context'
                    ]);

                    foreach ($activities as $activity) {
                        fputcsv($output, [
                            $activity['timestamp'],
                            $activity['action'],
                            $activity['category_name'],
                            $activity['privacy_level'],
                            $activity['ip_address'],
                            $activity['session_id'],
                            json_encode($activity['context'])
                        ]);
                    }

                    fclose($output);
                }
            }, $fileName, [
                'Content-Type' => $format === 'json' ? 'application/json' : 'text/csv',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Audit Log Service: Failed to export activity log', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'log_category' => 'AUDIT_SERVICE_ERROR'
            ]);

            throw $e;
        }
    }

    /**
     * Clean expired audit logs
     *
     * @return array Cleanup statistics
     * @privacy-safe Cleans only expired logs according to retention policy
     */
    public function cleanExpiredLogs(): array {
        try {
            $this->logger->info('Audit Log Service: Cleaning expired logs', [
                'log_category' => 'AUDIT_SERVICE_MAINTENANCE'
            ]);

            $stats = [
                'user_activities_cleaned' => 0,
                'security_events_cleaned' => 0,
                'gdpr_logs_cleaned' => 0,
                'total_cleaned' => 0
            ];

            DB::transaction(function () use (&$stats) {
                // Clean expired user activities
                $expiredActivities = UserActivity::where('expires_at', '<', now())->count();
                UserActivity::where('expires_at', '<', now())->delete();
                $stats['user_activities_cleaned'] = $expiredActivities;

                // Clean expired security events
                $expiredSecurity = SecurityEvent::where('expires_at', '<', now())->count();
                SecurityEvent::where('expires_at', '<', now())->delete();
                $stats['security_events_cleaned'] = $expiredSecurity;

                // Clean expired GDPR logs (with extra caution - 7+ years only)
                $expiredGdpr = GdprAuditLog::where('retention_until', '<', now()->subYears(7))->count();
                GdprAuditLog::where('retention_until', '<', now()->subYears(7))->delete();
                $stats['gdpr_logs_cleaned'] = $expiredGdpr;

                $stats['total_cleaned'] = $stats['user_activities_cleaned'] +
                    $stats['security_events_cleaned'] +
                    $stats['gdpr_logs_cleaned'];
            });

            $this->logger->info('Audit Log Service: Expired logs cleaned', [
                'statistics' => $stats,
                'log_category' => 'AUDIT_SERVICE_MAINTENANCE'
            ]);

            return $stats;
        } catch (\Exception $e) {
            $this->logger->error('Audit Log Service: Failed to clean expired logs', [
                'error' => $e->getMessage(),
                'log_category' => 'AUDIT_SERVICE_ERROR'
            ]);

            throw $e;
        }
    }

    /**
     * Get audit trail for specific action/entity
     *
     * @param User $user
     * @param string $entityType
     * @param int $entityId
     * @return Collection
     * @privacy-safe Returns audit trail for user's own entities only
     */
    public function getEntityAuditTrail(User $user, string $entityType, int $entityId): Collection {
        try {
            $this->logger->info('Audit Log Service: Getting entity audit trail', [
                'user_id' => $user->id,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'log_category' => 'AUDIT_SERVICE_OPERATION'
            ]);

            return $user->activities()
                ->where('context->entity_type', $entityType)
                ->where('context->entity_id', $entityId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($activity) {
                    // Get category value (enum object to string)
                    $categoryValue = $activity->category instanceof \BackedEnum
                        ? $activity->category->value
                        : $activity->category;

                    return [
                        'timestamp' => $activity->created_at->toISOString(),
                        'action' => $activity->action,
                        'category' => $categoryValue,
                        'context' => $activity->context,
                        'privacy_level' => $activity->privacy_level
                    ];
                });
        } catch (\Exception $e) {
            $this->logger->error('Audit Log Service: Failed to get entity audit trail', [
                'user_id' => $user->id,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'error' => $e->getMessage(),
                'log_category' => 'AUDIT_SERVICE_ERROR'
            ]);

            throw $e;
        }
    }

    // ===================================================================
    // PRIVATE HELPER METHODS
    // ===================================================================

    /**
     * Get request metadata for audit logging
     *
     * @return array
     * @privacy-safe Collects standard request metadata
     */
    private function getRequestMetadata(): array {
        return [
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'timestamp' => now()->toISOString(),
            'timezone' => config('app.timezone', 'UTC'),
            'user_timezone' => auth()->user()?->timezone ?? 'UTC'
        ];
    }

    /**
     * Sanitize context data to remove sensitive information
     *
     * @param array $context
     * @return array
     * @privacy-safe Removes sensitive data from context
     */
    private function sanitizeContext(array $context): array {
        return $this->recursiveSanitize($context);
    }

    /**
     * Recursively sanitize array data
     *
     * @param mixed $data
     * @return mixed
     * @privacy-safe Recursively removes sensitive data
     */
    private function recursiveSanitize($data) {
        if (is_array($data)) {
            $sanitized = [];
            foreach ($data as $key => $value) {
                if (in_array(strtolower($key), $this->sensitiveFields)) {
                    $sanitized[$key] = '[REDACTED]';
                } else {
                    $sanitized[$key] = $this->recursiveSanitize($value);
                }
            }
            return $sanitized;
        }

        if (is_string($data) && strlen($data) > 500) {
            // Truncate very long strings
            return substr($data, 0, 500) . '... [TRUNCATED]';
        }

        return $data;
    }

    /**
     * Get user activity log for export (no limit)
     *
     * @param User $user
     * @param Carbon|null $dateFrom
     * @param Carbon|null $dateTo
     * @return Collection
     * @privacy-safe Returns complete activity log for user
     */
    private function getUserActivityLogForExport(User $user, ?Carbon $dateFrom, ?Carbon $dateTo): Collection {
        $query = $user->activities()->orderBy('created_at', 'desc');

        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo);
        }

        return $query->get()->map(function ($activity) {
            // Get category value (enum object to string)
            $categoryValue = $activity->category instanceof \BackedEnum
                ? $activity->category->value
                : $activity->category;

            return [
                'timestamp' => $activity->created_at->toISOString(),
                'action' => $activity->action,
                'category' => $categoryValue,
                'category_name' => $this->activityCategories[$categoryValue]['name'] ?? 'Unknown',
                'context' => $activity->context,
                'ip_address' => $activity->ip_address,
                'privacy_level' => $activity->privacy_level,
                'session_id' => substr($activity->session_id, 0, 8) . '...'
            ];
        });
    }

    /**
     * Mask IP address for privacy compliance
     *
     * @param string|null $ipAddress
     * @return string|null
     * @privacy-safe Masks IP address while preserving general location
     */
    private function maskIpAddress(?string $ipAddress): ?string {
        if (!$ipAddress) {
            return null;
        }

        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ipAddress);
            $parts[3] = 'xxx';
            return implode('.', $parts);
        }

        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ipAddress);
            $parts[count($parts) - 1] = 'xxxx';
            return implode(':', $parts);
        }

        return 'masked';
    }
}
