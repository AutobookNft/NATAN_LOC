<?php

namespace App\Services\Gdpr;

use App\Models\User;
use App\Models\DataExport;
use App\Models\Collection;
use App\Models\UserConsent;
use App\Models\UserActivity;
use App\Jobs\ProcessDataExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Ultra\UltraLogManager\UltraLogManager;
use Ultra\ErrorManager\Interfaces\ErrorManagerInterface;
use Carbon\Carbon;
use RuntimeException;
use InvalidArgumentException;

/**
 * @Oracode Service: Data Export Management
 * 🎯 Purpose: Handles GDPR data portability with multiple formats
 * 🛡️ Privacy: Exports only user's own data with full audit trail
 * 🧱 Core Logic: Generates, stores, and delivers user data exports
 *
 * @package App\Services
 * @author Padmin D. Curtis (for Fabio Cherici)
 * @version 1.0.0
 * @date 2025-05-22
 */
class DataExportService {
    /**
     * Logger instance for audit trail
     * @var UltraLogManager
     */
    protected UltraLogManager $logger;

    /**
     * Error manager for robust error handling
     * @var ErrorManagerInterface
     */
    protected ErrorManagerInterface $errorManager;

    /**
     * Maximum export file size in bytes (50MB)
     * @var int
     */
    protected int $maxExportSize = 52428800;

    /**
     * Export retention period in days
     * @var int
     */
    protected int $retentionDays = 30;

    /**
     * Whether to use queue for processing exports
     * @var bool
     */
    protected bool $useQueue = false;

    /**
     * Available data categories for export
     * @var array
     */
    protected array $dataCategories = [
        'profile' => [
            'name' => 'Profile Information',
            'description' => 'Basic profile data, settings, and preferences',
            'includes' => ['name', 'email', 'bio', 'avatar', 'settings']
        ],
        'account' => [
            'name' => 'Account Information',
            'description' => 'Core account data and settings',
            'includes' => ['email', 'verification', 'account_status', 'metadata']
        ],
        'preferences' => [
            'name' => 'User Preferences',
            'description' => 'UI preferences and platform settings',
            'includes' => ['language', 'timezone', 'notifications', 'privacy_settings']
        ],
        'activity' => [
            'name' => 'Activity History',
            'description' => 'Platform usage, logins, and interactions',
            'includes' => ['login_history', 'page_views', 'actions', 'sessions']
        ],
        'activities' => [
            'name' => 'Legacy Activity History',
            'description' => 'Legacy platform usage data',
            'includes' => ['login_history', 'page_views', 'actions', 'sessions']
        ],
        'collections' => [
            'name' => 'Collections and NFTs',
            'description' => 'Created collections, NFT metadata, and ownership',
            'includes' => ['collections', 'nfts', 'metadata', 'ownership_history']
        ],
        'wallet' => [
            'name' => 'Wallet and Transactions',
            'description' => 'Wallet connections and transaction history',
            'includes' => ['wallet_address', 'transactions', 'blockchain_data']
        ],
        'consents' => [
            'name' => 'Privacy Consents',
            'description' => 'Consent history and privacy preferences',
            'includes' => ['consent_history', 'privacy_settings', 'cookie_preferences']
        ],
        'purchases' => [
            'name' => 'Purchase History',
            'description' => 'Purchase and transaction history',
            'includes' => ['orders', 'payments', 'invoices', 'refunds']
        ],
        'comments' => [
            'name' => 'Comments and Reviews',
            'description' => 'User comments and review data',
            'includes' => ['comments', 'reviews', 'ratings']
        ],
        'messages' => [
            'name' => 'Messages and Communications',
            'description' => 'User messages and communication history',
            'includes' => ['conversations', 'sent_messages', 'received_messages']
        ],
        'biography' => [
            'name' => 'Biography Data',
            'description' => 'User biographies and personal stories',
            'includes' => ['biographies', 'chapters', 'media', 'timeline']
        ],
        'audit' => [
            'name' => 'Audit Trail',
            'description' => 'Security logs and account changes',
            'includes' => ['security_events', 'account_changes', 'gdpr_requests']
        ]
    ];

    /**
     * Supported export formats
     * @var array
     */
    protected array $supportedFormats = [
        'json' => [
            'name' => 'JSON',
            'description' => 'Machine-readable structured data format',
            'mime_type' => 'application/json',
            'extension' => 'json'
        ],
        'csv' => [
            'name' => 'CSV',
            'description' => 'Spreadsheet-compatible comma-separated values',
            'mime_type' => 'text/csv',
            'extension' => 'csv'
        ],
        'pdf' => [
            'name' => 'PDF',
            'description' => 'Human-readable document format',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf'
        ]
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

        // Configure queue usage based on environment
        $this->useQueue = config('gdpr.export.use_queue', false);
    }

    /**
     * Get user's export history
     *
     * @param User $user
     * @param int $limit
     * @return \Illuminate\Support\Collection
     * @privacy-safe Returns user's own export history only
     */
    public function getUserExportHistory(User $user, int $limit = 20): \Illuminate\Support\Collection {
        try {
            $this->logger->info('Data Export Service: Getting user export history', [
                'user_id' => $user->id,
                'limit' => $limit,
                'log_category' => 'EXPORT_SERVICE_OPERATION'
            ]);

            // Get exports and transform to array format for view compatibility
            $exports = $user->dataExports()
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            // Transform to array format expected by the view
            return $exports->map(function ($export) {
                return [
                    'id' => $export->id,
                    'token' => $export->token,
                    'format' => $export->format,
                    'categories' => $export->categories,
                    'status' => $export->status,
                    'progress' => $export->progress,
                    'file_path' => $export->file_path,
                    'file_size' => $export->file_size,
                    'download_count' => $export->download_count,
                    'last_downloaded_at' => $export->last_downloaded_at,
                    'expires_at' => $export->expires_at,
                    'completed_at' => $export->completed_at,
                    'created_at' => $export->created_at,
                    'updated_at' => $export->updated_at,
                    'error_message' => $export->error_message,
                    'metadata' => $export->metadata,
                    'is_expired' => $export->isExpired(),
                    'is_ready' => $export->isReady(),
                    'formatted_file_size' => $export->getFormattedFileSizeAttribute(),
                    'status_name' => $export->getStatusNameAttribute(),
                    'format_name' => $export->getFormatNameAttribute(),
                ];
            });
        } catch (\Exception $e) {
            $this->errorManager->handle('GDPR_EXPORT_HISTORY_FAILED', [
                'user_id' => $user->id,
                'limit' => $limit,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ], $e);

            // Return empty collection on error to maintain return type
            return new \Illuminate\Support\Collection();
        }
    }

    /**
     * Get supported export formats
     *
     * @return array
     * @privacy-safe Returns public format definitions
     */
    public function getSupportedFormats(): array {
        return $this->supportedFormats;
    }

    /**
     * Set queue mode for export processing
     *
     * @param bool $useQueue
     * @return self
     * @privacy-safe Controls processing mode
     */
    public function setQueueMode(bool $useQueue): self {
        $this->useQueue = $useQueue;
        return $this;
    }

    /**
     * Generate user data export
     *
     * @param User $user
     * @param string $format
     * @param array $categories
     * @return string Export token
     * @privacy-safe Generates export for authenticated user only
     */
    public function generateUserDataExport(User $user, string $format, array $categories): string {
        try {
            $this->logger->info('Data Export Service: Generating user data export', [
                'user_id' => $user->id,
                'format' => $format,
                'categories' => implode(', ', $categories),
                'log_category' => 'EXPORT_SERVICE_OPERATION'
            ]);

            // Validate format
            if (!isset($this->supportedFormats[$format])) {
                $this->errorManager->handle('GDPR_EXPORT_INVALID_FORMAT', [
                    'user_id' => $user->id,
                    'format' => $format,
                    'supported_formats' => implode(', ', array_keys($this->supportedFormats))
                ]);
                return '';
            }

            // Validate categories
            $invalidCategories = array_diff($categories, array_keys($this->dataCategories));
            if (!empty($invalidCategories)) {
                // Log detailed info but pass minimal data to error manager
                $this->logger->error('GDPR Export: Invalid categories detected', [
                    'user_id' => $user->id,
                    'invalid_categories' => implode(', ', $invalidCategories),
                    'valid_categories' => implode(', ', array_keys($this->dataCategories)),
                    'requested_categories' => implode(', ', $categories)
                ]);

                $this->errorManager->handle('GDPR_EXPORT_INVALID_CATEGORIES', [
                    'user_id' => (string)$user->id,
                    'error_message' => 'Invalid categories requested'
                ]);
                return '';
            }            // Check for existing pending exports
            $pendingExport = $user->dataExports()
                ->whereIn('status', ['pending', 'processing'])
                ->first();

            if ($pendingExport) {
                $this->logger->warning('Data Export Service: User has pending export', [
                    'user_id' => $user->id,
                    'existing_export_id' => $pendingExport->id,
                    'log_category' => 'EXPORT_SERVICE_WARNING'
                ]);
                return $pendingExport->token;
            }

            // Create export record
            $token = Str::random(64);
            $export = DataExport::create([
                'user_id' => $user->id,
                'token' => $token,
                'format' => $format,
                'categories' => $categories,
                'status' => 'pending',
                'progress' => 0,
                'expires_at' => now()->addDays($this->retentionDays),
                'metadata' => [
                    'requested_at' => now()->toISOString(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'session_id' => session()->getId()
                ]
            ]);

            if ($this->useQueue) {
                // 🎯 QUEUE MODE: Process export asynchronously using job queue
                $this->logger->info('Dispatching export to queue for background processing', [
                    'export_id' => $export->id,
                    'user_id' => $user->id,
                    'log_category' => 'EXPORT_SERVICE_QUEUE'
                ]);

                ProcessDataExport::dispatch($export);
            } else {
                // 🎯 SYNC MODE: Process export immediately for development
                $this->logger->info('Processing export synchronously (development mode)', [
                    'export_id' => $export->id,
                    'user_id' => $user->id,
                    'log_category' => 'EXPORT_SERVICE_SYNC'
                ]);

                $success = $this->processDataExport($export);

                if (!$success) {
                    $this->logger->error('Synchronous export processing failed', [
                        'export_id' => $export->id,
                        'user_id' => $user->id,
                        'log_category' => 'EXPORT_SERVICE_SYNC_FAILED'
                    ]);
                    return '';
                }
            }

            return $token;
        } catch (\Exception $e) {
            $this->errorManager->handle('GDPR_EXPORT_GENERATION_FAILED', [
                'user_id' => $user->id,
                'format' => $format,
                'categories' => implode(', ', $categories),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ], $e);

            // Return empty token to indicate failure
            return '';
        }
    }

    /**
     * Process data export (called by queue job)
     *
     * @param DataExport $export
     * @return bool
     * @privacy-safe Processes export for specified user only
     */
    public function processDataExport(DataExport $export): bool {
        try {
            $this->logger->info('Data Export Service: Processing data export', [
                'export_id' => $export->id,
                'user_id' => $export->user_id,
                'format' => $export->format,
                'log_category' => 'EXPORT_SERVICE_PROCESSING'
            ]);

            $export->update(['status' => 'processing', 'progress' => 0]);

            // Collect data
            $userData = $this->collectUserData($export->user, $export->categories, $export);

            // Generate file
            $filePath = $this->generateExportFile($userData, $export);

            // Update export record
            $fileSize = Storage::disk('public')->size($filePath);
            $export->update([
                'status' => 'completed',
                'progress' => 100,
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'completed_at' => now()
            ]);

            $this->logger->info('Data Export Service: Export completed successfully', [
                'export_id' => $export->id,
                'user_id' => $export->user_id,
                'file_size' => $fileSize,
                'log_category' => 'EXPORT_SERVICE_SUCCESS'
            ]);

            return true;
        } catch (\Exception $e) {
            $export->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);

            $this->errorManager->handle('GDPR_EXPORT_PROCESSING_FAILED', [
                'export_id' => $export->id,
                'user_id' => $export->user_id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ], $e);

            return false;
        }
    }

    /**
     * Get export by token
     *
     * @param string $token
     * @param User $user
     * @return DataExport|null
     * @privacy-safe Returns export only if it belongs to user
     */
    public function getExportByToken(string $token, User $user): ?DataExport {
        return $user->dataExports()->where('token', $token)->first();
    }

    /**
     * Stream export file for download
     *
     * @param DataExport $export
     * @return StreamedResponse
     * @privacy-safe Streams file only for authorized user
     */
    public function streamExportFile(DataExport $export): StreamedResponse {
        try {
            $this->logger->info('Data Export Service: Streaming export file', [
                'export_id' => $export->id,
                'user_id' => $export->user_id,
                'log_category' => 'EXPORT_SERVICE_DOWNLOAD'
            ]);

            if ($export->status !== 'completed') {
                $this->errorManager->handle('GDPR_EXPORT_NOT_READY', [
                    'export_id' => $export->id,
                    'user_id' => $export->user_id,
                    'status' => $export->status
                ]);
                abort(422, __('gdpr.export.export_not_ready'));
            }

            if ($export->expires_at < now()) {
                $this->errorManager->handle('GDPR_EXPORT_EXPIRED', [
                    'export_id' => $export->id,
                    'user_id' => $export->user_id,
                    'expired_at' => $export->expires_at
                ]);
                abort(410, __('gdpr.export.export_expired'));
            }

            if (!Storage::disk('public')->exists($export->file_path)) {
                $this->errorManager->handle('GDPR_EXPORT_FILE_NOT_FOUND', [
                    'export_id' => $export->id,
                    'user_id' => $export->user_id,
                    'file_path' => $export->file_path
                ]);
                abort(404, __('gdpr.export.export_file_not_found'));
            }

            // Debug: Log file details before download
            $fileSize = Storage::disk('public')->size($export->file_path);
            $this->logger->info('Preparing file download', [
                'export_id' => $export->id,
                'file_path' => $export->file_path,
                'file_size' => $fileSize,
                'format' => $export->format,
                'log_category' => 'EXPORT_SERVICE_DOWNLOAD'
            ]);

            // Update download count
            $export->increment('download_count');
            $export->update(['last_downloaded_at' => now()]);

            $formatConfig = $this->supportedFormats[$export->format];

            // 🎯 FIX: Per i CSV usiamo ZIP come estensione dato che generiamo un archivio
            $actualExtension = $export->format === 'csv' ? 'zip' : $formatConfig['extension'];
            $actualMimeType = $export->format === 'csv' ? 'application/zip' : $formatConfig['mime_type'];
            $filename = "florence_egi_data_export_{$export->user_id}_{$export->created_at->format('Y-m-d')}.{$actualExtension}";

            $this->logger->info('Starting file download', [
                'export_id' => $export->id,
                'actual_extension' => $actualExtension,
                'actual_mime_type' => $actualMimeType,
                'download_filename' => $filename,
                'storage_file_path' => $export->file_path,
                'log_category' => 'EXPORT_SERVICE_DOWNLOAD'
            ]);

            // 🎯 FIX: Use Laravel's storage download with proper headers
            $fullPath = Storage::disk('public')->path($export->file_path);

            $this->logger->info('File full path for download', [
                'export_id' => $export->id,
                'file_path' => $export->file_path,
                'full_path' => $fullPath,
                'file_exists' => file_exists($fullPath),
                'file_size' => file_exists($fullPath) ? filesize($fullPath) : 'N/A',
                'log_category' => 'EXPORT_SERVICE_DOWNLOAD'
            ]);

            // Verify file exists
            if (!file_exists($fullPath)) {
                $this->errorManager->handle('GDPR_EXPORT_FILE_NOT_FOUND_ON_DISK', [
                    'export_id' => $export->id,
                    'user_id' => $export->user_id,
                    'file_path' => $export->file_path,
                    'full_path' => $fullPath
                ]);
                abort(404, __('gdpr.export.export_file_not_found'));
            }

            // Get file size
            $fileSize = filesize($fullPath);

            $this->logger->info('Starting file stream download', [
                'export_id' => $export->id,
                'filename' => $filename,
                'mime_type' => $actualMimeType,
                'file_size' => $fileSize,
                'log_category' => 'EXPORT_SERVICE_DOWNLOAD'
            ]);

            // Return response with proper streaming using readfile
            $response = response()->stream(function () use ($fullPath) {
                // Set output buffering and ensure clean output
                if (ob_get_level()) {
                    ob_end_clean();
                }

                $file = fopen($fullPath, 'rb');
                if ($file === false) {
                    throw new \RuntimeException('Cannot open file for reading');
                }

                // Use fpassthru for efficient file streaming
                fpassthru($file);
                fclose($file);
            }, 200, [
                'Content-Type' => $actualMimeType,
                'Content-Disposition' => 'attachment; filename="' . addslashes($filename) . '"',
                'Content-Length' => $fileSize,
                'Content-Transfer-Encoding' => 'binary',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
                'Accept-Ranges' => 'bytes'
            ]);

            // Log successful preparation
            $this->logger->info('File stream response prepared successfully', [
                'export_id' => $export->id,
                'response_headers' => $response->headers->all(),
                'log_category' => 'EXPORT_SERVICE_DOWNLOAD'
            ]);

            return $response;
        } catch (\Exception $e) {
            $this->errorManager->handle('GDPR_EXPORT_DOWNLOAD_FAILED', [
                'export_id' => $export->id,
                'user_id' => $export->user_id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ], $e);

            abort(500, __('gdpr.export.download_error'));
        }
    }

    /**
     * Clean expired exports
     *
     * @return int Number of cleaned exports
     * @privacy-safe Cleans only expired exports
     */
    public function cleanExpiredExports(): int {
        try {
            $this->logger->info('Data Export Service: Cleaning expired exports', [
                'log_category' => 'EXPORT_SERVICE_MAINTENANCE'
            ]);

            $expiredExports = DataExport::where('expires_at', '<', now())
                ->where('status', 'completed')
                ->get();

            $cleanedCount = 0;
            foreach ($expiredExports as $export) {
                if ($export->file_path && Storage::disk('public')->exists($export->file_path)) {
                    Storage::disk('public')->delete($export->file_path);
                }
                $export->update(['status' => 'expired', 'file_path' => null]);
                $cleanedCount++;
            }

            $this->logger->info('Data Export Service: Expired exports cleaned', [
                'cleaned_count' => $cleanedCount,
                'log_category' => 'EXPORT_SERVICE_MAINTENANCE'
            ]);

            return $cleanedCount;
        } catch (\Exception $e) {
            $this->errorManager->handle('GDPR_EXPORT_CLEANUP_FAILED', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ], $e);

            return 0;
        }
    }

    // ===================================================================
    // PRIVATE HELPER METHODS
    // ===================================================================

    /**
     * Collect user data for export
     *
     * @param User $user
     * @param array $categories
     * @param DataExport $export
     * @return array
     * @privacy-safe Collects data for specified user only
     */
    private function collectUserData(User $user, array $categories, DataExport $export): array {
        $data = [
            'export_info' => [
                'generated_at' => now()->toISOString(),
                'user_id' => $user->id,
                'categories' => $categories,
                'format' => $export->format,
                'florence_egi_version' => config('app.version', '1.0.0')
            ]
        ];

        // 🔥 USA LE CATEGORIE DEFINITE IN QUESTA CLASSE
        $validCategories = array_keys($this->dataCategories);
        $categoriesToProcess = array_intersect($categories, $validCategories);

        // Debug log per vedere cosa succede
        $this->logger->info('Processing export categories', [
            'requested_categories' => $categories,
            'valid_categories' => $validCategories,
            'categories_to_process' => $categoriesToProcess,
            'log_category' => 'EXPORT_SERVICE_DEBUG'
        ]);

        $totalCategories = count($categoriesToProcess);
        $currentCategory = 0;

        foreach ($categoriesToProcess as $category) {
            $this->updateExportProgress($export, ($currentCategory / $totalCategories) * 80);

            switch ($category) {
                case 'profile':
                    $data['profile'] = $this->collectProfileData($user);
                    break;
                case 'account':
                    $data['account'] = $this->collectAccountData($user);
                    break;
                case 'preferences':
                    $data['preferences'] = $this->collectPreferencesData($user);
                    break;
                case 'activity':
                    $data['activity'] = $this->collectActivityData($user);
                    break;
                case 'consents':
                    $data['consents'] = $this->collectConsentData($user);
                    break;
                case 'collections':
                    $data['collections'] = $this->collectCollectionData($user);
                    break;
                case 'purchases':
                    $data['purchases'] = $this->collectPurchasesData($user);
                    break;
                case 'comments':
                    $data['comments'] = $this->collectCommentsData($user);
                    break;
                case 'messages':
                    $data['messages'] = $this->collectMessagesData($user);
                    break;
                case 'biography':
                    $data['biography'] = $this->collectBiographyData($user);
                    break;
                // Mantieni anche i metodi esistenti se ci sono
                case 'wallet':
                    $data['wallet'] = $this->collectWalletData($user);
                    break;
                case 'audit':
                    $data['audit'] = $this->collectAuditData($user);
                    break;
                default:
                    // Log unknown category
                    $this->logger->warning('Unknown export category requested', [
                        'category' => $category,
                        'user_id' => $user->id
                    ]);
                    break;
            }

            $currentCategory++;
        }
        return $data;
    }

    /**
     * Collect profile data
     *
     * @param User $user
     * @return array
     * @privacy-safe Returns user's profile data only
     */
    private function collectProfileData(User $user): array {
        return [
            'basic_info' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'bio' => $user->bio,
                'created_at' => $user->created_at->toISOString(),
                'updated_at' => $user->updated_at->toISOString()
            ],
            'settings' => [
                'notification_preferences' => $user->notification_preferences ?? [],
                'privacy_settings' => $user->privacy_settings ?? [],
                'language' => $user->language ?? 'en',
                'timezone' => $user->timezone ?? 'UTC'
            ],
            'verification_status' => [
                'email_verified' => !is_null($user->email_verified_at),
                'email_verified_at' => $user->email_verified_at?->toISOString()
            ]
        ];
    }

    /**
     * Collect activity data
     *
     * @param User $user
     * @return array
     * @privacy-safe Returns user's activity data only
     */
    private function collectActivityData(User $user): array {
        return [
            'login_history' => $user->loginHistory()
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->get()
                ->map(function ($login) {
                    return [
                        'timestamp' => $login->created_at->toISOString(),
                        'ip_address' => $this->maskIpAddress($login->ip_address),
                        'user_agent' => $login->user_agent,
                        'success' => true, // Assume successful if logged in user_activities
                        'action' => $login->action ?? 'login'
                    ];
                })->toArray(),
            'platform_usage' => [
                'total_logins' => $user->loginHistory()->count(), // All login records are successful
                'last_login' => $user->last_login_at?->toISOString(),
                'account_age_days' => $user->created_at->diffInDays(now())
            ]
        ];
    }

    /**
     * Collect collection data
     *
     * @param User $user
     * @return array
     * @privacy-safe Returns user's collection data only
     */
    private function collectCollectionData(User $user): array {
        return [
            'owned_collections' => $user->collections()
                ->with('egis')
                ->get()
                ->map(function ($collection) {
                    return [
                        'id' => $collection->id,
                        'name' => $collection->name,
                        'description' => $collection->description,
                        'created_at' => $collection->created_at->toISOString(),
                        'egi_count' => $collection->egis->count(),
                        'status' => $collection->status,
                        'blockchain_data' => [
                            'contract_address' => $collection->contract_address,
                            'blockchain_id' => $collection->blockchain_id
                        ]
                    ];
                })->toArray(),
            'collaboration_collections' => $user->collaborations()
                ->get()
                ->map(function ($collection) {
                    return [
                        'id' => $collection->id,
                        'name' => $collection->name,
                        'role' => $collection->pivot->role,
                        'joined_at' => $collection->pivot->created_at->toISOString()
                    ];
                })->toArray()
        ];
    }

    /**
     * Collect wallet data
     *
     * @param User $user
     * @return array
     * @privacy-safe Returns user's wallet data only
     */
    private function collectWalletData(User $user): array {
        return [
            'wallet_connections' => [
                'primary_wallet' => $user->wallet_address,
                'connected_at' => $user->wallet_connected_at?->toISOString(),
                'wallet_type' => $user->wallet_type
            ],
            'blockchain_activity' => [
                'total_transactions' => $user->blockchainTransactions()->count(),
                'last_transaction' => $user->blockchainTransactions()
                    ->latest()
                    ->first()?->created_at?->toISOString()
            ]
        ];
    }

    /**
     * Collect consent data
     *
     * @param User $user
     * @return array
     * @privacy-safe Returns user's consent data only
     */
    private function collectConsentData(User $user): array {
        return [
            'current_consents' => $user->consent_summary ?? [],
            'consent_history' => $user->consents()
                ->with('consentVersion')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($consent) {
                    return [
                        'consent_type' => $consent->consent_type,
                        'granted' => $consent->granted,
                        'timestamp' => $consent->created_at->toISOString(),
                        'version' => $consent->consentVersion?->version,
                        'legal_basis' => $consent->legal_basis,
                        'withdrawal_method' => $consent->withdrawal_method
                    ];
                })->toArray(),
            'last_updated' => $user->consents_updated_at?->toISOString()
        ];
    }

    /**
     * Collect audit data
     *
     * @param User $user
     * @return array
     * @privacy-safe Returns user's audit data only
     */
    private function collectAuditData(User $user): array {
        return [
            'gdpr_requests' => $user->gdprRequests()
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($request) {
                    return [
                        'type' => $request->type,
                        'status' => $request->status,
                        'created_at' => $request->created_at->toISOString(),
                        'processed_at' => $request->processed_at?->toISOString(),
                        'notes' => $request->notes
                    ];
                })->toArray(),
            'security_events' => $user->securityEvents()
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->map(function ($event) {
                    return [
                        'type' => $event->type,
                        'description' => $event->description,
                        'timestamp' => $event->created_at->toISOString(),
                        'ip_address' => $this->maskIpAddress($event->ip_address)
                    ];
                })->toArray()
        ];
    }

    /**
     * Generate export file in requested format
     *
     * @param array $data
     * @param DataExport $export
     * @return string File path
     * @privacy-safe Generates file for specified export only
     */
    private function generateExportFile(array $data, DataExport $export): string {
        $this->updateExportProgress($export, 90);

        $fileName = "export_{$export->token}_{$export->format}";

        switch ($export->format) {
            case 'json':
                return $this->generateJsonFile($data, $fileName);
            case 'csv':
                return $this->generateCsvFile($data, $fileName);
            case 'pdf':
                return $this->generatePdfFile($data, $fileName);
            default:
                $this->errorManager->handle('GDPR_EXPORT_INVALID_FORMAT', [
                    'export_id' => $export->id,
                    'format' => $export->format,
                    'supported_formats' => implode(', ', array_keys($this->supportedFormats))
                ]);
                return '';
        }
    }

    /**
     * Generate JSON export file
     *
     * @param array $data
     * @param string $fileName
     * @return string
     * @privacy-safe Generates JSON file with user data
     */
    private function generateJsonFile(array $data, string $fileName): string {
        $filePath = $fileName . '.json';
        $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        Storage::disk('public')->put($filePath, $jsonContent);

        return $filePath;
    }

    /**
     * Generate CSV export file
     *
     * @param array $data
     * @param string $fileName
     * @return string
     * @privacy-safe Generates CSV file with user data
     */
    private function generateCsvFile(array $data, string $fileName): string {
        $zipFileName = $fileName . '.zip';

        // Create temporary files directory
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempZipPath = $tempDir . '/' . $zipFileName;

        // Delete existing file if present
        if (file_exists($tempZipPath)) {
            unlink($tempZipPath);
        }

        // Create ZIP archive
        $zip = new \ZipArchive();
        $result = $zip->open($tempZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        if ($result !== TRUE) {
            throw new \RuntimeException("Cannot create ZIP file: " . $this->getZipError($result));
        }

        // Add CSV files to ZIP
        $fileCount = 0;
        foreach ($data as $category => $categoryData) {
            if ($category === 'export_info') continue;

            // Flatten and convert to CSV
            $csvData = $this->flattenForCsv($categoryData);
            $csvContent = $this->arrayToCsv($csvData);

            // Validate CSV content
            if (empty($csvContent) || strlen($csvContent) < 10) {
                $this->logger->warning("Empty CSV content for category: {$category}", [
                    'log_category' => 'EXPORT_SERVICE_CSV_GENERATION'
                ]);
                continue;
            }

            // Add file to ZIP
            $csvFileName = $category . '.csv';
            if ($zip->addFromString($csvFileName, $csvContent) === FALSE) {
                $this->logger->error("Failed to add {$csvFileName} to ZIP", [
                    'log_category' => 'EXPORT_SERVICE_CSV_GENERATION'
                ]);
                continue;
            }

            $fileCount++;

            $this->logger->info("Added CSV to ZIP", [
                'category' => $category,
                'csv_size' => strlen($csvContent),
                'has_bom' => substr($csvContent, 0, 3) === "\xEF\xBB\xBF",
                'has_semicolon' => strpos($csvContent, ';') !== false,
                'log_category' => 'EXPORT_SERVICE_CSV_GENERATION'
            ]);
        }

        // Close ZIP
        $closeResult = $zip->close();
        if ($closeResult === FALSE) {
            throw new \RuntimeException("Failed to close ZIP archive");
        }

        // Verify ZIP was created
        if (!file_exists($tempZipPath) || filesize($tempZipPath) === 0) {
            throw new \RuntimeException("ZIP file was not created or is empty");
        }

        $this->logger->info("ZIP created successfully", [
            'path' => $tempZipPath,
            'size' => filesize($tempZipPath),
            'files_added' => $fileCount,
            'log_category' => 'EXPORT_SERVICE_CSV_GENERATION'
        ]);

        // Move to Laravel storage
        try {
            // Read the file content and store via Laravel
            $zipContent = file_get_contents($tempZipPath);

            $this->logger->info("Reading ZIP file for storage", [
                'temp_path' => $tempZipPath,
                'content_size' => strlen($zipContent),
                'target_file' => $zipFileName,
                'log_category' => 'EXPORT_SERVICE_CSV_GENERATION'
            ]);

            if (strlen($zipContent) === 0) {
                throw new \RuntimeException('ZIP file content is empty');
            }

            Storage::disk('public')->put($zipFileName, $zipContent);

            // Verify the file was stored correctly
            if (!Storage::disk('public')->exists($zipFileName)) {
                throw new \RuntimeException('ZIP file was not stored in public storage');
            }

            $storedSize = Storage::disk('public')->size($zipFileName);
            $this->logger->info("ZIP file stored successfully", [
                'stored_path' => $zipFileName,
                'stored_size' => $storedSize,
                'original_size' => strlen($zipContent),
                'log_category' => 'EXPORT_SERVICE_CSV_GENERATION'
            ]);

            // Clean up temp file
            unlink($tempZipPath);

            return $zipFileName;
        } catch (\Exception $e) {
            $this->logger->error("Failed to store ZIP file", [
                'error' => $e->getMessage(),
                'temp_path' => $tempZipPath,
                'target_file' => $zipFileName,
                'log_category' => 'EXPORT_SERVICE_CSV_GENERATION'
            ]);

            // Clean up on error
            if (file_exists($tempZipPath)) {
                unlink($tempZipPath);
            }
            throw new \RuntimeException('Failed to store ZIP file: ' . $e->getMessage());
        }
    }

    /**
     * Get human-readable ZIP error message
     */
    private function getZipError(int $code): string {
        switch ($code) {
            case \ZipArchive::ER_INVAL:
                return 'Invalid argument';
            case \ZipArchive::ER_NOENT:
                return 'No such file';
            case \ZipArchive::ER_MEMORY:
                return 'Memory allocation failure';
            case \ZipArchive::ER_TMPOPEN:
                return 'Can\'t create temp file';
            default:
                return "Unknown error (code: {$code})";
        }
    }

    /**
     * Generate PDF export file
     *
     * @param array $data
     * @param string $fileName
     * @return string
     * @privacy-safe Generates PDF file with user data
     */
    private function generatePdfFile(array $data, string $fileName): string {
        try {
            // Try DomPDF first
            $html = $this->arrayToHtml($data);

            // Set memory limit for PDF generation
            ini_set('memory_limit', '512M');

            // Create PDF using DomPDF with timeout protection
            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');

            // Set DomPDF options
            $pdf->getDomPDF()->getOptions()->set([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => false,
                'isRemoteEnabled' => false,
                'defaultFont' => 'Arial'
            ]);

            $filePath = $fileName . '.pdf';
            $pdfContent = $pdf->output();

            // Verify PDF content is valid
            if (substr($pdfContent, 0, 4) !== '%PDF') {
                throw new \Exception('Generated content is not a valid PDF');
            }

            Storage::disk('public')->put($filePath, $pdfContent);

            return $filePath;
        } catch (\Exception $e) {
            // Log the specific error
            $this->logger->error('PDF generation failed, falling back to HTML', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            // Fallback to HTML file if DomPDF fails
            $filePath = $fileName . '.html';
            $htmlContent = $this->arrayToHtml($data);
            Storage::disk('public')->put($filePath, $htmlContent);

            return $filePath;
        }
    }

    /**
     * Update export progress
     *
     * @param DataExport $export
     * @param int $progress
     * @return void
     * @privacy-safe Updates progress for specified export
     */
    private function updateExportProgress(DataExport $export, int $progress): void {
        $export->update(['progress' => min(100, max(0, $progress))]);
    }

    /**
     * Flatten nested data structures for CSV export
     *
     * @param array $data
     * @return array
     * @privacy-safe Flattens data structure for CSV compatibility
     */
    private function flattenForCsv(array $data): array {
        $flattened = [];

        foreach ($data as $key => $value) {
            if (is_array($value) && !empty($value)) {
                // Check if this is a list of similar records (like login_history, owned_collections)
                $first = reset($value);
                if (is_array($first) && $this->isAssociativeArray($first)) {
                    // This is a list of records - add them directly
                    foreach ($value as $record) {
                        if (is_array($record)) {
                            $flattened[] = $record;
                        }
                    }
                } else {
                    // This is a single record or simple values
                    if ($this->isAssociativeArray($value)) {
                        $flattened[] = $value;
                    } else {
                        // Convert simple array to key-value
                        $flattened[] = [$key => is_array($value) ? json_encode($value) : $value];
                    }
                }
            } else {
                // Single value - wrap in array with key
                $flattened[] = [$key => is_array($value) ? json_encode($value) : $value];
            }
        }

        // If we have no flattened data, return the original structure
        if (empty($flattened)) {
            // If original data is already a simple list of associative arrays, return as-is
            if (!empty($data)) {
                $first = reset($data);
                if (is_array($first) && $this->isAssociativeArray($first)) {
                    return $data;
                }
            }
            return $data;
        }

        return $flattened;
    }

    /**
     * Convert array to CSV format
     *
     * @param array $data
     * @return string
     * @privacy-safe Converts data to CSV format
     */
    private function arrayToCsv(array $data): string {
        $output = fopen('php://temp', 'r+');

        // Per massima compatibilità Excel: UTF-8 con BOM e separatore semicolon
        fwrite($output, "\xEF\xBB\xBF");

        if (is_array($data) && !empty($data)) {
            $first = reset($data);
            if (is_array($first) && !empty($first)) {
                // Write headers
                $keys = array_keys($first);
                if (!empty($keys)) {
                    $headers = array_map(function ($header) {
                        return $this->sanitizeForCsv($header);
                    }, $keys);

                    // Use semicolon separator for Excel European format
                    fputcsv($output, $headers, ';', '"');

                    // Write data rows
                    foreach ($data as $row) {
                        if (!is_array($row)) {
                            // If row is not an array, convert to simple key-value
                            $row = ['value' => $row];
                        }
                        // Convert all array values to strings and sanitize
                        $csvRow = array_map(function ($value) {
                            if (is_array($value)) {
                                return $this->sanitizeForCsv(json_encode($value, JSON_UNESCAPED_UNICODE));
                            }
                            return $this->sanitizeForCsv((string)$value);
                        }, $row);
                        fputcsv($output, $csvRow, ';', '"');
                    }
                }
            } else {
                // Simple key-value pairs
                fputcsv($output, ['Key', 'Value'], ';', '"');
                foreach ($data as $key => $value) {
                    $sanitizedKey = $this->sanitizeForCsv((string)$key);
                    $sanitizedValue = is_array($value)
                        ? $this->sanitizeForCsv(json_encode($value, JSON_UNESCAPED_UNICODE))
                        : $this->sanitizeForCsv((string)$value);
                    fputcsv($output, [$sanitizedKey, $sanitizedValue], ';', '"');
                }
            }
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Sanitize data for CSV output
     *
     * @param string $value
     * @return string
     * @privacy-safe Cleans data for CSV format
     */
    private function sanitizeForCsv(string $value): string {
        // Convert HTML entities to readable text first
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Strip HTML tags but keep the content
        $value = strip_tags($value);

        // Remove any control characters and non-printable characters
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\x9F]/', '', $value);

        // Replace problematic characters that Excel doesn't handle well
        $value = str_replace(['"', "'", '`', '´'], ['"', "'", "'", "'"], $value);

        // Replace multiple whitespaces with single space
        $value = preg_replace('/\s+/', ' ', $value);

        // Trim whitespace
        $value = trim($value);

        // For Excel compatibility: convert special characters to ASCII equivalents
        $value = $this->convertSpecialCharsForExcel($value);

        // Limit length to prevent extremely long cells
        if (strlen($value) > 1000) {
            $value = substr($value, 0, 997) . '...';
        }

        return $value;
    }

    /**
     * Convert special characters to Excel-friendly equivalents
     *
     * @param string $value
     * @return string
     */
    private function convertSpecialCharsForExcel(string $value): string {
        // Common character replacements for Excel compatibility
        $replacements = [
            // Quotes and apostrophes (using proper escaping)
            '"' => '"',
            '"' => '"',
            "'" => "'",
            "'" => "'",

            // Dashes and hyphens
            '–' => '-',
            '—' => '-',

            // Dots and ellipsis
            '…' => '...',

            // Mathematical symbols
            '×' => 'x',
            '÷' => '/',

            // Currency and symbols
            '€' => 'EUR',
            '£' => 'GBP',
            '¥' => 'JPY',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $value);
    }

    /**
     * Convert array to text format
     *
     * @param array $data
     * @return string
     * @privacy-safe Converts data to text format
     */
    /**
     * Convert array to HTML format for PDF generation
     *
     * @param array $data
     * @return string
     * @privacy-safe Converts data to HTML format
     */
    private function arrayToHtml(array $data): string {
        $html = '<!DOCTYPE html><html><head>';
        $html .= '<meta charset="UTF-8">';
        $html .= '<title>FlorenceEGI Data Export</title>';
        $html .= '<style>';
        $html .= 'body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }';
        $html .= 'h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }';
        $html .= 'h2 { color: #666; margin-top: 40px; margin-bottom: 20px; border-left: 4px solid #007bff; padding-left: 15px; }';
        $html .= 'table { width: 100%; border-collapse: collapse; margin: 15px 0; }';
        $html .= 'th, td { border: 1px solid #ddd; padding: 12px 8px; text-align: left; vertical-align: top; }';
        $html .= 'th { background-color: #f8f9fa; font-weight: bold; }';
        $html .= 'tr:nth-child(even) { background-color: #f9f9f9; }';
        $html .= 'tr:hover { background-color: #f5f5f5; }';
        $html .= 'dl { margin: 10px 0; }';
        $html .= 'dt { font-weight: bold; margin-top: 15px; color: #495057; }';
        $html .= 'dd { margin-left: 20px; margin-bottom: 10px; }';
        $html .= 'pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-wrap: break-word; font-size: 12px; }';
        $html .= '.nested-table { margin: 5px 0; }';
        $html .= '.nested-table th, .nested-table td { padding: 6px; font-size: 14px; }';
        // Card styles for wide data
        $html .= '.card-container { display: block; margin: 15px 0; }';
        $html .= '.data-card { border: 1px solid #ddd; margin-bottom: 15px; border-radius: 8px; overflow: hidden; }';
        $html .= '.card-header { background-color: #007bff; color: white; padding: 12px; font-weight: bold; }';
        $html .= '.card-body { padding: 15px; }';
        $html .= '.card-row { margin-bottom: 8px; display: block; }';
        $html .= '.card-label { font-weight: bold; color: #495057; display: inline-block; min-width: 120px; margin-right: 10px; }';
        $html .= '.card-value { display: inline-block; word-wrap: break-word; max-width: calc(100% - 130px); vertical-align: top; }';
        $html .= '</style>';
        $html .= '</head><body>';

        $html .= '<h1>FlorenceEGI - Esportazione Dati Personali</h1>';
        $html .= '<p><strong>Data di generazione:</strong> ' . now()->format('d/m/Y H:i:s') . '</p>';
        $html .= '<p><strong>Formato:</strong> PDF Export</p>';

        foreach ($data as $section => $sectionData) {
            if ($section === 'export_info') continue; // Skip technical info

            $html .= '<h2>' . ucfirst(str_replace('_', ' ', $section)) . '</h2>';
            $html .= $this->arrayToHtmlRecursive($sectionData);
        }

        $html .= '<div style="margin-top: 50px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666;">';
        $html .= '<p><em>Questo documento è stato generato automaticamente in conformità al Regolamento UE 2016/679 (GDPR) - Diritto alla portabilità dei dati (Art. 20).</em></p>';
        $html .= '</div>';

        $html .= '</body></html>';
        return $html;
    }

    /**
     * Recursively convert array to HTML
     *
     * @param mixed $data
     * @return string
     * @privacy-safe Helper for HTML conversion
     */
    private function arrayToHtmlRecursive($data): string {
        if (empty($data)) {
            return '<p><em>No data available</em></p>';
        }

        // Handle non-array data first
        if (!is_array($data)) {
            return '<p>' . htmlspecialchars((string)$data) . '</p>';
        }

        // Now we know $data is an array
        $first = reset($data);

        // Check if it's a list of associative arrays (table format)
        if (is_array($first) && !empty($first) && $this->isAssociativeArray($first)) {
            $columnCount = count(array_keys($first));

            // If too many columns (>4), use card layout instead of table
            if ($columnCount > 4) {
                return $this->renderAsCards($data);
            } else {
                return $this->renderAsTable($data);
            }
        } else {
            // Key-value pairs - render as definition list for better readability
            $html = '<dl style="margin: 10px 0;">';
            foreach ($data as $key => $value) {
                $html .= '<dt style="font-weight: bold; margin-top: 10px;">' .
                    htmlspecialchars(ucfirst(str_replace('_', ' ', $key))) . '</dt>';
                $html .= '<dd style="margin-left: 20px;">';

                if (is_array($value)) {
                    // Recursively render nested arrays
                    $html .= $this->arrayToHtmlRecursive($value);
                } else {
                    $html .= htmlspecialchars((string)$value);
                }
                $html .= '</dd>';
            }
            $html .= '</dl>';
        }

        return $html;
    }

    /**
     * Check if array is associative (has string keys)
     *
     * @param array $array
     * @return bool
     */
    private function isAssociativeArray(array $array): bool {
        if (empty($array)) {
            return false;
        }
        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Render data as table (for 4 or fewer columns)
     *
     * @param array $data
     * @return string
     */
    private function renderAsTable(array $data): string {
        $first = reset($data);
        $html = '<table>';
        $html .= '<thead><tr>';
        foreach (array_keys($first) as $header) {
            $html .= '<th>' . htmlspecialchars(ucfirst(str_replace('_', ' ', $header))) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($data as $row) {
            // Extra safety check: ensure $row is an array
            if (!is_array($row)) {
                continue;
            }
            $html .= '<tr>';
            foreach ($row as $value) {
                $html .= '<td>';
                if (is_array($value)) {
                    // Render nested arrays as nested tables or key-value pairs
                    $html .= $this->arrayToHtmlRecursive($value);
                } else {
                    $html .= htmlspecialchars((string)$value);
                }
                $html .= '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        return $html;
    }

    /**
     * Render data as cards (for more than 4 columns)
     *
     * @param array $data
     * @return string
     */
    private function renderAsCards(array $data): string {
        $html = '<div class="card-container">';
        $cardNumber = 1;

        foreach ($data as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $html .= '<div class="data-card">';
            $html .= '<div class="card-header">Elemento ' . $cardNumber . '</div>';
            $html .= '<div class="card-body">';
            $cardNumber++;

            foreach ($item as $key => $value) {
                $html .= '<div class="card-row">';
                $html .= '<span class="card-label">' . htmlspecialchars(ucfirst(str_replace('_', ' ', $key))) . ':</span>';
                $html .= '<span class="card-value">';

                if (is_array($value)) {
                    $html .= $this->arrayToHtmlRecursive($value);
                } else {
                    $html .= htmlspecialchars((string)$value);
                }

                $html .= '</span>';
                $html .= '</div>';
            }

            $html .= '</div>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    private function arrayToText(array $data): string {
        $text = "FlorenceEGI Data Export\n";
        $text .= "Generated: " . now()->toISOString() . "\n\n";

        foreach ($data as $section => $sectionData) {
            $text .= strtoupper($section) . "\n";
            $text .= str_repeat('=', strlen($section)) . "\n\n";
            $text .= $this->arrayToTextRecursive($sectionData, 0);
            $text .= "\n\n";
        }

        return $text;
    }

    /**
     * Recursively convert array to text
     *
     * @param mixed $data
     * @param int $depth
     * @return string
     * @privacy-safe Helper for text conversion
     */
    private function arrayToTextRecursive($data, int $depth): string {
        $text = '';
        $indent = str_repeat('  ', $depth);

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $text .= $indent . $key . ': ';
                if (is_array($value)) {
                    $text .= "\n" . $this->arrayToTextRecursive($value, $depth + 1);
                } else {
                    $text .= $value . "\n";
                }
            }
        } else {
            $text .= $indent . $data . "\n";
        }

        return $text;
    }

    /**
     * Mask IP address for privacy
     *
     * @param string|null $ipAddress
     * @return string|null
     * @privacy-safe Masks IP address for privacy compliance
     */
    private function maskIpAddress(?string $ipAddress): ?string {
        if (!$ipAddress) {
            return null;
        }

        if (\filter_var($ipAddress, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ipAddress);
            $parts[3] = 'xxx';
            return implode('.', $parts);
        }

        if (\filter_var($ipAddress, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ipAddress);
            $parts[count($parts) - 1] = 'xxxx';
            return implode(':', $parts);
        }

        return 'masked';
    }

    /**
     * Get available data categories
     *
     * @return array
     * @privacy-safe Returns public category definitions
     */
    public function getAvailableDataCategories(): array {
        // 🔥 USA LA CONFIGURAZIONE ESISTENTE invece di $this->dataCategories
        $categories = config('gdpr.export.data_categories', []);

        $result = [];
        foreach ($categories as $key => $translationKey) {
            $result[$key] = [
                'name' => __($translationKey),
                'key' => $key,
                'translation_key' => $translationKey
            ];
        }

        return $result;
    }

    /**
     * @Oracode Collect Account Data
     * 🎯 Purpose: Export core account information and settings
     * 🛡️ Privacy: User's account data only
     *
     * @param User $user
     * @return array
     * @privacy-safe Returns user's account data only
     */
    private function collectAccountData(User $user): array {
        return [
            'account_info' => [
                'user_id' => $user->id,
                'email' => $user->email,
                'email_verified' => !is_null($user->email_verified_at),
                'email_verified_at' => $user->email_verified_at?->toISOString(),
                'account_created' => $user->created_at->toISOString(),
                'last_updated' => $user->updated_at->toISOString(),
                'last_login' => $user->last_login_at?->toISOString()
            ],
            'account_status' => [
                'active' => true, // Se può fare export, è attivo
                'verified' => !is_null($user->email_verified_at),
                'two_factor_enabled' => !is_null($user->two_factor_secret ?? null)
            ],
            'account_metadata' => [
                'timezone' => $user->timezone ?? config('app.timezone'),
                'language' => $user->language ?? 'it',
                'created_via' => $user->created_via ?? 'web'
            ]
        ];
    }

    /**
     * @Oracode Collect User Preferences
     * 🎯 Purpose: Export user preferences and settings
     * 🛡️ Privacy: User's preferences only
     *
     * @param User $user
     * @return array
     * @privacy-safe Returns user's preferences only
     */
    private function collectPreferencesData(User $user): array {
        return [
            'ui_preferences' => [
                'language' => $user->language ?? 'it',
                'timezone' => $user->timezone ?? config('app.timezone'),
                'theme' => $user->theme ?? 'light',
                'notifications_enabled' => $user->notifications_enabled ?? true
            ],
            'privacy_preferences' => [
                'profile_visibility' => $user->profile_visibility ?? 'public',
                'allow_contact' => $user->allow_contact ?? true,
                'show_online_status' => $user->show_online_status ?? true
            ],
            'platform_preferences' => [
                'newsletter_subscribed' => $user->newsletter_subscribed ?? false,
                'marketing_emails' => $user->marketing_emails ?? false,
                'product_updates' => $user->product_updates ?? true
            ],
            'notification_settings' => [
                // Placeholder per future notifiche
                'email_notifications' => $user->email_notifications ?? [],
                'push_notifications' => $user->push_notifications ?? [],
                'sms_notifications' => $user->sms_notifications ?? []
            ]
        ];
    }

    /**
     * @Oracode Collect Purchases Data
     * 🎯 Purpose: Export purchase history and transaction data
     * 🛡️ Privacy: User's purchase data only
     *
     * @param User $user
     * @return array
     * @privacy-safe Returns user's purchase data only
     */
    private function collectPurchasesData(User $user): array {
        // Placeholder per future implementazione e-commerce
        // Al momento FlorenceEGI potrebbe non avere un sistema acquisti completo

        return [
            'purchase_summary' => [
                'total_purchases' => 0,
                'total_spent' => 0,
                'currency' => 'EUR',
                'first_purchase' => null,
                'last_purchase' => null
            ],
            'purchase_history' => [
                // Placeholder per quando implementerete il sistema acquisti
            ],
            'payment_methods' => [
                // Placeholder per metodi di pagamento salvati
            ],
            'invoices' => [
                // Placeholder per fatture
            ],
            'refunds' => [
                // Placeholder per rimborsi
            ],
            'note' => 'Purchase system not yet implemented in FlorenceEGI MVP'
        ];
    }

    /**
     * @Oracode Collect Messages Data
     * 🎯 Purpose: Export user messages and communications
     * 🛡️ Privacy: User's messages only
     *
     * @param User $user
     * @return array
     * @privacy-safe Returns user's messages only
     */
    private function collectMessagesData(User $user): array {
        // Placeholder per future sistema messaggi
        // Al momento FlorenceEGI potrebbe non avere un sistema messaggi

        return [
            'message_summary' => [
                'total_sent' => 0,
                'total_received' => 0,
                'conversations' => 0,
                'first_message' => null,
                'last_message' => null
            ],
            'conversations' => [
                // Placeholder per conversazioni future
            ],
            'sent_messages' => [
                // Placeholder per messaggi inviati
            ],
            'received_messages' => [
                // Placeholder per messaggi ricevuti
            ],
            'message_settings' => [
                'allow_messages' => true,
                'message_privacy' => 'contacts_only'
            ],
            'note' => 'Messaging system not yet implemented in FlorenceEGI MVP'
        ];
    }

    /**
     * @Oracode Collect Comments Data
     * 🎯 Purpose: Export user comments and reviews
     * 🛡️ Privacy: User's comments only
     *
     * @param User $user
     * @return array
     * @privacy-safe Returns user's comments only
     */
    private function collectCommentsData(User $user): array {
        // Placeholder per future sistema commenti

        return [
            'comment_summary' => [
                'total_comments' => 0,
                'total_reviews' => 0,
                'average_rating_given' => null,
                'first_comment' => null,
                'last_comment' => null
            ],
            'comments' => [
                // Placeholder per commenti futuri
            ],
            'reviews' => [
                // Placeholder per recensioni future
            ],
            'comment_settings' => [
                'allow_public_comments' => true,
                'moderate_comments' => false
            ],
            'note' => 'Comment system not yet implemented in FlorenceEGI MVP'
        ];
    }

    /**
     * @Oracode Collect Biography Data with GDPR Compliance
     * 🎯 Purpose: Export complete biography data including chapters and media
     * 🛡️ Privacy: Only user's own biographies with privacy level tracking
     * 🧱 Core Logic: Structured export with timeline integrity and media references
     *
     * @param User $user
     * @return array
     * @privacy-safe Returns user's biography data only
     */
    private function collectBiographyData(User $user): array {
        $biographies = $user->biographies()
            ->with(['chapters' => function ($query) {
                $query->orderBy('sort_order')->orderBy('date_from');
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'summary' => [
                'total_biographies' => $biographies->count(),
                'public_biographies' => $biographies->where('is_public', true)->count(),
                'completed_biographies' => $biographies->where('is_completed', true)->count(),
                'total_chapters' => $biographies->sum(fn($bio) => $bio->chapters->count()),
                'export_date' => now()->toISOString()
            ],
            'biographies' => $biographies->map(function ($biography) {
                return [
                    'biography_info' => [
                        'id' => $biography->id,
                        'type' => $biography->type,
                        'title' => $biography->title,
                        'slug' => $biography->slug,
                        'excerpt' => $biography->excerpt,
                        'is_public' => $biography->is_public,
                        'is_completed' => $biography->is_completed,
                        'created_at' => $biography->created_at->toISOString(),
                        'updated_at' => $biography->updated_at->toISOString()
                    ],
                    'content' => [
                        'main_content' => $biography->content,
                        'content_length' => strlen($biography->content ?? ''),
                        'estimated_reading_time' => $biography->getEstimatedReadingTime()
                    ],
                    'settings' => $biography->settings ?? [],
                    'chapters' => $biography->chapters->map(function ($chapter) {
                        return [
                            'chapter_info' => [
                                'id' => $chapter->id,
                                'title' => $chapter->title,
                                'chapter_type' => $chapter->chapter_type,
                                'slug' => $chapter->slug,
                                'sort_order' => $chapter->sort_order,
                                'is_published' => $chapter->is_published,
                                'is_ongoing' => $chapter->is_ongoing,
                                'created_at' => $chapter->created_at->toISOString(),
                                'updated_at' => $chapter->updated_at->toISOString()
                            ],
                            'content' => [
                                'content' => $chapter->content,
                                'content_length' => strlen($chapter->content),
                                'reading_time' => $chapter->getReadingTime()
                            ],
                            'timeline' => [
                                'date_from' => $chapter->date_from?->toDateString(),
                                'date_to' => $chapter->date_to?->toDateString(),
                                'date_range_display' => $chapter->date_range_display,
                                'duration_formatted' => $chapter->duration_formatted,
                                'is_current_period' => $chapter->isCurrentPeriod()
                            ],
                            'formatting' => $chapter->formatting_data ?? [],
                            'media_info' => [
                                'total_media' => $chapter->getMedia()->count(),
                                'media_collections' => $chapter->getMedia()
                                    ->groupBy('collection_name')
                                    ->map(function ($mediaGroup, $collection) {
                                        return [
                                            'collection' => $collection,
                                            'count' => $mediaGroup->count(),
                                            'files' => $mediaGroup->map(function ($media) {
                                                return [
                                                    'filename' => $media->file_name,
                                                    'mime_type' => $media->mime_type,
                                                    'size' => $media->size,
                                                    'url' => $media->getUrl(),
                                                    'created_at' => $media->created_at->toISOString()
                                                ];
                                            })->toArray()
                                        ];
                                    })->toArray()
                            ]
                        ];
                    })->toArray(),
                    'media_summary' => [
                        'total_media' => $biography->getMedia()->count(),
                        'media_collections' => $biography->getMedia()
                            ->groupBy('collection_name')
                            ->map(function ($mediaGroup, $collection) {
                                return [
                                    'collection' => $collection,
                                    'count' => $mediaGroup->count(),
                                    'total_size' => $mediaGroup->sum('size'),
                                    'files' => $mediaGroup->map(function ($media) {
                                        return [
                                            'filename' => $media->file_name,
                                            'mime_type' => $media->mime_type,
                                            'size' => $media->size,
                                            'url' => $media->getUrl(),
                                            'created_at' => $media->created_at->toISOString()
                                        ];
                                    })->toArray()
                                ];
                            })->toArray()
                    ],
                    'privacy_info' => [
                        'visibility_level' => $biography->is_public ? 'public' : 'private',
                        'published_chapters' => $biography->chapters->where('is_published', true)->count(),
                        'draft_chapters' => $biography->chapters->where('is_published', false)->count(),
                        'gdpr_notes' => 'This biography data is exported under Article 20 GDPR - Right to data portability'
                    ]
                ];
            })->toArray(),
            'export_metadata' => [
                'legal_basis' => 'Article 20 GDPR - Right to data portability',
                'processing_purpose' => 'User data export request',
                'retention_note' => 'This export file will be automatically deleted after 30 days',
                'data_controller' => 'FlorenceEGI S.r.l.',
                'export_format_note' => 'Structured data for portability and interoperability'
            ]
        ];
    }
}
