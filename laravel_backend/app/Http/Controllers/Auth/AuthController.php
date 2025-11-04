<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Resolvers\TenantResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * @package App\Http\Controllers\Auth
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-02
 * @purpose Controller per autenticazione multi-tenant
 *
 * CONTESTO: /home/fabio/NATAN_LOC
 * PERCORSO FILE: app/Http/Controllers/Auth/AuthController.php
 *
 * Gestisce login/logout con supporto multi-tenant:
 * - Se si accede da subdomain (es: firenze.natan.loc) → login diretto per quel tenant
 * - Se si accede da root (natan.loc) → selezione tenant prima del login
 */
class AuthController extends Controller
{
    /**
     * Mostra la pagina di login
     * 
     * La pagina di login è sempre visibile, anche senza tenant.
     * Se non c'è tenant, viene usato il tenant di default (ID=1, Florence EGI).
     *
     * @param Request $request
     * @return View
     */
    public function showLogin(Request $request): View
    {
        // Prova a risolvere il tenant dal subdomain/header/user
        $tenantId = TenantResolver::resolve();
        $tenant = null;
        
        if ($tenantId) {
            $tenant = Tenant::find($tenantId);
        }
        
        // Se c'è un tenant_id nella query string (da selezione tenant), usalo
        if ($request->has('tenant_id')) {
            $tenant = Tenant::where('id', $request->get('tenant_id'))
                ->where('is_active', true)
                ->first();
        }
        
        // Se non c'è tenant selezionato, mostra tutti i tenant attivi per la selezione
        // NON forzare automaticamente il tenant ID=1, lascia l'utente scegliere
        if (!$tenant) {
            $tenants = Tenant::where('is_active', true)
                ->orderBy('name', 'asc')
                ->get();
            
            // Se non ci sono tenant, mostra comunque il form (userà tenant_id=1 come fallback nel login)
            if ($tenants->isEmpty()) {
                $tenant = null;
                $tenants = null;
            }
        }
        
        return view('auth.login', [
            'tenant' => $tenant,
            'tenants' => $tenants ?? null,
        ]);
    }
    
    /**
     * Gestisce il login
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'tenant_id' => ['nullable', 'integer'],
        ]);
        
        $tenantId = $request->input('tenant_id');
        
        // Se non c'è tenant_id, prova a risolverlo
        if (!$tenantId) {
            $tenantId = TenantResolver::resolve();
        }
        
        // Se ancora non c'è tenant_id, usa il tenant di default (Florence EGI, ID=1)
        if (!$tenantId) {
            $defaultTenant = Tenant::find(1);
            if ($defaultTenant && $defaultTenant->is_active) {
                $tenantId = 1;
            } else {
                // Se non esiste il tenant di default, cerca il primo tenant attivo
                $firstTenant = Tenant::where('is_active', true)->first();
                if ($firstTenant) {
                    $tenantId = $firstTenant->id;
                } else {
                    // Se non ci sono tenant, usa ID=1 come fallback (verrà creato se necessario)
                    $tenantId = 1;
                }
            }
        }
        
        // Verifica che il tenant esista e sia attivo
        // Se non esiste ma è ID=1 (default Florence EGI), permettere comunque il login
        $tenant = Tenant::where('id', $tenantId)
            ->where('is_active', true)
            ->first();
        
        // Se il tenant non esiste e NON è il default (ID=1), errore
        if (!$tenant && $tenantId != 1) {
            return back()
                ->withErrors(['tenant_id' => 'Il tenant selezionato non è valido o non è attivo.'])
                ->withInput($request->only('email'));
        }
        
        // Se tenant è null ma ID=1 (default Florence EGI), crea il tenant se non esiste
        if (!$tenant && $tenantId == 1) {
            $tenant = Tenant::firstOrCreate(
                ['id' => 1],
                [
                    'name' => 'Florence EGI',
                    'slug' => 'florence-egi',
                    'is_active' => true,
                    'settings' => json_encode([]),
                ]
            );
        }
        
        // Verifica che il tenant esista prima del login (se non è ID=1)
        if (!$tenant && $tenantId != 1) {
            return back()
                ->withErrors(['tenant_id' => 'Il tenant selezionato non è valido o non è attivo.'])
                ->withInput($request->only('email'));
        }
        
        // Tenta il login
        if (Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            $user = Auth::user();
            
            // Verifica che l'utente appartenga al tenant corretto
            // ECCEZIONE: superadmin può accedere a qualsiasi tenant
            $isSuperadmin = $user->hasRole('superadmin');
            
            if (!$isSuperadmin && $user->tenant_id && $user->tenant_id != $tenantId) {
                // Utente normale con tenant_id diverso: blocca accesso
                Auth::logout();
                return back()
                    ->withErrors(['email' => 'Questo utente non appartiene al tenant selezionato.'])
                    ->withInput($request->only('email'));
            }
            
            // Se l'utente non ha tenant_id e il tenant esiste, assegnagli quello selezionato
            // (ma NON sovrascrivere se è superadmin e ha già un tenant_id)
            if (!$user->tenant_id && $tenant) {
                $user->tenant_id = $tenantId;
                $user->save();
            }
            
            // Superadmin può accedere a qualsiasi tenant (non cambiamo il suo tenant_id)
            // ma impostiamo il tenant corrente nella sessione per questa sessione
            
            // Superadmin: salva tenant selezionato in sessione per accesso cross-tenant
            if ($isSuperadmin && $tenantId) {
                $request->session()->put('current_tenant_id', $tenantId);
            }
            
            // Rigenera la sessione per sicurezza
            $request->session()->regenerate();
            
            // Redirect alla chat NATAN
            return redirect()->intended(route('natan.chat'));
        }
        
        return back()
            ->withErrors(['email' => 'Le credenziali fornite non sono corrette.'])
            ->withInput($request->only('email', 'tenant_id'));
    }
    
    /**
     * Logout
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
}



