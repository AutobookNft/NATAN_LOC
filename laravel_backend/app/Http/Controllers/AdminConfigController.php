<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * @package App\Http\Controllers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-03
 * @purpose Controller per gestione configurazioni tenant-specific (Admin)
 *
 * CONTESTO: /home/fabio/NATAN_LOC
 * PERCORSO FILE: app/Http/Controllers/AdminConfigController.php
 *
 * Gestisce le configurazioni specifiche del tenant corrente:
 * - Visualizzazione configurazioni attuali
 * - Modifica configurazioni (settings JSON)
 * - Configurazioni per AI, scraping, embeddings, etc.
 */
class AdminConfigController extends Controller
{
    /**
     * Mostra la pagina delle configurazioni del tenant corrente
     *
     * @return View
     */
    public function index(): View
    {
        $user = Auth::user();
        
        // Superadmin usa tenant dalla sessione se disponibile
        $tenantId = $user->hasRole('superadmin')
            ? (request()->session()->get('current_tenant_id') ?? $user->tenant_id)
            : $user->tenant_id;
        
        if (!$tenantId && !$user->hasRole('superadmin')) {
            abort(403, __('admin_config.no_tenant'));
        }
        
        // Verifica permessi: solo admin, superadmin o pa_entity_admin
        if (!$user->hasAnyRole(['admin', 'superadmin', 'pa_entity_admin'])) {
            abort(403, __('users.insufficient_permissions'));
        }
        
        $tenant = $tenantId ? Tenant::find($tenantId) : null;
        
        if (!$tenant) {
            abort(404, __('admin_config.tenant_not_found'));
        }
        
        $settings = $tenant->settings ?? [];
        
        return view('natan.admin.config', [
            'tenant' => $tenant,
            'settings' => $settings,
        ]);
    }
    
    /**
     * Salva le configurazioni del tenant corrente
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        // Superadmin usa tenant dalla sessione se disponibile
        $tenantId = $user->hasRole('superadmin')
            ? (request()->session()->get('current_tenant_id') ?? $user->tenant_id)
            : $user->tenant_id;
        
        if (!$tenantId && !$user->hasRole('superadmin')) {
            abort(403, __('admin_config.no_tenant'));
        }
        
        // Verifica permessi: solo admin, superadmin o pa_entity_admin
        if (!$user->hasAnyRole(['admin', 'superadmin', 'pa_entity_admin'])) {
            abort(403, __('users.insufficient_permissions'));
        }
        
        $tenant = $tenantId ? Tenant::find($tenantId) : null;
        
        if (!$tenant) {
            abort(404, __('admin_config.tenant_not_found'));
        }
        
        // Validazione configurazioni
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.ai' => 'nullable|array',
            'settings.ai.default_model' => 'nullable|string|max:255',
            'settings.ai.temperature' => 'nullable|numeric|min:0|max:2',
            'settings.ai.max_tokens' => 'nullable|integer|min:1|max:32000',
            'settings.scraping' => 'nullable|array',
            'settings.scraping.enabled' => 'nullable|boolean',
            'settings.scraping.interval_hours' => 'nullable|integer|min:1',
            'settings.embeddings' => 'nullable|array',
            'settings.embeddings.enabled' => 'nullable|boolean',
            'settings.embeddings.model' => 'nullable|string|max:255',
            'settings.notifications' => 'nullable|array',
            'settings.notifications.email' => 'nullable|boolean',
            'settings.notifications.slack' => 'nullable|boolean',
        ]);
        
        // Merge con settings esistenti
        $currentSettings = $tenant->settings ?? [];
        $newSettings = array_merge($currentSettings, $validated['settings']);
        
        $tenant->settings = $newSettings;
        $tenant->save();
        
        return redirect()
            ->route('admin.config.index')
            ->with('success', __('admin_config.saved_successfully'));
    }
}

