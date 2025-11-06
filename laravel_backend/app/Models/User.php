<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @package App\Models
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-10-31
 * @purpose User model con supporto multi-tenant
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'tenant_id' => 'integer',
        ];
    }

    /**
     * Boot the model
     */
    protected static function booted(): void
    {
        // Users non hanno Global Scope tenant perché l'utente stesso ha tenant_id
        // ma possiamo aggiungere scope opzionale per filtrare utenti del tenant corrente
    }

    /**
     * Get tenant this user belongs to
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    /**
     * Get conversations for this user
     */
    public function conversations()
    {
        return $this->hasMany(UserConversation::class, 'user_id');
    }

    /**
     * Get activities for this user (audit trail)
     */
    public function activities()
    {
        return $this->hasMany(UserActivity::class, 'user_id');
    }
}