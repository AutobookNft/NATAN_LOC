<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tenant;
use App\Helpers\RoleHelper;
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

        // Determina tenant corrente dinamicamente (NO hardcoded)
        $tenantId = null;

        if ($currentUser->hasRole('superadmin')) {
            // Superadmin: priorità 1) sessione, 2) tenant_id utente
            if (request()->session()->has('current_tenant_id')) {
                $tenantId = (int) request()->session()->get('current_tenant_id');
            } elseif ($currentUser->tenant_id) {
                $tenantId = $currentUser->tenant_id;
            }
        } else {
            // Utente normale: usa sempre il proprio tenant_id
            $tenantId = $currentUser->tenant_id;
        }

        $tenant = $tenantId ? Tenant::find($tenantId) : null;

        // Se superadmin, carica tutti i tenant disponibili per la selezione
        $availableTenants = collect([]);
        if ($currentUser->hasRole('superadmin')) {
            $availableTenants = Tenant::where('is_active', true)
                ->orderBy('name', 'asc')
                ->get(['id', 'name', 'slug']);
        }

        // Ruoli disponibili filtrati in base al ruolo dell'utente corrente
        // superadmin: vede tutti i ruoli
        // admin: vede solo ruoli PA/NATAN (non superadmin)
        // pa_entity_admin: vede solo ruoli PA base (non superadmin o admin)
        $roles = RoleHelper::getAssignableRoles();

        return view('natan.users.create', [
            'tenant' => $tenant,
            'roles' => $roles,
            'availableTenants' => $availableTenants,
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
            'tenant_id' => ['nullable', 'integer', 'exists:tenants,id'], // Superadmin può specificare tenant
            'tenant_slug' => ['nullable', 'string', 'exists:tenants,slug'], // Alternative: slug
            'tenant_name' => ['nullable', 'string'], // Alternative: name
        ]);

        // Filtra ruoli per rimuovere quelli non assegnabili dall'utente corrente
        if (!empty($validated['roles'])) {
            $validated['roles'] = RoleHelper::filterAssignableRoles($validated['roles']);

            // Se dopo il filtraggio non ci sono ruoli validi, abort
            if (empty($validated['roles'])) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['roles' => __('users.invalid_roles_selected')]);
            }
        }

        // Determina tenant_id dinamicamente (NO hardcoded values)
        $targetTenantId = null;

        if ($currentUser->hasRole('superadmin')) {
            // Superadmin: priorità 1) tenant_id dal form, 2) tenant_slug, 3) tenant_name, 4) sessione, 5) tenant_id utente
            if (!empty($validated['tenant_id'])) {
                $targetTenantId = (int) $validated['tenant_id'];
            } elseif (!empty($validated['tenant_slug'])) {
                $tenant = Tenant::where('slug', $validated['tenant_slug'])->first();
                $targetTenantId = $tenant ? $tenant->id : null;
            } elseif (!empty($validated['tenant_name'])) {
                $tenant = Tenant::where('name', $validated['tenant_name'])->first();
                $targetTenantId = $tenant ? $tenant->id : null;
            } elseif (request()->session()->has('current_tenant_id')) {
                $targetTenantId = (int) request()->session()->get('current_tenant_id');
            } elseif ($currentUser->tenant_id) {
                $targetTenantId = $currentUser->tenant_id;
            }
        } else {
            // Utente normale: usa sempre il proprio tenant_id
            $targetTenantId = $currentUser->tenant_id;
        }

        // Verifica che il tenant esista e sia valido
        if (!$targetTenantId) {
            abort(400, __('users.tenant_required'));
        }

        $targetTenant = Tenant::find($targetTenantId);
        if (!$targetTenant) {
            abort(404, __('users.tenant_not_found'));
        }

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

        // Ruoli disponibili filtrati in base al ruolo dell'utente corrente
        $roles = RoleHelper::getAssignableRoles();
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

        // Superadmin può modificare qualsiasi utente, altri solo del proprio tenant
        if (!$currentUser->hasRole('superadmin')) {
            $currentTenantId = $currentUser->tenant_id;
            if (!$user->tenant_id || $user->tenant_id !== $currentTenantId) {
                abort(404);
            }
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,name'],
        ]);

        // Filtra ruoli per rimuovere quelli non assegnabili dall'utente corrente
        if (!empty($validated['roles'])) {
            $validated['roles'] = RoleHelper::filterAssignableRoles($validated['roles']);

            // Se dopo il filtraggio non ci sono ruoli validi, abort
            if (empty($validated['roles'])) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['roles' => __('users.invalid_roles_selected')]);
            }
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // Sincronizza ruoli (solo quelli assegnabili)
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