<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/**
 * @package App\Http\Controllers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-03
 * @purpose Controller per gestione utenti del tenant corrente (Admin)
 *
 * CONTESTO: /home/fabio/NATAN_LOC
 * PERCORSO FILE: app/Http/Controllers/UserController.php
 *
 * Gestisce CRUD utenti del tenant corrente:
 * - Solo admin/superadmin del tenant può creare/modificare utenti
 * - Utenti vengono creati assegnati al tenant corrente
 * - Gestione ruoli tramite Spatie Permission
 */
class UserController extends Controller
{
    /**
     * Lista utenti del tenant corrente
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $currentUser = Auth::user();
        
        // Superadmin non ha bisogno di tenant_id
        if (!$currentUser->hasRole('superadmin') && (!$currentUser || !$currentUser->tenant_id)) {
            abort(403, __('users.no_tenant'));
        }
        
        // Verifica permessi: solo admin, superadmin o pa_entity_admin
        if (!$currentUser->hasAnyRole(['admin', 'superadmin', 'pa_entity_admin'])) {
            abort(403, __('users.insufficient_permissions'));
        }
        
        // Superadmin può non avere tenant_id, usa quello dalla sessione se disponibile
        $tenantId = $currentUser->hasRole('superadmin') 
            ? (request()->session()->get('current_tenant_id') ?? $currentUser->tenant_id)
            : $currentUser->tenant_id;
        
        $tenant = $tenantId ? Tenant::find($tenantId) : null;
        
        // Query utenti: superadmin vede tutti, altri vedono solo il proprio tenant
        $query = User::query();
        if (!$currentUser->hasRole('superadmin')) {
            $query->where('tenant_id', $currentUser->tenant_id);
        }
        
        // Ricerca
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Ordinamento
        $sortBy = $request->get('sort_by', 'name');
        $sortDir = $request->get('sort_dir', 'asc');
        
        $allowedSorts = ['name', 'email', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'name';
        }
        $sortDir = strtolower($sortDir) === 'desc' ? 'desc' : 'asc';
        
        $query->orderBy($sortBy, $sortDir);
        
        // Paginazione
        $perPage = $request->get('per_page', 15);
        $perPage = min(max(5, (int)$perPage), 100); // Limit between 5 and 100
        
        $users = $query->paginate($perPage)->withQueryString();
        
        // Aggiungi ruoli a ogni utente
        foreach ($users as $user) {
            $user->load('roles');
        }
        
        // Statistiche: superadmin vede tutte, altri solo del proprio tenant
        $statsQuery = User::query();
        if (!$currentUser->hasRole('superadmin')) {
            $statsQuery->where('tenant_id', $currentUser->tenant_id);
        }
        
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'admins' => (clone $statsQuery)
                ->role(['admin', 'superadmin', 'pa_entity_admin'])
                ->count(),
            'active' => (clone $statsQuery)
                ->whereNotNull('email_verified_at')
                ->count(),
        ];
        
        return view('natan.users.index', [
            'users' => $users,
            'tenant' => $tenant,
            'stats' => $stats,
        ]);
    }
    
    /**
     * Mostra form creazione nuovo utente
     *
     * @return View
     */
    public function create(): View
    {
        $currentUser = Auth::user();
        
        // Superadmin non ha bisogno di tenant_id
        if (!$currentUser->hasRole('superadmin') && (!$currentUser || !$currentUser->tenant_id)) {
            abort(403, __('users.no_tenant'));
        }
        
        if (!$currentUser->hasAnyRole(['admin', 'superadmin', 'pa_entity_admin'])) {
            abort(403, __('users.insufficient_permissions'));
        }
        
        // Superadmin usa tenant dalla sessione se disponibile
        $tenantId = $currentUser->hasRole('superadmin') 
            ? (request()->session()->get('current_tenant_id') ?? $currentUser->tenant_id)
            : $currentUser->tenant_id;
        
        $tenant = $tenantId ? Tenant::find($tenantId) : null;
        
        // Ruoli disponibili (filtrati per tenant se necessario)
        $roles = Role::orderBy('name', 'asc')->get();
        
        return view('natan.users.create', [
            'tenant' => $tenant,
            'roles' => $roles,
        ]);
    }
    
    /**
     * Salva nuovo utente
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $currentUser = Auth::user();
        
        // Superadmin non ha bisogno di tenant_id
        if (!$currentUser->hasRole('superadmin') && (!$currentUser || !$currentUser->tenant_id)) {
            abort(403, __('users.no_tenant'));
        }
        
        if (!$currentUser->hasAnyRole(['admin', 'superadmin', 'pa_entity_admin'])) {
            abort(403, __('users.insufficient_permissions'));
        }
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,name'],
        ]);
        
        // Determina tenant_id: superadmin usa tenant dalla sessione o quello dell'utente
        $targetTenantId = $currentUser->hasRole('superadmin')
            ? (request()->session()->get('current_tenant_id') ?? $currentUser->tenant_id)
            : $currentUser->tenant_id;
        
        // Crea utente assegnato al tenant corrente
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'tenant_id' => $targetTenantId,
            'email_verified_at' => now(), // Auto-verifica email per admin
        ]);
        
        // Assegna ruoli
        if (!empty($validated['roles'])) {
            $user->assignRole($validated['roles']);
        }
        
        return redirect()
            ->route('users.index')
            ->with('success', __('users.created_successfully', ['name' => $user->name]));
    }
    
    /**
     * Mostra dettagli utente
     *
     * @param User $user
     * @return View
     */
    public function show(User $user): View
    {
        $currentUser = Auth::user();
        
        // Superadmin può vedere tutti gli utenti, altri solo del proprio tenant
        if (!$currentUser->hasRole('superadmin')) {
            $currentTenantId = $currentUser->tenant_id;
            if (!$user->tenant_id || $user->tenant_id !== $currentTenantId) {
                abort(404);
            }
        }
        
        $user->load('roles', 'tenant');
        
        // Superadmin può vedere qualsiasi tenant
        $tenantId = $currentUser->hasRole('superadmin') 
            ? (request()->session()->get('current_tenant_id') ?? $currentUser->tenant_id)
            : $currentUser->tenant_id;
        
        $tenant = $tenantId ? Tenant::find($tenantId) : null;
        
        return view('natan.users.show', [
            'user' => $user,
            'tenant' => $tenant,
        ]);
    }
    
    /**
     * Mostra form modifica utente
     *
     * @param User $user
     * @return View
     */
    public function edit(User $user): View
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->hasAnyRole(['admin', 'superadmin', 'pa_entity_admin'])) {
            abort(403, __('users.insufficient_permissions'));
        }
        
        // Superadmin può modificare qualsiasi utente, altri solo del proprio tenant
        if (!$currentUser->hasRole('superadmin')) {
            $currentTenantId = $currentUser->tenant_id;
            if (!$user->tenant_id || $user->tenant_id !== $currentTenantId) {
                abort(404);
            }
        }
        
        // Superadmin usa tenant dalla sessione se disponibile
        $tenantId = $currentUser->hasRole('superadmin') 
            ? (request()->session()->get('current_tenant_id') ?? $currentUser->tenant_id)
            : $currentUser->tenant_id;
        
        $tenant = $tenantId ? Tenant::find($tenantId) : null;
        $roles = Role::orderBy('name', 'asc')->get();
        $user->load('roles');
        
        return view('natan.users.edit', [
            'user' => $user,
            'tenant' => $tenant,
            'roles' => $roles,
        ]);
    }
    
    /**
     * Aggiorna utente
     *
     * @param Request $request
     * @param User $user
     * @return RedirectResponse
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->hasAnyRole(['admin', 'superadmin', 'pa_entity_admin'])) {
            abort(403, __('users.insufficient_permissions'));
        }
        
        // Verifica che l'utente appartenga al tenant corrente
        if (!$user->tenant_id || $user->tenant_id !== $currentUser->tenant_id) {
            abort(404);
        }
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,name'],
        ]);
        
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        
        $user->save();
        
        // Sincronizza ruoli
        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }
        
        return redirect()
            ->route('users.index')
            ->with('success', __('users.updated_successfully', ['name' => $user->name]));
    }
    
    /**
     * Elimina utente
     *
     * @param User $user
     * @return RedirectResponse
     */
    public function destroy(User $user): RedirectResponse
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->hasAnyRole(['admin', 'superadmin', 'pa_entity_admin'])) {
            abort(403, __('users.insufficient_permissions'));
        }
        
        // Verifica che l'utente appartenga al tenant corrente
        if (!$user->tenant_id || $user->tenant_id !== $currentUser->tenant_id) {
            abort(404);
        }
        
        // Non permettere eliminazione di se stessi
        if ($user->id === $currentUser->id) {
            return redirect()
                ->route('users.index')
                ->with('error', __('users.cannot_delete_self'));
        }
        
        $userName = $user->name;
        $user->delete();
        
        return redirect()
            ->route('users.index')
            ->with('success', __('users.deleted_successfully', ['name' => $userName]));
    }
}

