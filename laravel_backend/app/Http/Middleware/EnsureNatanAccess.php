<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package App\Http\Middleware
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-01-31
 * @purpose Middleware per limitare accesso NATAN a ruoli PA/autorizzati
 *
 * CONTESTO: /home/fabio/NATAN_LOC
 * PERCORSO FILE: app/Http/Middleware/EnsureNatanAccess.php
 *
 * NATAN_LOC è specifico per PA (Pubbliche Amministrazioni).
 * Ruoli autorizzati:
 * - superadmin (accesso completo cross-tenant)
 * - admin (admin tenant)
 * - pa_entity_admin (admin PA tenant)
 * - pa_entity (utente PA)
 * - editor (editor PA)
 *
 * Ruoli NON autorizzati (EGI FlorenceEGI):
 * - creator, collector, patron, trader_pro, enterprise, etc.
 */
class EnsureNatanAccess
{
    /**
     * Ruoli autorizzati ad accedere a NATAN_LOC
     * 
     * @var array<string>
     */
    private const ALLOWED_ROLES = [
        'superadmin',
        'admin',
        'pa_entity_admin',
        'pa_entity',
        'editor',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica autenticazione
        if (!Auth::check()) {
            abort(401, __('natan.errors.authentication_required'));
        }

        $user = Auth::user();

        // Verifica che l'utente abbia almeno uno dei ruoli autorizzati
        $hasAllowedRole = false;
        foreach (self::ALLOWED_ROLES as $role) {
            if ($user->hasRole($role)) {
                $hasAllowedRole = true;
                break;
            }
        }

        if (!$hasAllowedRole) {
            abort(403, __('natan.errors.natan_access_required'));
        }

        return $next($request);
    }
}



