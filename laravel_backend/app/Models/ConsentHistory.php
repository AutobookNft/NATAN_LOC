<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @Oracode Consent History Model
 * 🎯 Purpose: Immutable audit trail of all consent changes
 * 🧱 Core Logic: GDPR Article 7 consent documentation
 * 📡 API: Read-only for compliance auditing
 * 🛡️ GDPR: Comprehensive consent lifecycle tracking
 *
 * @package App\Models
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-20
 */
class ConsentHistory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'consent_histories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'user_consent_id',
        'consent_type_slug',
        'action',
        'action_timestamp',
        'action_source',
        'previous_state',
        'new_state',
        'state_diff',
        'consent_version',
        'consent_text_shown',
        'consent_options_available',
        'consent_selections',
        'interaction_method',
        'explicit_action',
        'time_to_decision',
        'interaction_metadata',
        'ip_address',
        'user_agent',
        'session_id',
        'device_fingerprint',
        'browser_info',
        'referrer_url',
        'legal_basis',
        'reason_for_action',
        'triggered_by',
        'business_context',
        'user_notified',
        'notification_sent_at',
        'notification_channel',
        'acknowledgment_required',
        'acknowledged_at',
        'age_verified',
        'identity_verified',
        'verification_methods',
        'verification_notes',
        'admin_user_id',
        'admin_notes',
        'requires_review',
        'reviewed_at',
        'reviewed_by',
        'record_hash',
        'integrity_metadata',
        'is_verified',
        'related_request_id',
        'related_incident_id',
        'related_records',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'action_timestamp' => 'datetime',
        'previous_state' => 'array',
        'new_state' => 'array',
        'state_diff' => 'array',
        'consent_options_available' => 'array',
        'consent_selections' => 'array',
        'interaction_metadata' => 'array',
        'browser_info' => 'array',
        'business_context' => 'array',
        'verification_methods' => 'array',
        'integrity_metadata' => 'array',
        'related_records' => 'array',
        'explicit_action' => 'boolean',
        'user_notified' => 'boolean',
        'acknowledgment_required' => 'boolean',
        'requires_review' => 'boolean',
        'is_verified' => 'boolean',
        'notification_sent_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Action types for consent history
     *
     * @var array<string>
     */
    public const ACTIONS = [
        'granted' => 'granted',
        'renewed' => 'renewed',
        'withdrawn' => 'withdrawn',
        'expired' => 'expired',
        'updated' => 'updated',
        'migrated' => 'migrated',
        'restored' => 'restored',
        'invalidated' => 'invalidated',
    ];

    /**
     * Action source options
     *
     * @var array<string>
     */
    public const ACTION_SOURCES = [
        'web' => 'web',
        'mobile_app' => 'mobile_app',
        'api' => 'api',
        'admin' => 'admin',
        'system' => 'system',
        'import' => 'import',
    ];

    /**
     * Interaction method options
     *
     * @var array<string>
     */
    public const INTERACTION_METHODS = [
        'checkbox' => 'checkbox',
        'toggle' => 'toggle',
        'radio_button' => 'radio_button',
        'dropdown' => 'dropdown',
        'form_submit' => 'form_submit',
        'api_call' => 'api_call',
        'admin_action' => 'admin_action',
        'automatic' => 'automatic',
    ];

    /**
     * Get the user who performed the consent action.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related user consent record.
     *
     * @return BelongsTo
     */
    public function userConsent(): BelongsTo
    {
        return $this->belongsTo(UserConsent::class);
    }

    /**
     * Get the admin user who performed the action (if applicable).
     *
     * @return BelongsTo
     */
    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    /**
     * Get the user who reviewed this history record.
     *
     * @return BelongsTo
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Prevent updates to maintain immutability.
     *
     * @param array $attributes
     * @param array $options
     * @return bool
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        // Only allow review-related updates
        $allowedFields = ['requires_review', 'reviewed_at', 'reviewed_by', 'admin_notes'];
        $filteredAttributes = array_intersect_key($attributes, array_flip($allowedFields));

        if (empty($filteredAttributes)) {
            return false; // No updates allowed
        }

        return parent::update($filteredAttributes, $options);
    }

    /**
     * Prevent deletion to maintain audit trail.
     *
     * @return bool|null
     */
    public function delete(): ?bool
    {
        return false; // No deletion allowed for audit trail
    }

    /**
     * Generate integrity hash for tamper detection.
     *
     * @return string
     */
    public function generateHash(): string
    {
        $data = [
            'user_id' => $this->user_id,
            'consent_type_slug' => $this->consent_type_slug,
            'action' => $this->action,
            'action_timestamp' => $this->action_timestamp?->toISOString(),
            'new_state' => $this->new_state,
            'ip_address' => $this->ip_address,
        ];

        return hash('sha256', json_encode($data, \JSON_SORT_KEYS));
    }

    /**
     * Verify integrity of this history record.
     *
     * @return bool
     */
    public function verifyIntegrity(): bool
    {
        if (!$this->record_hash) {
            return false;
        }

        return hash_equals($this->record_hash, $this->generateHash());
    }

    /**
     * Boot method to automatically generate hash.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $model->record_hash = $model->generateHash();
        });
    }

    /**
     * Scope for specific action types.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $action
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for explicit user actions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExplicitActions($query)
    {
        return $query->where('explicit_action', true);
    }

    /**
     * Scope for admin actions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAdminActions($query)
    {
        return $query->where('action_source', 'admin')
            ->whereNotNull('admin_user_id');
    }

    /**
     * Scope for records requiring review.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRequiresReview($query)
    {
        return $query->where('requires_review', true)
            ->whereNull('reviewed_at');
    }

    /**
     * Scope for consent grants.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGrants($query)
    {
        return $query->whereIn('action', ['granted', 'renewed', 'restored']);
    }

    /**
     * Scope for consent withdrawals.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithdrawals($query)
    {
        return $query->whereIn('action', ['withdrawn', 'expired', 'invalidated']);
    }

    /**
     * Scope for verified records.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope for records by consent type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $consentType
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByConsentType($query, string $consentType)
    {
        return $query->where('consent_type_slug', $consentType);
    }

    /**
     * Scope for records within date range.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Carbon\Carbon $from
     * @param \Carbon\Carbon $to
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange($query, \Carbon\Carbon $from, \Carbon\Carbon $to)
    {
        return $query->whereBetween('action_timestamp', [$from, $to]);
    }
}

