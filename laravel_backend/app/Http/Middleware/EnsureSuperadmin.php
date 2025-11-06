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
 * @purpose Middleware per limitare accesso NATAN solo a superadmin
 *
 * CONTESTO: /home/fabio/NATAN_LOC
 * PERCORSO FILE: app/Http/Middleware/EnsureSuperadmin.php
 *
 * NATAN è accessibile SOLO a utenti con ruolo superadmin.
 * Questo middleware verifica che l'utente autenticato abbia il ruolo superadmin.
 */
class EnsureSuperadmin
{
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

        // Verifica ruolo superadmin
        if (!$user->hasRole('superadmin')) {
            abort(403, __('natan.errors.superadmin_required'));
        }

        return $next($request);
    }
}



