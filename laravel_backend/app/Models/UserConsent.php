<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @Oracode Model: User Consent
 * 🎯 Purpose: Individual consent record with full audit trail
 * 🛡️ Privacy: Immutable consent tracking for GDPR compliance
 * 🧱 Core Logic: Consent versioning and withdrawal tracking
 *
 * @package App\Models
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-20
 */
class UserConsent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'consent_version_id',
        'consent_type',
        'granted',
        'legal_basis',
        'withdrawal_method',
        'ip_address',
        'user_agent',
        'metadata',
        'status',
        'withdrawn_at',
    ];

    /**
     * The attributes that should be cast
     * @var array<string, string>
     */
    protected $casts = [
        'granted' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Prevent updates to maintain audit trail integrity
     * @var bool
     */
    public $incrementing = true;

    /**
     * Boot the model
     * @return void
     * @privacy-safe Ensures consent records remain immutable
     */
    protected static function boot(): void
    {
        parent::boot();

        // Prevent updates - consents should be immutable
        static::updating(function ($model) {
            throw new \Exception('Consent records cannot be updated. Create a new record instead.');
        });
    }

    /**
     * Get the user that owns the consent
     * @return BelongsTo
     * @privacy-safe Returns owning user relationship
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the consent version
     * @return BelongsTo
     * @privacy-safe Returns consent version relationship
     */
    public function consentVersion(): BelongsTo
    {
        return $this->belongsTo(ConsentVersion::class);
    }

    /**
     * Scope for specific consent type
     * @param $query
     * @param string $type
     * @return mixed
     * @privacy-safe Filters by consent type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('consent_type', $type);
    }

    /**
     * Scope for granted consents
     * @param $query
     * @return mixed
     * @privacy-safe Filters for granted consents only
     */
    public function scopeGranted($query)
    {
        return $query->where('granted', true);
    }

    /**
     * Scope for withdrawn consents
     * @param $query
     * @return mixed
     * @privacy-safe Filters for withdrawn consents only
     */
    public function scopeWithdrawn($query)
    {
        return $query->where('granted', false)->whereNotNull('withdrawal_method');
    }
}

