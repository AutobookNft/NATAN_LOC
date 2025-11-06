<?php

use App\Http\Controllers\NatanChatController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Se l'utente è autenticato, redirect alla chat
    if (auth()->check()) {
        return redirect()->route('natan.chat');
    }
    // Altrimenti redirect al login
    return redirect()->route('login');
});

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Auth\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Auth\AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [\App\Http\Controllers\Auth\AuthController::class, 'logout'])->name('logout');

    // Route di test per verificare autenticazione
    Route::get('/test-auth', function () {
        $user = auth()->user();

        $result = [
            'auth_check' => auth()->check(),
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_email' => $user?->email,
            'user_tenant_id' => $user?->tenant_id,
            'tenant' => null,
            'tenant_resolved' => null,
            'current_tenant_id' => app()->bound('currentTenantId') ? app('currentTenantId') : null,
        ];

        if ($user && $user->tenant_id) {
            $tenant = \App\Models\Tenant::find($user->tenant_id);
            if ($tenant) {
                $result['tenant'] = [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug,
                ];
            }
        }

        // Prova a risolvere il tenant
        $resolvedTenantId = \App\Resolvers\TenantResolver::resolve();
        if ($resolvedTenantId) {
            $resolvedTenant = \App\Models\Tenant::find($resolvedTenantId);
            if ($resolvedTenant) {
                $result['tenant_resolved'] = [
                    'id' => $resolvedTenant->id,
                    'name' => $resolvedTenant->name,
                    'slug' => $resolvedTenant->slug,
                ];
            }
        }

        return response()->json($result, 200, [], JSON_PRETTY_PRINT);
    })->name('test.auth');
});

// NATAN Chat Interface (accessibile a ruoli PA/autorizzati)
Route::middleware(['auth', 'natan.access'])->group(function () {
    Route::get('/natan/chat', [NatanChatController::class, 'index'])
        ->name('natan.chat');
});

// API Session endpoint per ottenere user e tenant info
Route::get('/api/session', function () {
    $user = auth()->user();

    if (!$user) {
        return response()->json([
            'user' => null,
            'tenant' => null,
        ], 200); // Non errore, semplicemente non autenticato
    }

    // Ottieni tenant dall'utente o dal resolver
    $tenantId = $user->tenant_id ?? app('currentTenantId');
    $tenant = null;

    if ($tenantId) {
        $tenant = \App\Models\Tenant::find($tenantId);
    }

    return response()->json([
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'tenant_id' => $user->tenant_id,
        ],
        'tenant' => $tenant ? [
            'id' => $tenant->id,
            'name' => $tenant->name,
            'slug' => $tenant->slug,
            'domain' => $tenant->slug . '.natan.loc', // Costruito da slug
        ] : null,
    ]);
})->name('api.session');

// NATAN Routes (accessibili a ruoli PA/autorizzati)
// I controlli di permesso specifici sono gestiti nei controller e nei menu items
Route::prefix('natan')->name('natan.')->middleware(['auth', 'natan.access'])->group(function () {
    // NATAN Conversation API dentro il gruppo natan
    Route::post('/conversations/save', [\App\Http\Controllers\NatanConversationController::class, 'saveConversation'])
        ->name('api.conversations.save')
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
    Route::get('/conversations/{conversationId}', [\App\Http\Controllers\NatanConversationController::class, 'getConversation'])
        ->name('api.conversations.get')
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);

    // Documents routes
    Route::get('/documents', [\App\Http\Controllers\DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/{document}', [\App\Http\Controllers\DocumentController::class, 'show'])->name('documents.show');
    
    // MongoDB Documents routes (dentro prefix 'natan', quindi solo /documents/view/{documentId})
    Route::get('/documents/view/{documentId}', [\App\Http\Controllers\MongoDocumentController::class, 'view'])->name('documents.view');

    // Scrapers routes
    Route::get('/scrapers', [\App\Http\Controllers\NatanScrapersController::class, 'index'])->name('scrapers.index');
});

// PDF proxy route (fuori dal gruppo auth per permettere accesso con token)
// Può essere accessibile anche senza autenticazione se ha token valido
Route::get('/natan/documents/pdf/{documentId}', [\App\Http\Controllers\MongoDocumentController::class, 'pdfProxy'])
    ->name('natan.documents.pdf')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);

// Riapri il gruppo natan per le altre route
Route::prefix('natan')->name('natan.')->middleware(['auth', 'natan.access'])->group(function () {
    Route::get('/scrapers/{scraperId}', [\App\Http\Controllers\NatanScrapersController::class, 'show'])->name('scrapers.show');
    Route::post('/scrapers/{scraperId}/run', [\App\Http\Controllers\NatanScrapersController::class, 'run'])->name('scrapers.run');
    Route::get('/scrapers/{scraperId}/progress', [\App\Http\Controllers\NatanScrapersController::class, 'progress'])->name('scrapers.progress');
    Route::post('/scrapers/{scraperId}/preview', [\App\Http\Controllers\NatanScrapersController::class, 'preview'])->name('scrapers.preview');

    // Batch routes
    Route::get('/batch', [\App\Http\Controllers\BatchController::class, 'index'])->name('batch.index');
    Route::get('/batch/create', [\App\Http\Controllers\BatchController::class, 'create'])->name('batch.create');
    Route::get('/batch/{batchId}', [\App\Http\Controllers\BatchController::class, 'show'])->name('batch.show');

    // Embeddings routes
    Route::get('/embeddings', [\App\Http\Controllers\EmbeddingController::class, 'index'])->name('embeddings.index');
    Route::get('/embeddings/{embeddingId}', [\App\Http\Controllers\EmbeddingController::class, 'show'])->name('embeddings.show');

    // Statistics routes
    Route::get('/statistics/dashboard', [\App\Http\Controllers\StatisticsController::class, 'dashboard'])->name('statistics.dashboard');

    // AI Costs routes
    Route::get('/ai-costs/dashboard', [\App\Http\Controllers\AiCostsController::class, 'dashboard'])->name('ai-costs.dashboard');
});

// Tenant CRUD Routes (richiede superadmin - gestione multi-tenant)
Route::middleware(['auth', 'superadmin'])->prefix('natan/tenants')->group(function () {
    Route::get('/', [\App\Http\Controllers\TenantController::class, 'index'])->name('tenants.index');
    Route::get('/create', [\App\Http\Controllers\TenantController::class, 'create'])->name('tenants.create');
    Route::post('/', [\App\Http\Controllers\TenantController::class, 'store'])->name('tenants.store');
    Route::get('/{tenant}', [\App\Http\Controllers\TenantController::class, 'show'])->name('tenants.show');
    Route::get('/{tenant}/edit', [\App\Http\Controllers\TenantController::class, 'edit'])->name('tenants.edit');
    Route::put('/{tenant}', [\App\Http\Controllers\TenantController::class, 'update'])->name('tenants.update');
    Route::patch('/{tenant}', [\App\Http\Controllers\TenantController::class, 'update']);
    Route::delete('/{tenant}', [\App\Http\Controllers\TenantController::class, 'destroy'])->name('tenants.destroy');
});

// Admin Config Routes (tenant-specific configurations)
Route::middleware(['auth'])->prefix('admin/config')->group(function () {
    Route::get('/', [\App\Http\Controllers\AdminConfigController::class, 'index'])->name('admin.config.index');
    Route::put('/', [\App\Http\Controllers\AdminConfigController::class, 'update'])->name('admin.config.update');
    Route::patch('/', [\App\Http\Controllers\AdminConfigController::class, 'update']);
});

// Users Management Routes (tenant-specific users)
Route::middleware(['auth'])->prefix('users')->group(function () {
    Route::get('/', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::get('/create', [\App\Http\Controllers\UserController::class, 'create'])->name('users.create');
    Route::post('/', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
    Route::get('/{user}', [\App\Http\Controllers\UserController::class, 'show'])->name('users.show');
    Route::get('/{user}/edit', [\App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');
    Route::put('/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
    Route::patch('/{user}', [\App\Http\Controllers\UserController::class, 'update']);
    Route::delete('/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');
});
