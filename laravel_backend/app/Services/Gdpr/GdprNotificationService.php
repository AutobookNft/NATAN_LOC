<?php

declare(strict_types=1);

namespace App\Services\Gdpr;

use App\DataTransferObjects\Notifications\Gdpr\Fanculo;
use App\DataTransferObjects\Notifications\Gdpr\GdprPayloadData;
use App\DataTransferObjects\Notifications\Gdpr\GdprNotificationData;
use App\DataTransferObjects\Notifications\Gdpr\GdprNotificationPayloadData;
use App\DataTransferObjects\Notifications\Gdpr\Stronzo;
use App\Enums\Gdpr\GdprNotificationStatus;
use App\Enums\NotificationHandlerType;
use App\Enums\NotificationStatus;
use App\Models\User;
use App\Services\Notifications\MultiChannelHandlerFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Throwable;
use Ultra\ErrorManager\Interfaces\ErrorManagerInterface;
use Ultra\UltraLogManager\UltraLogManager;

/**
 * @package   App\Services\Gdpr
 * @author    Padmin D. Curtis (for Fabio Cherici)
 * @version   1.2.0
 * @date      2025-06-14
 * @solution  Orchestrates the creation and dispatch of all GDPR-related notifications with OS1.5 compliance.
 *
 * --- OS1 DOCUMENTATION ---
 * @oracode-intent: To provide a single, reliable entry point for triggering any GDPR notification, abstracting away the underlying complexity of payload creation, data assembly, and handler dispatching while maintaining transactional integrity at the persistence layer.
 * @oracode-value-flow:
 * 1.  INPUT: Receives a User, a notification type (string), and optional context.
 * 2.  PROCESS: Validates the type, creates structured DTOs (GdprPayloadData and GdprNotificationData), and delegates to the appropriate handler for multi-channel dispatch.
 * 3.  OUTPUT: Returns the complete notification data structure for caller flexibility, while persistence atomicity is handled at the channel level.
 * @oracode-arch-pattern: Service Layer Facade with Single Responsibility. Orchestrates without coupling to persistence implementation details.
 * @oracode-transparency-level: HIGH. All operations are logged via ULM, all failures are handled via UEM, and the service remains transaction-agnostic.
 * @oracode-sustainability-factor: HIGH. DTO-driven approach allows easy extension of notification types and contexts without breaking existing consumers.
 * @os1-compliance: Full. Demonstrates Explicitly Intentional design, Simplicity Empowerment (clear API), Semantic Consistency (naming and flow), and Proactive Security (validation and error handling).
 *
 * 🎯 **GdprNotificationService - OS1.5 Compliant Orchestrator**
 *
 * 🧱 **Core Logic:** This service acts as the central orchestrator for all GDPR notifications.
 * It follows the OS1.5 principle of Single Responsibility by focusing solely on notification coordination,
 * delegating persistence concerns to the appropriate channels while maintaining full observability.
 *
 * 📡 **Communicates With:**
 * - `UltraLogManager`: For comprehensive operation logging and audit trails
 * - `ErrorManagerInterface`: For robust, centralized error handling and user feedback
 * - `MultiChannelHandlerFactory`: For handler instantiation and delegation
 * - `GdprNotificationData`: For structured data transfer with type safety
 *
 * 🧪 **Testability:** Fully testable through dependency injection. All external dependencies are injected,
 * making it straightforward to mock ULM, UEM, and handlers for isolated unit testing.
 *
 * 🛡️ **Security Considerations:** Validates notification types against configuration whitelist,
 * logs all attempts for audit purposes, and ensures sensitive context data is properly structured
 * through DTOs before delegation.
 */
class GdprNotificationService
{
    /**
     * @param UltraLogManager $logger The centralized logging service for operation tracking.
     * @param ErrorManagerInterface $errorManager The centralized error management service for robust failure handling.
     */
    public function __construct(
        protected UltraLogManager $logger,
        protected ErrorManagerInterface $errorManager
    ) {}

    /**
     * Dispatches a GDPR notification to a user with full observability and error handling.
     *
     * 🎯 **Method Intent:** Provides a clean, high-level API for GDPR notification dispatch
     * while maintaining OS1.5 compliance through structured data flow and comprehensive logging.
     *
     * @param User $user The user to notify.
     * @param string $notificationGdprType The specific type of GDPR event (e.g., 'consent_updated'). Must match a key in config/gdpr.php.
     * @param array $context Optional context for message placeholder replacement and audit trail.
     * @param array $channels Optional channel configuration for multi-channel dispatch.
     * @return GdprNotificationData|JsonResponse|RedirectResponse|null The complete notification data structure on success, or error response on failure.
     * @throws Throwable For unexpected errors not handled by UEM.
     */
    public function dispatchNotification(
        User $user,
        string $gdprNotificationType,
        array $context = [],
        array $channels = []
    ): GdprNotificationData|JsonResponse|RedirectResponse|null {


        // OS1.5 Explicitly Intentional: Validate notification type against configuration whitelist
        if (!array_key_exists($gdprNotificationType, config('gdpr.notifications.classes', []))) {
            return $this->errorManager->handle('GDPR_VIOLATION_ATTEMPT', [
                'details' => 'Attempted to dispatch an undefined GDPR notification type.',
                'notification_type' => $gdprNotificationType,
                'user_id' => $user->id,
            ]);
        }

        try {

            $this->logger->info('Attempting to dispatch GDPR notification.');

            // 1. Create structured payload DTO with OS1.5 Semantic Consistency
            $payload = new GdprNotificationPayloadData(
                consent_type: $context['new_value'] ?? 'unknown',
                gdpr_notification_type: $gdprNotificationType,
                previous_value: $context['previous_value'] ?? null,
                new_value: $context['new_value'] ?? null,
                email: $user->email,
                role: $user->role ?? 'creator',
                message: "notification.gdpr.{$gdprNotificationType}.content",
                ip_address: $context['ip_address'] ?? null,
                user_agent: $context['user_agent'] ?? null,
                payload_status: $context['payload_status'] ?? GdprNotificationStatus::PENDING_USER_CONFIRMATION->value,
            );

            // 2. Create complete notification DTO for handler delegation
            $notificationData = new GdprNotificationData(
                type: $gdprNotificationType,
                outcome: NotificationStatus::PENDING, // Will be set by downstream processes
                payload: $payload ?? null,
            );

            // // 3. Delegate to handler with OS1.5 Simplicity Empowerment
            $handler = MultiChannelHandlerFactory::getHandler(NotificationHandlerType::GDPR);
            $handler->handle($user, $notificationData, $channels);

            $this->logger->info('Successfully dispatched GDPR notification.', [
                'user_id' => $user->id,
                'notification_type' => $gdprNotificationType,
                'notification_data' => $notificationData->toArray(),
            ]);

            return $notificationData;

        } catch (Throwable $e) {
            // OS1.5 Proactive Security: Comprehensive error handling with context preservation
            return $this->errorManager->handle('GDPR_NOTIFICATION_DISPATCH_FAILED', [
                'error_message' => $e->getMessage(),
                'user_id' => $user->id,
                'notification_type' => $gdprNotificationType,
            ], $e);
        }
    }

    /**
     * Gets a list of available GDPR notification types for testing and validation purposes.
     *
     * 🎯 **Method Intent:** Provides transparent access to configured notification types
     * for testing, validation, and administrative purposes.
     *
     * @return array List of available notification type keys from configuration.
     */
    public function getAvailableNotificationTypes(): array
    {
        return array_keys(config('gdpr.notifications.classes', []));
    }
}
