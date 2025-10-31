<?php

namespace App\Services\Gdpr;

use App\Enums\Gdpr\ConsentStatus;
use App\Enums\Gdpr\ProcessingRestrictionReason;
use App\Enums\Gdpr\ProcessingRestrictionType;
use App\Models\ProcessingRestriction;
use App\Models\User;
use Carbon\Carbon;
use Ultra\ErrorManager\Interfaces\ErrorManagerInterface;
use Ultra\UltraLogManager\UltraLogManager;

/**
 * GDPR Processing Restriction Service
 *
 * Manages user requests to restrict data processing, ensuring compliance
 * with GDPR Art. 18 and maintaining a clear audit trail.
 *
 * @oracode-dimension governance
 * @oracode-value-flow Controls data processing restrictions, logs actions transparently for accountability.
 * @oracode-community-impact Empowers users by enabling their data rights, fostering trust.
 * @oracode-transparency-level High - All restriction lifecycle events are logged; mapping to processing types is configurable.
 * @oracode-sustainability-factor High - Adherence to legal requirements, robust error handling, configurable logic.
 * @oracode-intent Explicitly manages the lifecycle of GDPR processing restrictions, from creation to removal or expiration,
 *                   ensuring all actions are auditable, compliant with configurations, and user notifications are handled.
 * @os1-compliance Full - Adheres to Zero Placeholder, Ciclo Iterativo (assumed complete for this file), Intenzionalità Esplicita,
 *                   Semplicità Potenziante, Coerenza Semantica, Circolarità Virtuosa (via audit logs), Evoluzione Ricorsiva (via error handling).
 */
class ProcessingRestrictionService
{
    protected ErrorManagerInterface $errorManager;
    protected ActivityLogService $activityLogService;
    protected UltraLogManager $logManager;

    /**
     * Create a new service instance.
     *
     * @param  \Ultra\ErrorManager\Interfaces\ErrorManagerInterface  $errorManager
     * @param  \App\Services\Gdpr\ActivityLogService  $activityLogService
     * @param  \Ultra\UltraLogManager\UltraLogManager  $logManager
     * @return void
     */
    public function __construct(
        ErrorManagerInterface $errorManager,
        ActivityLogService $activityLogService,
        UltraLogManager $logManager
    ) {
        $this->errorManager = $errorManager;
        $this->activityLogService = $activityLogService;
        $this->logManager = $logManager;
    }

    /**
     * Get all active processing restrictions for a user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProcessingRestriction>
     * @os1-intent Retrieves all currently active processing restrictions for a given user, ordered by creation date.
     */
    public function getUserActiveRestrictions(User $user)
    {
        return $user->processingRestrictions()
            ->where('status', ConsentStatus::ACTIVE)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Check if the user has reached the maximum allowed active restrictions.
     *
     * @param  \App\Models\User  $user
     * @return bool
     * @os1-intent Determines if a user has hit the configurable limit for active processing restrictions.
     */
    public function hasReachedRestrictionLimit(User $user): bool
    {
        $activeRestrictions = $this->getUserActiveRestrictions($user)->count();
        $maxRestrictions = config('gdpr.processing_restriction.max_active_restrictions', 5); // Default a 5 se non configurato

        return $activeRestrictions >= $maxRestrictions;
    }

    /**
     * Create a new processing restriction request.
     *
     * @param  \App\Models\User  $user The user requesting the restriction.
     * @param  \App\Enums\Gdpr\ProcessingRestrictionType  $type The type of restriction (enum instance).
     * @param  \App\Enums\Gdpr\ProcessingRestrictionReason  $reason The reason for the restriction (enum instance).
     * @param  string|null  $notes Optional user-provided notes.
     * @param  array<string>  $dataCategories Specific data categories to restrict (if applicable).
     * @return \App\Models\ProcessingRestriction|null The created restriction or null on failure.
     * @os1-intent Creates a new processing restriction, performs limit checks, calculates expiry (if configured),
     *             persists the restriction, logs the activity with full details, and sends notifications (if configured).
     */
    public function createRestriction(
        User $user,
        ProcessingRestrictionType $type,
        ProcessingRestrictionReason $reason,
        ?string $notes = null,
        array $dataCategories = []
    ): ?ProcessingRestriction {
        if ($this->hasReachedRestrictionLimit($user)) {
            $this->errorManager->handle('GDPR_PROCESSING_RESTRICTION_LIMIT_REACHED', [
                'user_id' => $user->id,
                'active_count' => $this->getUserActiveRestrictions($user)->count(),
                'max_allowed' => config('gdpr.processing_restriction.max_active_restrictions', 5),
            ]);
            return null;
        }

        try {
            $expiryDaysConfig = config('gdpr.processing_restriction.auto_expiry_days');
            $expiresAt = is_numeric($expiryDaysConfig) ? Carbon::now()->addDays((int)$expiryDaysConfig) : null;

            $restriction = new ProcessingRestriction();
            $restriction->user_id = $user->id;
            $restriction->restriction_type = $type; // Assumes model casts Enum to its value
            $restriction->restriction_reason = $reason; // Assumes model casts Enum to its value
            $restriction->status = ConsentStatus::ACTIVE;
            // Assumes model casts array to JSON. Ensure $dataCategories is an array of strings.
            $restriction->data_categories = !empty($dataCategories) ? $dataCategories : null;
            $restriction->notes = $notes;
            $restriction->expires_at = $expiresAt;
            $restriction->save();

            // Log the activity directly using ActivityLogService->log()
            $this->activityLogService->log(
                action: 'processing_restriction_requested',
                legalBasis: 'user_request', // As per ActivityLogService::logProcessingRestrictionRequested
                details: [
                    'restriction_id' => $restriction->id,
                    'restriction_type' => $restriction->restriction_type->value, // Log the enum value
                    'restriction_reason' => $restriction->restriction_reason->value, // Log the enum value
                    'data_categories' => $restriction->data_categories ?? [],
                    'notes' => $restriction->notes, // Log notes for completeness
                    'expires_at' => $restriction->expires_at ? $restriction->expires_at->toIso8601String() : null,
                ],
                userId: $user->id,
                complianceNote: 'Art. 18 GDPR - Right to restriction of processing' // Specific compliance note
            );

            if (config('gdpr.processing_restriction.enable_notifications', true)) {
                $this->sendRestrictionNotification($restriction);
            }

            return $restriction;
        } catch (\Throwable $e) {
            $this->errorManager->handle('GDPR_PROCESSING_RESTRICTION_CREATE_ERROR', [
                'user_id' => $user->id,
                'restriction_type' => $type->value,
                'restriction_reason' => $reason->value,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], $e);
            return null;
        }
    }

    /**
     * Remove a processing restriction.
     *
     * @param  \App\Models\ProcessingRestriction  $restriction The restriction to remove.
     * @param  string  $removedBy Actor performing the removal (e.g., 'user', 'admin', 'system').
     * @param  string|null  $reasonForRemoval Optional textual reason for removal.
     * @return bool True on success, false on failure.
     * @os1-intent Marks a restriction as removed, sets lifting details, persists changes,
     *             and logs this action with actor, reason, and other relevant details.
     */
    public function removeRestriction(
        ProcessingRestriction $restriction,
        string $removedBy = 'user',
        ?string $reasonForRemoval = null
    ): bool {
        try {
            $restriction->status = ProcessingRestrictionType::REMOVED;
            $restriction->lifted_at = Carbon::now();
            $restriction->lifted_by = $removedBy; // Persist who lifted
            if ($reasonForRemoval !== null) {
                $restriction->lift_reason = $reasonForRemoval; // Persist reason if provided
            }
            $restriction->save();

            // Log the activity directly using ActivityLogService->log()
            $this->activityLogService->log(
                action: 'processing_restriction_removed',
                legalBasis: 'user_request', // Assuming user initiates, can be made dynamic if admins can remove
                details: [
                    'restriction_id' => $restriction->id,
                    'restriction_type' => $restriction->restriction_type->value,
                    'removed_by_actor' => $removedBy,
                    'removal_reason_provided' => $reasonForRemoval, // Log the reason passed to this method
                    'model_lift_reason' => $restriction->lift_reason, // Log the reason stored in the model
                    'lifted_at' => $restriction->lifted_at->toIso8601String(),
                ],
                userId: $restriction->user_id,
                // Consider adding a specific compliance note for removal if distinct from general Art. 18
                complianceNote: 'Art. 18 GDPR - Right to restriction of processing (removal)'
            );

            return true;
        } catch (\Throwable $e) {
            $this->errorManager->handle('GDPR_PROCESSING_RESTRICTION_REMOVE_ERROR', [
                'restriction_id' => $restriction->id,
                'user_id' => $restriction->user_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], $e);
            return false;
        }
    }

    /**
     * Check if a user has an active restriction for the specified processing type.
     *
     * @param  \App\Models\User  $user The user to check.
     * @param  string  $processingActivity The granular application-level processing activity (e.g., 'marketing_emails').
     * @param  string|null  $dataCategory Optional specific data category involved (e.g., 'email_address').
     * @return bool True if an active restriction applies, false otherwise.
     * @os1-intent Checks if any active restriction for the user applies to a given granular processing activity
     *             and, optionally, a specific data category, using the configurable type_mapping.
     */
    public function hasActiveRestriction(
        User $user,
        string $processingActivity,
        ?string $dataCategory = null
    ): bool {
        $activeRestrictions = $this->getUserActiveRestrictions($user);

        if ($activeRestrictions->isEmpty()) {
            return false;
        }

        foreach ($activeRestrictions as $restriction) {
            if ($this->restrictionAppliesToProcessingType($restriction, $processingActivity)) {
                // If a specific data category is provided for the check
                if ($dataCategory !== null) {
                    $restrictedCategories = $restriction->data_categories ?? []; // Model should cast to array
                    // If the restriction has no specific categories, it applies to all categories for that type.
                    // OR if the specific data category is listed in the restriction's categories.
                    if (empty($restrictedCategories) || in_array($dataCategory, $restrictedCategories)) {
                        return true;
                    }
                } else {
                    // No specific data category to check for the processingActivity,
                    // so if the restriction type matches, it applies.
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check if a specific restriction record applies to the specified granular processing activity.
     *
     * @param  \App\Models\ProcessingRestriction  $restriction The restriction record.
     * @param  string  $processingActivity The granular application-level processing activity.
     * @return bool True if the restriction applies, false otherwise.
     * @os1-intent Determines if a specific restriction record (based on its type) applies to a given granular
     *             processing activity by consulting the 'gdpr.processing_restriction.type_mapping' configuration.
     *             Handles the 'ALL' restriction type as a global block.
     */
    protected function restrictionAppliesToProcessingType(
        ProcessingRestriction $restriction,
        string $processingActivity
    ): bool {
        // The `ALL` restriction type applies to all processing activities.
        if ($restriction->restriction_type === ProcessingRestrictionType::ALL) {
            return true;
        }

        $typeMapping = config('gdpr.processing_restriction.type_mapping', []);
        $restrictionTypeValue = $restriction->restriction_type->value;

        // Check if the restriction's type is mapped in the configuration
        // and if the current processingActivity is listed for that restriction type.
        if (
            isset($typeMapping[$restrictionTypeValue]) &&
            is_array($typeMapping[$restrictionTypeValue]) &&
            in_array($processingActivity, $typeMapping[$restrictionTypeValue])
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get expired restrictions.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProcessingRestriction>
     * @os1-intent Retrieves all restrictions marked as active whose 'expires_at' date has passed.
     */
    public function getExpiredRestrictions()
    {
        return ProcessingRestriction::where('status', ConsentStatus::ACTIVE)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', Carbon::now())
            ->get();
    }

    /**
     * Process expired restrictions by marking them as 'expired' and logging the action.
     *
     * @return int Number of restrictions processed.
     * @os1-intent Iterates through all found expired restrictions, updates their status to 'expired',
     *             persists changes, and logs each expiration event for audit purposes.
     *             Handles errors gracefully for individual processing failures.
     */
    public function processExpiredRestrictions(): int
    {
        $expiredRestrictions = $this->getExpiredRestrictions();
        $count = 0;

        if ($expiredRestrictions->isEmpty()) {
            return 0;
        }

        foreach ($expiredRestrictions as $restriction) {
            try {
                $restriction->status = ConsentStatus::EXPIRED;
                $restriction->save();

                // Log the activity directly using ActivityLogService->log()
                $this->activityLogService->log(
                    action: 'processing_restriction_expired',
                    legalBasis: 'legal_obligation', // Based on policy of auto-expiry
                    details: [
                        'restriction_id' => $restriction->id,
                        'restriction_type' => $restriction->restriction_type->value,
                        'expired_at_configured' => $restriction->expires_at->toIso8601String(),
                        'processed_at' => Carbon::now()->toIso8601String(),
                    ],
                    userId: $restriction->user_id,
                    complianceNote: 'Art. 5(1)(e) GDPR - Restriction expired per data minimization/retention policy'
                );

                $count++;
            } catch (\Throwable $e) {
                $this->errorManager->handle('GDPR_EXPIRED_RESTRICTION_PROCESS_ERROR', [
                    'restriction_id' => $restriction->id,
                    'user_id' => $restriction->user_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ], $e);
            }
        }

        if ($count > 0) {
            $this->logManager->info('Processing of expired GDPR restrictions completed.', [
                'component' => static::class, // Use static::class for late static binding
                'method' => __FUNCTION__,
                'processed_count' => $count,
                'total_found_expired' => $expiredRestrictions->count()
            ]);
        }

        return $count;
    }

    /**
     * Send notification about the restriction creation.
     *
     * @param  \App\Models\ProcessingRestriction  $restriction The restriction that was created.
     * @return void
     * @os1-intent Sends a notification to the user about the successful creation of their processing restriction,
     *             if notifications are enabled and the notification class is correctly configured in 'gdpr.notifications.email_classes.processing_restricted'.
     *             Handles configuration errors gracefully by logging via UEM and not attempting to send.
     */
    protected function sendRestrictionNotification(ProcessingRestriction $restriction): void
    {
        if (!config('gdpr.processing_restriction.enable_notifications', true)) {
            return;
        }

        try {
            /** @var \App\Models\User|null $user */
            $user = $restriction->user;

            if (!$user) {
                 $this->logManager->warning('Cannot send restriction notification: User not found for restriction.', [
                    'component' => static::class,
                    'method' => __FUNCTION__,
                    'restriction_id' => $restriction->id,
                ]);
                return;
            }

            $notificationClassConfigKey = 'gdpr.notifications.email_classes.processing_restricted';
            // Prendi la classe dal config, se non esiste o non è una stringa, usa il default.
            $notificationClass = config($notificationClassConfigKey);
            if (!is_string($notificationClass) || !class_exists($notificationClass)) {
                 // Se la configurazione non è valida o la classe non esiste, usa un default o logga un errore serio.
                 // Per ora, logghiamo un errore con UEM e non inviamo.
                $actualConfigValue = config($notificationClassConfigKey); // Per il log
                $defaultNotificationClass = \App\Notifications\Gdpr\ProcessingRestrictedNotification::class; // Esempio di fallback

                $this->errorManager->handle('GDPR_NOTIFICATION_CLASS_INVALID', [
                    'restriction_id' => $restriction->id,
                    'user_id' => $user->id,
                    'configured_class_key' => $notificationClassConfigKey,
                    'configured_class_value_type' => gettype($actualConfigValue),
                    'configured_class_value' => is_string($actualConfigValue) ? $actualConfigValue : 'Not a string',
                    'class_exists_check_failed_for' => is_string($notificationClass) ? $notificationClass : 'Invalid class name',
                    'message' => "Notification class for '{$notificationClassConfigKey}' is invalid or not found. Fallback might be needed or check config."
                ]);
                // Potresti decidere di usare una classe di notifica di fallback qui se la principale fallisce
                // if (class_exists($defaultNotificationClass)) {
                //     $user->notify(new $defaultNotificationClass($restriction));
                // }
                return;
            }

            $user->notify(new $notificationClass($restriction));

        } catch (\Throwable $e) {
            $this->errorManager->handle('GDPR_RESTRICTION_NOTIFICATION_ERROR', [
                'restriction_id' => $restriction->id,
                'user_id' => $restriction->user_id ?? null, // L'utente potrebbe essere nullo se la relazione fallisce
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], $e);
        }
    }
}
