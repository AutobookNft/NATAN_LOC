<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

/**
 * @package App\Helpers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-01-31
 * @purpose Helper per filtrare ruoli in base al ruolo dell'utente corrente
 *
 * CONTESTO: /home/fabio/NATAN_LOC
 * PERCORSO FILE: app/Helpers/RoleHelper.php
 *
 * Gestisce la logica di filtraggio ruoli:
 * - superadmin: vede tutti i ruoli
 * - admin: vede solo ruoli PA/NATAN (non può assegnare superadmin)
 * - pa_entity_admin: vede solo ruoli PA/NATAN (non può assegnare superadmin o admin)
 */
class RoleHelper
{
    /**
     * Ruoli PA/NATAN pertinenti (ruoli validi per tenant PA)
     *
     * @var array<string>
     */
    private const PA_ROLES = [
        'pa_entity',
        'pa_entity_admin',
        'editor',
        'legal', // Ruolo PA per consulenti legali
        'inspector', // Ruolo PA per ispettori
    ];

    /**
     * Ruoli NON assegnabili (solo superadmin può assegnarli)
     *
     * @var array<string>
     */
    private const PROTECTED_ROLES = [
        'superadmin',
    ];

    /**
     * Ruoli EGI/FlorenceEGI (non pertinenti per NATAN_LOC)
     *
     * @var array<string>
     */
    private const EGI_ROLES = [
        'creator',
        'collector',
        'patron',
        'trader_pro',
        'enterprise',
        'epp_entity',
        'frangette_entity',
        'admin',
        'editor',
        'legal', // Ruolo PA per consulenti legali
        'inspector', // Ruolo PA per ispettori
        'commissioner', // Ruolo PA per commissari
        'weak_connect',
    ];

    /**
     * Ruoli da escludere completamente (non pertinenti per NATAN_LOC)
     *
     * @var array<string>
     */
    private const EXCLUDED_ROLES = [
        'guest', // Non ha senso in NATAN
    ];

    /**
     * Ottiene i ruoli assegnabili dall'utente corrente
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getAssignableRoles(): \Illuminate\Support\Collection
    {
        $currentUser = Auth::user();

        if (!$currentUser) {
            return collect([]);
        }

        // Superadmin: vede tutti i ruoli (tranne quelli esclusi)
        if ($currentUser->hasRole('superadmin')) {
            return Role::whereNotIn('name', self::EXCLUDED_ROLES)
                ->orderBy('name', 'asc')
                ->get();
        }

        // Admin: vede solo ruoli PA/NATAN (non può assegnare superadmin)
        if ($currentUser->hasRole('admin')) {
            return Role::whereIn('name', self::PA_ROLES)
                ->orderBy('name', 'asc')
                ->get();
        }

        // pa_entity_admin: vede solo ruoli PA/NATAN base (non può assegnare superadmin o admin)
        if ($currentUser->hasRole('pa_entity_admin')) {
            return Role::whereIn('name', [
                'pa_entity',
                'pa_entity_admin',
                'editor',
                'legal',
            ])
                ->orderBy('name', 'asc')
                ->get();
        }

        // Altri ruoli: nessun permesso di assegnazione
        return collect([]);
    }

    /**
     * Verifica se un ruolo può essere assegnato dall'utente corrente
     *
     * @param string $roleName
     * @return bool
     */
    public static function canAssignRole(string $roleName): bool
    {
        $currentUser = Auth::user();

        if (!$currentUser) {
            return false;
        }

        // Superadmin: può assegnare tutti i ruoli (tranne quelli esclusi)
        if ($currentUser->hasRole('superadmin')) {
            return !in_array($roleName, self::EXCLUDED_ROLES, true);
        }

        // Admin: può assegnare solo ruoli PA/NATAN (non superadmin)
        if ($currentUser->hasRole('admin')) {
            return in_array($roleName, self::PA_ROLES, true)
                && !in_array($roleName, self::PROTECTED_ROLES, true);
        }

        // pa_entity_admin: può assegnare solo ruoli PA base (non superadmin o admin)
        if ($currentUser->hasRole('pa_entity_admin')) {
            return in_array($roleName, [
                'pa_entity',
                'pa_entity_admin',
                'editor',
                'legal',
                'inspector',
            ], true);
        }

        return false;
    }

    /**
     * Filtra array di nomi ruoli rimuovendo quelli non assegnabili
     *
     * @param array<string> $roleNames
     * @return array<string>
     */
    public static function filterAssignableRoles(array $roleNames): array
    {
        return array_filter($roleNames, function ($roleName) {
            return self::canAssignRole($roleName);
        });
    }
}

