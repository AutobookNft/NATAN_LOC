<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @Oracode Model: Consent Version
 * 🎯 Purpose: Consent policy version management
 * 🛡️ Privacy: Policy versioning for transparency compliance
 * 🧱 Core Logic: Version control for consent policies
 *
 * @package App\Models
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-20
 */
class ConsentVersion extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable
     * @var array<string>
     */
    protected $fillable = [
        'version',
        'consent_types',
        'changes',
        'effective_date',
        'deprecated_at',
        'is_active',
        'created_by',
        'notes'
    ];

    /**
     * The attributes that should be cast
     * @var array<string, string>
     */
    protected $casts = [
        'consent_types' => 'array',
        'changes' => 'array',
        'effective_date' => 'datetime',
        'deprecated_at' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user consents for this version
     * @return HasMany
     * @privacy-safe Returns related consent records
     */
    public function userConsents(): HasMany
    {
        return $this->hasMany(UserConsent::class);
    }

    /**
     * Get the user who created this version
     * @return BelongsTo
     * @privacy-safe Returns creator relationship
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for active versions
     * @param $query
     * @return mixed
     * @privacy-safe Filters for active versions only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for current version (latest active)
     * @param $query
     * @return mixed
     * @privacy-safe Gets current active version
     */
    public function scopeCurrent($query)
    {
        return $query->active()->latest('effective_date');
    }
}

