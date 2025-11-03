<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

/**
 * @package App\Http\Controllers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-02
 * @purpose Controller CRUD per gestione tenant (PA & Enterprises)
 * 
 * CONTESTO: /home/fabio/NATAN_LOC
 * PERCORSO FILE: app/Http/Controllers/TenantController.php
 * 
 * Gestisce operazioni CRUD complete per tenant:
 * - Lista tenant con filtro e ricerca
 * - Creazione nuovo tenant
 * - Modifica tenant esistente
 * - Visualizzazione dettagli tenant
 * - Eliminazione soft delete tenant
 */
class TenantController extends Controller
{
    /**
     * Lista tutti i tenant con ricerca e filtro
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // IMPORTANTE: Tenant non deve essere filtrato per tenant_id
        // L'admin deve vedere TUTTI i tenant del sistema
        $query = Tenant::query();
        
        // Ricerca per nome, slug, email, codice
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }
        
        // Filtro per tipo entità
        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->get('entity_type'));
        }
        
        // Filtro per stato attivo/inattivo
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        
        // Ordinamento (default: nome A-Z)
        $sortBy = $request->get('sort_by', 'name');
        $sortDir = $request->get('sort_dir', 'asc');
        
        // Validazione sort_by per sicurezza
        $allowedSorts = ['name', 'slug', 'entity_type', 'is_active', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'name';
        }
        $sortDir = strtolower($sortDir) === 'desc' ? 'desc' : 'asc';
        
        $query->orderBy($sortBy, $sortDir);
        
        // Paginazione
        $perPage = $request->get('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50, 100]) ? (int)$perPage : 15;
        $tenants = $query->paginate($perPage)->withQueryString();
        
        // Statistiche (sempre su TUTTI i tenant)
        $stats = [
            'total' => Tenant::count(),
            'active' => Tenant::where('is_active', true)->count(),
            'pa' => Tenant::where('entity_type', 'pa')->count(),
            'company' => Tenant::where('entity_type', 'company')->count(),
        ];
        
        return view('natan.tenants.index', [
            'tenants' => $tenants,
            'stats' => $stats,
            'filters' => $request->only(['search', 'entity_type', 'is_active', 'sort_by', 'sort_dir', 'per_page']),
        ]);
    }
    
    /**
     * Mostra form creazione nuovo tenant
     * 
     * @return View
     */
    public function create(): View
    {
        return view('natan.tenants.create');
    }
    
    /**
     * Salva nuovo tenant
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tenants,slug|regex:/^[a-z0-9-]+$/',
            'code' => 'nullable|string|max:100|unique:tenants,code',
            'entity_type' => 'required|in:pa,company,public_entity,other',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:1000',
            'vat_number' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'trial_ends_at' => 'nullable|date',
            'subscription_ends_at' => 'nullable|date',
            'notes' => 'nullable|string|max:5000',
        ]);
        
        if ($validator->fails()) {
            return redirect()
                ->route('tenants.create')
                ->withErrors($validator)
                ->withInput();
        }
        
        $tenant = Tenant::create($validator->validated());
        
        return redirect()
            ->route('tenants.show', $tenant)
            ->with('success', __('tenants.created_successfully'));
    }
    
    /**
     * Mostra dettagli tenant
     * 
     * @param Tenant $tenant
     * @return View
     */
    public function show(Tenant $tenant): View
    {
        // Carica statistiche correlate
        $tenant->loadCount([
            'users',
            'paActs',
            'conversations',
            'chatMessages',
            'userMemories',
        ]);
        
        return view('natan.tenants.show', [
            'tenant' => $tenant,
        ]);
    }
    
    /**
     * Mostra form modifica tenant
     * 
     * @param Tenant $tenant
     * @return View
     */
    public function edit(Tenant $tenant): View
    {
        return view('natan.tenants.edit', [
            'tenant' => $tenant,
        ]);
    }
    
    /**
     * Aggiorna tenant esistente
     * 
     * @param Request $request
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tenants,slug,' . $tenant->id . '|regex:/^[a-z0-9-]+$/',
            'code' => 'nullable|string|max:100|unique:tenants,code,' . $tenant->id,
            'entity_type' => 'required|in:pa,company,public_entity,other',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:1000',
            'vat_number' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'trial_ends_at' => 'nullable|date',
            'subscription_ends_at' => 'nullable|date',
            'notes' => 'nullable|string|max:5000',
        ]);
        
        if ($validator->fails()) {
            return redirect()
                ->route('tenants.edit', $tenant)
                ->withErrors($validator)
                ->withInput();
        }
        
        $tenant->update($validator->validated());
        
        return redirect()
            ->route('tenants.show', $tenant)
            ->with('success', __('tenants.updated_successfully'));
    }
    
    /**
     * Elimina tenant (soft delete)
     * 
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function destroy(Tenant $tenant): RedirectResponse
    {
        $tenant->delete();
        
        return redirect()
            ->route('tenants.index')
            ->with('success', __('tenants.deleted_successfully'));
    }
}
