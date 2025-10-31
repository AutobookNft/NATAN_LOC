<?php

namespace App\Services\Gdpr;

use App\Models\User;
use App\Models\GdprRequest;
use App\Models\BreachReport;
use App\Models\PrivacyPolicy;
use App\Models\DpoMessage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Ultra\UltraLogManager\UltraLogManager;
use Ultra\ErrorManager\Interfaces\ErrorManagerInterface;
use Carbon\Carbon;

/**
 * @Oracode Service: GDPR Compliance Management
 * 🎯 Purpose: Core business logic for GDPR data subject rights
 * 🛡️ Privacy: Handles all GDPR operations with full compliance
 * 🧱 Core Logic: Orchestrates data management, requests, and compliance
 *
 * @package App\Services
 * @author Padmin D. Curtis (for Fabio Cherici)
 * @version 1.0.0
 * @date 2025-05-22
 */
class GdprService
{
    /**
     * Logger instance for audit trail
     * @var UltraLogManager
     */
    protected UltraLogManager $logger;

    /**
     * Error manager for robust error handling
     * @var UltraErrorManager
     */
    protected ErrorManagerInterface $errorManager;

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
    }

    /**
     * Get editable user fields with GDPR compliance info
     *
     * @return array
     * @privacy-safe Returns field configuration only
     */
    public function getEditableUserFields(): array
    {
        return [
            'name' => [
                'type' => 'string',
                'max_length' => 255,
                'required' => true,
                'gdpr_category' => 'identity',
                'retention_period' => 'until_deletion'
            ],
            'email' => [
                'type' => 'email',
                'max_length' => 255,
                'required' => true,
                'gdpr_category' => 'contact',
                'retention_period' => 'until_deletion',
                'validation' => 'unique'
            ],
            'bio' => [
                'type' => 'text',
                'max_length' => 1000,
                'required' => false,
                'gdpr_category' => 'profile',
                'retention_period' => 'until_deletion'
            ],
            'notification_preferences' => [
                'type' => 'json',
                'required' => false,
                'gdpr_category' => 'preferences',
                'retention_period' => 'until_deletion'
            ]
        ];
    }

    /**
     * Get summary of on-chain data for user
     *
     * @param User $user
     * @return array
     * @privacy-safe Returns summary of immutable data
     */
    public function getOnChainDataSummary(User $user): array
    {
        try {
            $this->logger->info('GDPR Service: Getting on-chain data summary', [
                'user_id' => $user->id,
                'log_category' => 'GDPR_SERVICE_OPERATION'
            ]);

            return [
                'wallet_address' => $user->wallet_address ?? 'Not connected',
                'nft_collections' => $user->collections()->count(),
                'blockchain_transactions' => $this->getBlockchainTransactionCount($user),
                'smart_contracts' => $this->getSmartContractInteractions($user),
                'immutable_note' => 'Data stored on blockchain cannot be deleted, only made inaccessible from this platform',
                'last_blockchain_activity' => $this->getLastBlockchainActivity($user)
            ];

        } catch (\Exception $e) {
            $this->logger->error('GDPR Service: Failed to get on-chain data summary', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'log_category' => 'GDPR_SERVICE_ERROR'
            ]);

            throw $e;
        }
    }

    /**
     * Update user personal data with change tracking
     *
     * @param User $user
     * @param array $data
     * @return array
     * @privacy-safe Updates only provided user's data
     */
    public function updateUserPersonalData(User $user, array $data): array
    {
        try {
            $this->logger->info('GDPR Service: Updating user personal data', [
                'user_id' => $user->id,
                'fields' => array_keys($data),
                'log_category' => 'GDPR_SERVICE_OPERATION'
            ]);

            $originalData = $user->only(array_keys($data));
            $changes = [];
            $previous = [];

            DB::transaction(function () use ($user, $data, &$changes, &$previous) {
                foreach ($data as $field => $value) {
                    if ($user->{$field} !== $value) {
                        $previous[$field] = $user->{$field};
                        $changes[$field] = $value;
                        $user->{$field} = $value;
                    }
                }

                if (!empty($changes)) {
                    $user->save();

                    // Create audit record
                    GdprRequest::create([
                        'user_id' => $user->id,
                        'type' => 'data_update',
                        'status' => 'completed',
                        'request_data' => [
                            'fields_changed' => array_keys($changes),
                            'previous_values' => $previous,
                            'new_values' => $changes
                        ],
                        'processed_at' => now()
                    ]);
                }
            });

            return [
                'changes' => $changes,
                'previous' => $previous,
                'updated_fields' => array_keys($changes)
            ];

        } catch (\Exception $e) {
            $this->logger->error('GDPR Service: Failed to update personal data', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'log_category' => 'GDPR_SERVICE_ERROR'
            ]);

            throw $e;
        }
    }

    /**
     * Create rectification request for incorrect data
     *
     * @param User $user
     * @param array $requestData
     * @return GdprRequest
     * @privacy-safe Creates request for authenticated user only
     */
    public function createRectificationRequest(User $user, array $requestData): GdprRequest
    {
        try {
            $this->logger->info('GDPR Service: Creating rectification request', [
                'user_id' => $user->id,
                'field_name' => $requestData['field_name'],
                'log_category' => 'GDPR_SERVICE_OPERATION'
            ]);

            return GdprRequest::create([
                'user_id' => $user->id,
                'type' => 'rectification',
                'status' => 'pending',
                'request_data' => [
                    'field_name' => $requestData['field_name'],
                    'current_value' => $requestData['current_value'],
                    'requested_value' => $requestData['requested_value'],
                    'reason' => $requestData['reason'],
                    'submitted_at' => now()->toISOString()
                ],
                'notes' => 'Rectification request submitted by user'
            ]);

        } catch (\Exception $e) {
            $this->logger->error('GDPR Service: Failed to create rectification request', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'log_category' => 'GDPR_SERVICE_ERROR'
            ]);

            throw $e;
        }
    }

    /**
     * Get available processing types for limitation
     *
     * @return array
     * @privacy-safe Returns processing type definitions
     */
    public function getProcessingTypes(): array
    {
        return [
            'marketing' => [
                'name' => 'Marketing Communications',
                'description' => 'Use of data for promotional emails, newsletters, and marketing campaigns',
                'legal_basis' => 'consent',
                'can_limit' => true
            ],
            'profiling' => [
                'name' => 'Automated Profiling',
                'description' => 'Automated analysis to create user profiles for recommendations',
                'legal_basis' => 'legitimate_interest',
                'can_limit' => true
            ],
            'analytics' => [
                'name' => 'Analytics and Statistics',
                'description' => 'Analysis of usage patterns and platform statistics',
                'legal_basis' => 'legitimate_interest',
                'can_limit' => true
            ],
            'automated_decisions' => [
                'name' => 'Automated Decision Making',
                'description' => 'Automated systems that make decisions affecting you',
                'legal_basis' => 'consent',
                'can_limit' => true
            ],
            'functional' => [
                'name' => 'Essential Platform Functions',
                'description' => 'Core platform operations required for service delivery',
                'legal_basis' => 'contract',
                'can_limit' => false
            ]
        ];
    }

    /**
     * Get user's current processing limitations
     *
     * @param User $user
     * @return array
     * @privacy-safe Returns user's own limitations only
     */
    public function getUserProcessingLimitations(User $user): array
    {
        try {
            $limitations = $user->processingLimitations ?? [];

            return [
                'marketing' => $limitations['marketing'] ?? false,
                'profiling' => $limitations['profiling'] ?? false,
                'analytics' => $limitations['analytics'] ?? false,
                'automated_decisions' => $limitations['automated_decisions'] ?? false,
                'effective_date' => $user->limitations_updated_at,
                'last_updated' => $user->updated_at
            ];

        } catch (\Exception $e) {
            $this->logger->error('GDPR Service: Failed to get processing limitations', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'log_category' => 'GDPR_SERVICE_ERROR'
            ]);

            throw $e;
        }
    }

    /**
     * Update user's processing limitations
     *
     * @param User $user
     * @param array $limitations
     * @return array
     * @privacy-safe Updates limitations for authenticated user only
     */
    public function updateProcessingLimitations(User $user, array $limitations): array
    {
        try {
            $this->logger->info('GDPR Service: Updating processing limitations', [
                'user_id' => $user->id,
                'limitations' => $limitations,
                'log_category' => 'GDPR_SERVICE_OPERATION'
            ]);

            $previousLimitations = $user->processingLimitations ?? [];

            DB::transaction(function () use ($user, $limitations) {
                $user->processingLimitations = $limitations;
                $user->limitations_updated_at = now();
                $user->save();

                // Create audit record
                GdprRequest::create([
                    'user_id' => $user->id,
                    'type' => 'processing_limitation',
                    'status' => 'completed',
                    'request_data' => [
                        'limitations' => $limitations,
                        'effective_date' => now()->toISOString()
                    ],
                    'processed_at' => now()
                ]);
            });

            return [
                'previous' => $previousLimitations,
                'current' => $limitations,
                'effective_date' => now()
            ];

        } catch (\Exception $e) {
            $this->logger->error('GDPR Service: Failed to update processing limitations', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'log_category' => 'GDPR_SERVICE_ERROR'
            ]);

            throw $e;
        }
    }

    /**
     * Get deletion information for user
     *
     * @param User $user
     * @return array
     * @privacy-safe Returns deletion info for authenticated user
     */
    public function getDeletionInfo(User $user): array
    {
        try {
            $this->logger->info('GDPR Service: Getting deletion info', [
                'user_id' => $user->id,
                'log_category' => 'GDPR_SERVICE_OPERATION'
            ]);

            return [
                'deletable_data' => [
                    'profile_data' => 'Name, email, bio, preferences',
                    'activity_logs' => 'Platform usage history',
                    'uploaded_content' => 'Non-blockchain content',
                    'consents' => 'Cookie and privacy preferences'
                ],
                'retained_data' => [
                    'legal_obligations' => 'Transaction records (7 years)',
                    'blockchain_data' => 'NFT ownership records (immutable)',
                    'anonymized_analytics' => 'Aggregated usage statistics'
                ],
                'deletion_timeline' => [
                    'immediate' => 'Platform access revoked',
                    '24_hours' => 'Personal data anonymized',
                    '30_days' => 'Backup systems updated',
                    '7_years' => 'Legal retention period expires'
                ],
                'consequences' => [
                    'platform_access' => 'Permanent loss of account access',
                    'nft_collections' => 'Collections remain on blockchain but inaccessible via platform',
                    'recovery' => 'Account cannot be recovered after deletion'
                ]
            ];

        } catch (\Exception $e) {
            $this->logger->error('GDPR Service: Failed to get deletion info', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'log_category' => 'GDPR_SERVICE_ERROR'
            ]);

            throw $e;
        }
    }

    /**
     * Create account deletion request
     *
     * @param User $user
     * @param array $requestData
     * @return GdprRequest
     * @privacy-safe Creates deletion request for authenticated user
     */
    public function createDeletionRequest(User $user, array $requestData): GdprRequest
    {
        try {
            $this->logger->warning('GDPR Service: Creating deletion request', [
                'user_id' => $user->id,
                'reason' => $requestData['reason'] ?? 'No reason provided',
                'log_category' => 'GDPR_SERVICE_OPERATION'
            ]);

            return GdprRequest::create([
                'user_id' => $user->id,
                'type' => 'deletion',
                'status' => 'pending',
                'request_data' => [
                    'reason' => $requestData['reason'] ?? null,
                    'acknowledge_onchain' => $requestData['acknowledge_onchain'],
                    'requested_at' => now()->toISOString(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ],
                'notes' => 'Account deletion request - requires manual review'
            ]);

        } catch (\Exception $e) {
            $this->logger->error('GDPR Service: Failed to create deletion request', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'log_category' => 'GDPR_SERVICE_ERROR'
            ]);

            throw $e;
        }
    }

    /**
     * Execute account deletion (irreversible)
     *
     * @param User $user
     * @return array
     * @privacy-safe Deletes only authenticated user's account
     */
    public function executeAccountDeletion(User $user): array
    {
        try {
            $userId = $user->id;
            $userEmail = $user->email;

            $this->logger->critical('GDPR Service: Executing account deletion', [
                'user_id' => $userId,
                'user_email' => $userEmail,
                'log_category' => 'GDPR_SERVICE_CRITICAL'
            ]);

            $deletionResults = [];

            DB::transaction(function () use ($user, &$deletionResults) {
                // 1. Anonymize personal data
                $user->update([
                    'name' => 'Deleted User',
                    'email' => 'deleted_' . $user->id . '@deleted.local',
                    'bio' => null,
                    'avatar' => null,
                    'phone' => null,
                    'deleted_at' => now()
                ]);
                $deletionResults['personal_data'] = 'anonymized';

                // 2. Delete consents
                $user->consents()->delete();
                $deletionResults['consents'] = 'deleted';

                // 3. Delete processing limitations
                $user->update(['processingLimitations' => null]);
                $deletionResults['processing_limits'] = 'deleted';

                // 4. Mark collections as orphaned (blockchain data remains)
                $collectionsCount = $user->collections()->count();
                $user->collections()->update(['user_id' => null, 'status' => 'orphaned']);
                $deletionResults['collections'] = "orphaned: {$collectionsCount}";

                // 5. Delete activity logs older than legal requirement
                $user->activityLogs()->where('created_at', '<', now()->subYears(7))->delete();
                $deletionResults['activity_logs'] = 'legal_retention_applied';

                // 6. Create final audit record
                GdprRequest::create([
                    'user_id' => $user->id,
                    'type' => 'deletion_executed',
                    'status' => 'completed',
                    'request_data' => [
                        'deletion_timestamp' => now()->toISOString(),
                        'deletion_results' => $deletionResults
                    ],
                    'processed_at' => now()
                ]);

                // 7. Soft delete the user
                $user->delete();
                $deletionResults['account_status'] = 'deleted';
            });

            return $deletionResults;

        } catch (\Exception $e) {
            $this->logger->critical('GDPR Service: Account deletion failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'log_category' => 'GDPR_SERVICE_CRITICAL_ERROR'
            ]);

            throw $e;
        }
    }

    /**
     * Get user's breach reports
     *
     * @param User $user
     * @return Collection
     * @privacy-safe Returns user's own reports only
     */
    public function getUserBreachReports(User $user): Collection
    {
        return $user->breachReports()->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get available breach report categories
     *
     * @return array
     * @privacy-safe Returns category definitions
     */
    public function getBreachReportCategories(): array
    {
        return [
            'data_leak' => [
                'name' => 'Data Leak',
                'description' => 'Unauthorized disclosure of personal data',
                'severity_levels' => ['medium', 'high', 'critical']
            ],
            'unauthorized_access' => [
                'name' => 'Unauthorized Access',
                'description' => 'Someone accessed your account without permission',
                'severity_levels' => ['high', 'critical']
            ],
            'system_breach' => [
                'name' => 'System Security Breach',
                'description' => 'Platform security vulnerability or breach',
                'severity_levels' => ['high', 'critical']
            ],
            'phishing' => [
                'name' => 'Phishing Attack',
                'description' => 'Attempted fraudulent communication',
                'severity_levels' => ['low', 'medium', 'high']
            ],
            'other' => [
                'name' => 'Other Security Concern',
                'description' => 'Any other privacy or security related issue',
                'severity_levels' => ['low', 'medium', 'high', 'critical']
            ]
        ];
    }

    /**
     * Create breach report
     *
     * @param User $user
     * @param array $reportData
     * @return BreachReport
     * @privacy-safe Creates report for authenticated user
     */
    public function createBreachReport(User $user, array $reportData): BreachReport
    {
        try {
            $this->logger->warning('GDPR Service: Creating breach report', [
                'user_id' => $user->id,
                'category' => $reportData['category'],
                'severity' => $reportData['severity'],
                'log_category' => 'GDPR_SERVICE_BREACH'
            ]);

            return BreachReport::create([
                'user_id' => $user->id,
                'category' => $reportData['category'],
                'severity' => $reportData['severity'],
                'description' => $reportData['description'],
                'incident_date' => $reportData['incident_date'] ?? now(),
                'affected_data' => $reportData['affected_data'] ?? [],
                'status' => 'reported',
                'report_data' => [
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'submitted_at' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            $this->logger->error('GDPR Service: Failed to create breach report', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'log_category' => 'GDPR_SERVICE_ERROR'
            ]);

            throw $e;
        }
    }

    /**
     * Get current privacy policy
     *
     * @return PrivacyPolicy
     * @privacy-safe Returns public policy information
     */
    public function getCurrentPrivacyPolicy(): PrivacyPolicy
    {
        return PrivacyPolicy::where('is_active', true)->latest('version')->first()
            ?? new PrivacyPolicy(['version' => '1.0', 'content' => 'Policy pending...']);
    }

    /**
     * Get privacy policy versions
     *
     * @return Collection
     * @privacy-safe Returns public policy version history
     */
    public function getPrivacyPolicyVersions(): Collection
    {
        return PrivacyPolicy::orderBy('version', 'desc')->get();
    }

    /**
     * Get data processing information
     *
     * @return array
     * @privacy-safe Returns public processing information
     */
    public function getDataProcessingInformation(): array
    {
        return [
            'data_controller' => [
                'name' => config('app.name'),
                'address' => config('gdpr.controller_address'),
                'email' => config('gdpr.controller_email'),
                'dpo_email' => config('gdpr.dpo_email')
            ],
            'processing_purposes' => [
                'service_delivery' => [
                    'purpose' => 'Platform operation and service delivery',
                    'legal_basis' => 'Contract performance',
                    'data_types' => ['Identity', 'Contact', 'Technical'],
                    'retention' => 'Until account deletion'
                ],
                'communication' => [
                    'purpose' => 'Customer communication and support',
                    'legal_basis' => 'Legitimate interest',
                    'data_types' => ['Contact', 'Communication'],
                    'retention' => '3 years after last contact'
                ],
                'analytics' => [
                    'purpose' => 'Service improvement and analytics',
                    'legal_basis' => 'Legitimate interest',
                    'data_types' => ['Usage', 'Technical'],
                    'retention' => '2 years'
                ]
            ],
            'data_categories' => [
                'identity' => 'Name, username, unique identifiers',
                'contact' => 'Email address, postal address, phone number',
                'technical' => 'IP address, browser data, device information',
                'usage' => 'Platform interactions, preferences, history',
                'blockchain' => 'Wallet addresses, transaction data (immutable)'
            ]
        ];
    }

    /**
     * Get third-party services information
     *
     * @return array
     * @privacy-safe Returns public third-party information
     */
    public function getThirdPartyServices(): array
    {
        return [
            'analytics' => [
                'name' => 'Analytics Provider',
                'purpose' => 'Website analytics and performance monitoring',
                'data_shared' => ['Usage data', 'Technical data'],
                'privacy_policy' => 'https://analytics-provider.com/privacy'
            ],
            'payment' => [
                'name' => 'Payment Processor',
                'purpose' => 'Payment processing and fraud prevention',
                'data_shared' => ['Transaction data', 'Contact data'],
                'privacy_policy' => 'https://payment-processor.com/privacy'
            ],
            'blockchain' => [
                'name' => 'Algorand Network',
                'purpose' => 'NFT creation and blockchain transactions',
                'data_shared' => ['Wallet addresses', 'Transaction data'],
                'privacy_policy' => 'https://algorand.foundation/privacy'
            ]
        ];
    }

    /**
     * Get DPO contact information
     *
     * @return array
     * @privacy-safe Returns public DPO contact information
     */
    public function getDpoContactInformation(): array
    {
        return [
            'name' => config('gdpr.dpo_name', 'Data Protection Officer'),
            'email' => config('gdpr.dpo_email', 'dpo@' . config('app.domain')),
            'phone' => config('gdpr.dpo_phone'),
            'address' => config('gdpr.dpo_address'),
            'response_time' => config('gdpr.response_time', '72 hours'),
            'languages' => config('gdpr.supported_languages', ['en', 'it']),
            'office_hours' => config('gdpr.office_hours', 'Monday-Friday 9:00-17:00 CET')
        ];
    }

    /**
     * Get user's DPO messages
     *
     * @param User $user
     * @return Collection
     * @privacy-safe Returns user's own messages only
     */
    public function getUserDpoMessages(User $user): Collection
    {
        return $user->dpoMessages()->orderBy('created_at', 'desc')->get();
    }

    /**
     * Send message to DPO
     *
     * @param User $user
     * @param array $messageData
     * @return DpoMessage
     * @privacy-safe Creates message for authenticated user
     */
    public function sendMessageToDpo(User $user, array $messageData): DpoMessage
    {
        try {
            $this->logger->info('GDPR Service: Sending message to DPO', [
                'user_id' => $user->id,
                'subject' => $messageData['subject'],
                'priority' => $messageData['priority'],
                'log_category' => 'GDPR_SERVICE_DPO'
            ]);

            return DpoMessage::create([
                'user_id' => $user->id,
                'subject' => $messageData['subject'],
                'message' => $messageData['message'],
                'priority' => $messageData['priority'],
                'request_type' => $messageData['request_type'],
                'status' => 'sent',
                'metadata' => [
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'submitted_at' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            $this->logger->error('GDPR Service: Failed to send DPO message', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'log_category' => 'GDPR_SERVICE_ERROR'
            ]);

            throw $e;
        }
    }

    // ===================================================================
    // PRIVATE HELPER METHODS
    // ===================================================================

    /**
     * Get blockchain transaction count for user
     *
     * @param User $user
     * @return int
     * @privacy-safe Counts user's blockchain interactions
     */
    private function getBlockchainTransactionCount(User $user): int
    {
        // Implementation depends on your blockchain integration
        return $user->blockchainTransactions()->count() ?? 0;
    }

    /**
     * Get smart contract interactions count
     *
     * @param User $user
     * @return int
     * @privacy-safe Counts user's smart contract interactions
     */
    private function getSmartContractInteractions(User $user): int
    {
        // Implementation depends on your smart contract integration
        return $user->smartContractInteractions()->count() ?? 0;
    }

    /**
     * Get last blockchain activity timestamp
     *
     * @param User $user
     * @return Carbon|null
     * @privacy-safe Returns user's last blockchain activity
     */
    private function getLastBlockchainActivity(User $user): ?Carbon
    {
        // Implementation depends on your blockchain integration
        return $user->blockchainTransactions()->latest()->first()?->created_at;
    }
}
