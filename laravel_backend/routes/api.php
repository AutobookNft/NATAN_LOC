<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\NatanConversationController;
use App\Http\Controllers\NatanUseProxyController;
use App\Http\Controllers\NatanDiagnosticController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Context switching endpoint (requires authentication)
Route::middleware(['auth:web'])->post('/context/switch', [ApiController::class, 'switchContext'])
    ->name('api.context.switch');

// API Session endpoint (no auth required for session check)
Route::get('/session', function () {
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'user' => null,
            'tenant' => null,
        ], 200);
    }

    // Get tenant from user or resolver
    $tenant = null;
    if ($user->tenant_id) {
        $tenant = \App\Models\Tenant::find($user->tenant_id);
    }

    return response()->json([
        'user' => $user ? [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'tenant_id' => $user->tenant_id,
        ] : null,
        'tenant' => $tenant ? [
            'id' => $tenant->id,
            'name' => $tenant->name,
            'slug' => $tenant->slug,
            'domain' => $tenant->slug . '.natan.loc',
        ] : null,
    ]);
})->name('api.session');

// NATAN API Routes (require authentication)
Route::middleware(['auth:sanctum'])->prefix('natan')->name('api.natan.')->group(function () {
    // Conversation API
    Route::post('/conversations/save', [NatanConversationController::class, 'saveConversation'])
        ->name('conversations.save');

    Route::get('/conversations/{conversationId}', [NatanConversationController::class, 'getConversation'])
        ->name('conversations.get');
});

// Fallback: Also support web session auth (for Blade-based frontend)
Route::middleware(['auth:web'])->prefix('natan')->name('api.natan.web.')->group(function () {
    Route::post('/conversations/save', [NatanConversationController::class, 'saveConversation'])
        ->name('conversations.save');

    Route::get('/conversations/{conversationId}', [NatanConversationController::class, 'getConversation'])
        ->name('conversations.get');
});

// USE Pipeline Proxy Routes (proxy to Python FastAPI)
Route::middleware(['auth:sanctum'])->prefix('api/v1')->name('api.v1.')->group(function () {
    Route::post('/use/query', [NatanUseProxyController::class, 'proxyUseQuery'])
        ->name('use.query');

    Route::post('/embed', [NatanUseProxyController::class, 'proxyEmbedding'])
        ->name('embed');
});

// Fallback: Also support web session auth for USE proxy
Route::middleware(['auth:web'])->prefix('api/v1')->name('api.v1.web.')->group(function () {
    Route::post('/use/query', [NatanUseProxyController::class, 'proxyUseQuery'])
        ->name('use.query');

    Route::post('/embed', [NatanUseProxyController::class, 'proxyEmbedding'])
        ->name('embed');
});

// Diagnostic Routes (no auth required for debugging)
Route::prefix('diagnostic')->name('api.diagnostic.')->group(function () {
    Route::post('/retrieval', [NatanDiagnosticController::class, 'retrieval'])
        ->name('retrieval');
    Route::match(['GET', 'POST'], '/use-query', [NatanDiagnosticController::class, 'useQuery'])
        ->name('use');
    
    Route::get('/mongodb/{tenantId}', [NatanDiagnosticController::class, 'mongodb'])
        ->name('mongodb');
});

// Backward compatibility with /api/v1/diagnostic (optional authentication handled inside controller)
Route::prefix('api/v1/diagnostic')->name('api.v1.diagnostic.')->group(function () {
    Route::post('/retrieval', [NatanDiagnosticController::class, 'retrieval'])
        ->name('retrieval');
    Route::match(['GET', 'POST'], '/use-query', [NatanDiagnosticController::class, 'useQuery'])
        ->name('use');
    
    Route::get('/mongodb/{tenantId}', [NatanDiagnosticController::class, 'mongodb'])
        ->name('mongodb');
});
