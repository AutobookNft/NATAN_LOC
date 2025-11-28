<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\NatanConversationController;
use App\Http\Controllers\NatanUseProxyController;
use App\Http\Controllers\NatanDiagnosticController;
use App\Http\Controllers\NatanProjectController;
use App\Http\Controllers\NatanDocumentController;

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

// NOTE: /api/session route is defined in web.php as /api/session (not in api.php)
// This avoids the api/ prefix duplication and name conflict

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
    // Conversations
    Route::post('/conversations/save', [NatanConversationController::class, 'saveConversation'])
        ->name('conversations.save');
    Route::get('/conversations/{conversationId}', [NatanConversationController::class, 'getConversation'])
        ->name('conversations.get');
});

// USE Pipeline Proxy Routes (proxy to Python FastAPI)
// Use web session auth (for browser-based requests)
Route::middleware(['web', 'auth:web'])->prefix('api/v1')->name('api.v1.')->group(function () {
    Route::post('/use/query', [NatanUseProxyController::class, 'proxyUseQuery'])
        ->name('use.query');

    Route::post('/embed', [NatanUseProxyController::class, 'proxyEmbedding'])
        ->name('embed');
    
    // RAG-Fortress Chat endpoint (proxy to Python FastAPI /chat)
    Route::match(['post', 'options'], '/chat', [NatanUseProxyController::class, 'proxyChat'])
        ->name('chat');
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
