<?php

namespace App\Services\Gdpr;

use App\Models\GdprAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Ultra\ErrorManager\Interfaces\ErrorManagerInterface;

/**
 * GDPR Activity Log Service
 *
 * Manages audit trail for all GDPR-related actions with tamper-proof records.
 *
 * @oracode-dimension governance
 * @value-flow Transparent tracking of all GDPR activities
 * @community-impact Builds trust through complete visibility
 * @transparency-level Maximum - immutable audit trail
 * @sustainability-factor High - compliance with Art. 30 GDPR
 * @narrative-coherence Emphasizes FlorenceEGI's commitment to transparency
 */
class ActivityLogService
{
    /**
     * The error manager instance.
     *
     * @var \Ultra\ErrorManager\Interfaces\ErrorManagerInterface
     */
    protected $errorManager;

    /**
     * Create a new service instance.
     *
     * @param  \Ultra\ErrorManager\Interfaces\ErrorManagerInterface  $errorManager
     * @return void
     */
    public function __construct(ErrorManagerInterface $errorManager)
    {
        $this->errorManager = $errorManager;
    }

    /**
     * Log a GDPR activity with tamper-proof protection.
     *
     * @param  string  $action
     * @param  string  $legalBasis
     * @param  array  $details
     * @param  int|null  $userId
     * @param  string|null  $complianceNote
     * @return \App\Models\GdprAuditLog|null
     */
    public function log(
        string $action,
        string $legalBasis = 'user_request',
        array $details = [],
        ?int $userId = null,
        ?string $complianceNote = null
    ): ?GdprAuditLog {
        try {
            // Get the user ID from the parameter, authenticated user, or null
            $userId = $userId ?? (Auth::check() ? Auth::id() : null);

            // If no user ID is available, we can't create the log
            if ($userId === null) {
                return null;
            }

            // Prepare request metadata
            $requestMetadata = [
                'method' => Request::method(),
                'path' => Request::path(),
                'url' => Request::fullUrl(),
                'referrer' => Request::header('referer'),
                'headers' => $this->sanitizeHeaders(Request::header()),
            ];

            // Calculate retention period based on action type
            $retentionDays = $this->getRetentionPeriod($action);
            $retentionUntil = now()->addDays($retentionDays);

            // Set compliance note if not provided
            if ($complianceNote === null) {
                $complianceNote = $this->getComplianceNote($action, $legalBasis);
            }

            // Create the audit log
            $log = new GdprAuditLog();
            $log->user_id = $userId;
            $log->action = $action;
            $log->legal_basis = $legalBasis;
            $log->details = json_encode($details);
            $log->request_metadata = json_encode($requestMetadata);
            $log->ip_address = Request::ip();
            $log->user_agent = Request::userAgent();
            $log->timestamp = now();
            $log->retention_until = $retentionUntil;
            $log->compliance_note = $complianceNote;
            $log->is_verified = true;

            // Create a hash of the record before saving to ensure immutability
            $recordData = json_encode([
                'user_id' => $log->user_id,
                'action' => $log->action,
                'legal_basis' => $log->legal_basis,
                'details' => $log->details,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'timestamp' => $log->timestamp->toIso8601String(),
            ]);

            $log->record_hash = hash('sha256', $recordData);
            $log->save();

            return $log;
        } catch (\Throwable $e) {
            // Handle error with UEM but don't propagate it (logging shouldn't break functionality)
            $this->errorManager->handle('GDPR_AUDIT_LOG_ERROR', [
                'action' => $action,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ], $e);

            return null;
        }
    }

    /**
     * Sanitize request headers to remove sensitive information.
     *
     * @param  array  $headers
     * @return array
     */
    protected function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = [
            'authorization',
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ];

        $sanitized = [];
        foreach ($headers as $key => $value) {
            $lowerKey = strtolower($key);
            if (in_array($lowerKey, $sensitiveHeaders)) {
                $sanitized[$key] = '[REDACTED]';
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Get the retention period in days for a specific action type.
     *
     * @param  string  $action
     * @return int
     */
    protected function getRetentionPeriod(string $action): int
    {
        $periods = config('gdpr.audit_log.retention_periods', [
            'default' => 730, // 2 years
            'consent_updated' => 1825, // 5 years
            'account_deletion' => 1825, // 5 years
            'data_exported' => 365, // 1 year
        ]);

        return $periods[$action] ?? $periods['default'];
    }

    /**
     * Get the compliance note for a specific action and legal basis.
     *
     * @param  string  $action
     * @param  string  $legalBasis
     * @return string
     */
    protected function getComplianceNote(string $action, string $legalBasis): string
    {
        $notes = [
            'consent_updated' => 'Art. 7 GDPR - Conditions for consent',
            'data_exported' => 'Art. 20 GDPR - Right to data portability',
            'processing_restriction_requested' => 'Art. 18 GDPR - Right to restriction of processing',
            'account_deletion_requested' => 'Art. 17 GDPR - Right to erasure',
            'breach_report_submitted' => 'Art. 33 GDPR - Notification of a personal data breach',
            'personal_data_updated' => 'Art. 16 GDPR - Right to rectification',
            'data_access' => 'Art. 15 GDPR - Right of access',
        ];

        return $notes[$action] ?? 'Art. 5 GDPR - Principles relating to processing of personal data';
    }

    /**
     * Get audit logs for a user.
     *
     * @param  int|null  $userId
     * @param  string|null  $actionType
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserLogs(?int $userId = null, ?string $actionType = null, int $limit = 50)
    {
        $query = GdprAuditLog::query();

        // Apply user filter if specified
        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        // Apply activity type filter if specified
        if ($actionType !== null) {
            $query->where('action', $actionType);
        }

        // Order by timestamp and limit results
        return $query->orderBy('timestamp', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Log consent updated activity.
     *
     * @param  int  $consentId
     * @param  array  $changes
     * @param  int|null  $userId
     * @return \App\Models\GdprAuditLog|null
     */
    public function logConsentUpdated(int $consentId, array $changes, ?int $userId = null): ?GdprAuditLog
    {
        return $this->log(
            'consent_updated',
            'consent',
            [
                'consent_id' => $consentId,
                'changes' => $changes
            ],
            $userId,
            'Art. 7 GDPR - Conditions for consent'
        );
    }

    /**
     * Log data export requested activity.
     *
     * @param  int  $exportId
     * @param  array  $exportDetails
     * @param  int|null  $userId
     * @return \App\Models\GdprAuditLog|null
     */
    public function logDataExportRequested(int $exportId, array $exportDetails, ?int $userId = null): ?GdprAuditLog
    {
        return $this->log(
            'data_export_requested',
            'user_request',
            [
                'export_id' => $exportId,
                'format' => $exportDetails['format'] ?? null,
                'data_categories' => $exportDetails['data_categories'] ?? []
            ],
            $userId,
            'Art. 20 GDPR - Right to data portability'
        );
    }

    /**
     * Log data export completed activity.
     *
     * @param  int  $exportId
     * @param  array  $completionDetails
     * @param  int|null  $userId
     * @return \App\Models\GdprAuditLog|null
     */
    public function logDataExportCompleted(int $exportId, array $completionDetails, ?int $userId = null): ?GdprAuditLog
    {
        return $this->log(
            'data_export_completed',
            'user_request',
            [
                'export_id' => $exportId,
                'file_size' => $completionDetails['file_size'] ?? null,
                'completion_time' => $completionDetails['completion_time'] ?? null
            ],
            $userId,
            'Art. 20 GDPR - Right to data portability'
        );
    }

    /**
     * Log processing restriction requested activity.
     *
     * @param  int  $restrictionId
     * @param  array  $restrictionDetails
     * @param  int|null  $userId
     * @return \App\Models\GdprAuditLog|null
     */
    public function logProcessingRestrictionRequested(int $restrictionId, array $restrictionDetails, ?int $userId = null): ?GdprAuditLog
    {
        return $this->log(
            'processing_restriction_requested',
            'user_request',
            [
                'restriction_id' => $restrictionId,
                'restriction_type' => $restrictionDetails['restriction_type'] ?? null,
                'restriction_reason' => $restrictionDetails['restriction_reason'] ?? null,
                'data_categories' => $restrictionDetails['data_categories'] ?? []
            ],
            $userId,
            'Art. 18 GDPR - Right to restriction of processing'
        );
    }

    /**
     * Log breach report submitted activity.
     *
     * @param  int  $reportId
     * @param  array  $reportDetails
     * @param  int|null  $userId
     * @return \App\Models\GdprAuditLog|null
     */
    public function logBreachReportSubmitted(int $reportId, array $reportDetails, ?int $userId = null): ?GdprAuditLog
    {
        return $this->log(
            'breach_report_submitted',
            'legal_obligation',
            [
                'report_id' => $reportId,
                'incident_date' => $reportDetails['incident_date'] ?? null,
                'affected_data' => $reportDetails['affected_data'] ?? null
            ],
            $userId,
            'Art. 33 GDPR - Notification of a personal data breach'
        );
    }

    /**
     * Log account deletion requested activity.
     *
     * @param  int  $requestId
     * @param  array  $requestDetails
     * @param  int|null  $userId
     * @return \App\Models\GdprAuditLog|null
     */
    public function logAccountDeletionRequested(int $requestId, array $requestDetails, ?int $userId = null): ?GdprAuditLog
    {
        return $this->log(
            'account_deletion_requested',
            'user_request',
            [
                'request_id' => $requestId,
                'reason' => $requestDetails['reason'] ?? null,
                'scheduled_deletion_date' => $requestDetails['scheduled_deletion_date'] ?? null
            ],
            $userId,
            'Art. 17 GDPR - Right to erasure'
        );
    }

    /**
     * Log personal data updated activity.
     *
     * @param  array  $updatedFields
     * @param  int|null  $userId
     * @return \App\Models\GdprAuditLog|null
     */
    public function logPersonalDataUpdated(array $updatedFields, ?int $userId = null): ?GdprAuditLog
    {
        // Only log the field names that were updated, not their values
        return $this->log(
            'personal_data_updated',
            'user_request',
            [
                'updated_fields' => array_keys($updatedFields)
            ],
            $userId,
            'Art. 16 GDPR - Right to rectification'
        );
    }

    /**
     * Log data access activity.
     *
     * @param  string  $dataType
     * @param  int  $dataId
     * @param  string  $accessType
     * @param  int|null  $userId
     * @return \App\Models\GdprAuditLog|null
     */
    public function logDataAccess(string $dataType, int $dataId, string $accessType, ?int $userId = null): ?GdprAuditLog
    {
        return $this->log(
            'data_access',
            'user_request',
            [
                'data_type' => $dataType,
                'data_id' => $dataId,
                'access_type' => $accessType
            ],
            $userId,
            'Art. 15 GDPR - Right of access'
        );
    }

    /**
     * Verify the integrity of audit logs.
     *
     * @param  int  $limit
     * @return array
     */
    public function verifyLogsIntegrity(int $limit = 1000): array
    {
        $logs = GdprAuditLog::orderBy('id', 'desc')
            ->limit($limit)
            ->get();

        $results = [
            'checked' => 0,
            'valid' => 0,
            'invalid' => 0,
            'invalid_logs' => []
        ];

        foreach ($logs as $log) {
            $results['checked']++;

            // Recreate the hash from the stored data
            $recordData = json_encode([
                'user_id' => $log->user_id,
                'action' => $log->action,
                'legal_basis' => $log->legal_basis,
                'details' => $log->details,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'timestamp' => $log->timestamp->toIso8601String(),
            ]);

            $calculatedHash = hash('sha256', $recordData);

            if ($calculatedHash === $log->record_hash) {
                $results['valid']++;
            } else {
                $results['invalid']++;
                $log->is_verified = false;
                $log->save();

                $results['invalid_logs'][] = [
                    'id' => $log->id,
                    'action' => $log->action,
                    'timestamp' => $log->timestamp->toIso8601String()
                ];
            }
        }

        return $results;
    }

    /**
     * Get system-wide GDPR activity statistics.
     *
     * @param  int  $days
     * @return array
     */
    public function getActivityStatistics(int $days = 30): array
    {
        try {
            $startDate = now()->subDays($days);

            // Get activity counts by type
            $activityCounts = GdprAuditLog::where('timestamp', '>=', $startDate)
                ->selectRaw('action, COUNT(*) as count')
                ->groupBy('action')
                ->pluck('count', 'action')
                ->toArray();

            // Get legal basis distribution
            $legalBasisCounts = GdprAuditLog::where('timestamp', '>=', $startDate)
                ->selectRaw('legal_basis, COUNT(*) as count')
                ->groupBy('legal_basis')
                ->pluck('count', 'legal_basis')
                ->toArray();

            // Get unique users
            $uniqueUsers = GdprAuditLog::where('timestamp', '>=', $startDate)
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->count('user_id');

            // Get daily activity counts
            $dailyActivity = GdprAuditLog::where('timestamp', '>=', $startDate)
                ->selectRaw('DATE(timestamp) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();

            // Check for tampered records
            $tamperedCount = GdprAuditLog::where('is_verified', false)->count();

            return [
                'period_days' => $days,
                'total_activities' => array_sum($activityCounts),
                'unique_users' => $uniqueUsers,
                'activities_by_type' => $activityCounts,
                'legal_basis_distribution' => $legalBasisCounts,
                'daily_activity' => $dailyActivity,
                'tampered_records' => $tamperedCount,
                'earliest_retention' => GdprAuditLog::min('retention_until')
            ];
        } catch (\Throwable $e) {
            $this->errorManager->handle('GDPR_AUDIT_STATS_ERROR', [
                'days' => $days,
                'error' => $e->getMessage()
            ], $e);

            return [
                'period_days' => $days,
                'total_activities' => 0,
                'unique_users' => 0,
                'activities_by_type' => [],
                'legal_basis_distribution' => [],
                'daily_activity' => [],
                'tampered_records' => 0,
                'earliest_retention' => null
            ];
        }
    }

    /**
     * Find logs that have passed their retention period.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findExpiredLogs(int $limit = 1000)
    {
        return GdprAuditLog::where('retention_until', '<', now())
            ->limit($limit)
            ->get();
    }

    /**
     * Delete logs that have passed their retention period.
     *
     * @param int $limit
     * @return int Number of logs deleted
     */
    public function purgeExpiredLogs(int $limit = 1000): int
    {
        try {
            $expiredLogs = $this->findExpiredLogs($limit);
            $count = $expiredLogs->count();

            foreach ($expiredLogs as $log) {
                $log->delete();
            }

            // Log the purge operation itself
            $this->log(
                'audit_logs_purged',
                'legal_obligation',
                [
                    'count' => $count,
                    'max_retention_date' => now()->toDateTimeString()
                ],
                null,
                'Art. 5(1)(e) GDPR - Storage limitation principle'
            );

            return $count;
        } catch (\Throwable $e) {
            $this->errorManager->handle('GDPR_AUDIT_PURGE_ERROR', [
                'limit' => $limit,
                'error' => $e->getMessage()
            ], $e);

            return 0;
        }
    }
}
