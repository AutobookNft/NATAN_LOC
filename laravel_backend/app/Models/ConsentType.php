<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @Oracode Consent Type Model
 * 🎯 Purpose: Manage consent type definitions and configurations
 * 🧱 Core Logic: Master data for consent management with GDPR compliance
 * 📡 API: Read-heavy model for consent configuration
 * 🛡️ GDPR: Central consent type management for Article 7 compliance
 *
 * @package App\Models
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-20
 */
class ConsentType extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'consent_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'slug',
        'name',
        'description',
        'legal_basis',
        'data_categories',
        'processing_purposes',
        'recipients',
        'international_transfers',
        'transfer_countries',
        'is_required',
        'is_granular',
        'can_withdraw',
        'withdrawal_effect_days',
        'retention_period',
        'retention_days',
        'deletion_method',
        'priority_order',
        'is_active',
        'requires_double_opt_in',
        'requires_age_verification',
        'minimum_age',
        'icon',
        'color',
        'form_fields',
        'gdpr_assessment_date',
        'gdpr_assessment_notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'data_categories' => 'array',
        'processing_purposes' => 'array',
        'recipients' => 'array',
        'transfer_countries' => 'array',
        'form_fields' => 'array',
        'international_transfers' => 'boolean',
        'is_required' => 'boolean',
        'is_granular' => 'boolean',
        'can_withdraw' => 'boolean',
        'is_active' => 'boolean',
        'requires_double_opt_in' => 'boolean',
        'requires_age_verification' => 'boolean',
        'gdpr_assessment_date' => 'datetime',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Legal basis options
     *
     * @var array<string>
     */
    public const LEGAL_BASIS_OPTIONS = [
        'consent' => 'consent',
        'legitimate_interest' => 'legitimate_interest',
        'contract' => 'contract',
        'legal_obligation' => 'legal_obligation',
        'vital_interests' => 'vital_interests',
        'public_task' => 'public_task',
    ];

    /**
     * Deletion method options
     *
     * @var array<string>
     */
    public const DELETION_METHODS = [
        'hard_delete' => 'hard_delete',
        'anonymize' => 'anonymize',
        'archive' => 'archive',
    ];


    /**
     * Get the user who created this consent type.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved this consent type.
     *
     * @return BelongsTo
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get user consents for this type.
     *
     * @return HasMany
     */
    public function userConsents(): HasMany
    {
        return $this->hasMany(UserConsent::class, 'consent_type', 'slug');
    }

    /**
     * Get localized name for current locale.
     *
     * @param string|null $locale
     * @return string
     */
    public function getLocalizedName(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->name[$locale] ?? $this->name['en'] ?? $this->slug;
    }

    /**
     * Get localized description for current locale.
     *
     * @param string|null $locale
     * @return string
     */
    public function getLocalizedDescription(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->description[$locale] ?? $this->description['en'] ?? '';
    }

    /**
     * Check if this consent type requires GDPR consent.
     *
     * @return bool
     */
    public function requiresGdprConsent(): bool
    {
        return $this->legal_basis === 'consent';
    }

    /**
     * Check if this consent type involves international transfers.
     *
     * @return bool
     */
    public function hasInternationalTransfers(): bool
    {
        return $this->international_transfers && !empty($this->transfer_countries);
    }

    /**
     * Check if this consent type is approved and active.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->is_active && $this->approved_at !== null;
    }

    /**
     * Scope for active consent types.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for approved consent types.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    /**
     * Scope for available consent types (active and approved).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailable($query)
    {
        return $query->active()->approved();
    }

    /**
     * Scope for required consent types.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope ordered by priority.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('priority_order')->orderBy('name->en');
    }

    /**
     * Scope for consent types requiring GDPR consent.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGdprConsent($query)
    {
        return $query->where('legal_basis', 'consent');
    }
}

