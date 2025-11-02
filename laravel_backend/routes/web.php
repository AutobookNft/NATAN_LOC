<?php

use App\Http\Controllers\NatanChatController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// NATAN Chat Interface
// Note: Auth gestita tramite EGI, quindi non usiamo middleware 'auth' qui
Route::get('/natan/chat', [NatanChatController::class, 'index'])
    ->name('natan.chat');

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
        $tenant = \App\Models\PaEntity::find($tenantId);
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

// NATAN Routes (placeholder - da implementare)
Route::prefix('natan')->name('natan.')->group(function () {
    // NATAN Conversation API dentro il gruppo natan
    Route::post('/conversations/save', [\App\Http\Controllers\NatanConversationController::class, 'saveConversation'])
        ->name('api.conversations.save')
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
    Route::get('/conversations/{conversationId}', [\App\Http\Controllers\NatanConversationController::class, 'getConversation'])
        ->name('api.conversations.get')
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);

    Route::get('/documents', function () {
        return view('natan.placeholder', ['title' => 'Documenti NATAN', 'feature' => 'Gestione documenti PA & Enterprises']);
    })->name('documents.index');

    Route::get('/scrapers', [\App\Http\Controllers\NatanScrapersController::class, 'index'])->name('scrapers.index');
    Route::get('/scrapers/{scraperId}', [\App\Http\Controllers\NatanScrapersController::class, 'show'])->name('scrapers.show');
    Route::post('/scrapers/{scraperId}/run', [\App\Http\Controllers\NatanScrapersController::class, 'run'])->name('scrapers.run');
    Route::post('/scrapers/{scraperId}/preview', [\App\Http\Controllers\NatanScrapersController::class, 'preview'])->name('scrapers.preview');

    Route::get('/embeddings', function () {
        return view('natan.placeholder', ['title' => 'Embeddings', 'feature' => 'Gestione vector embeddings']);
    })->name('embeddings.index');

    Route::get('/ai-costs', function () {
        return view('natan.placeholder', ['title' => 'AI Costs', 'feature' => 'Monitoraggio costi AI']);
    })->name('ai-costs.dashboard');

    Route::get('/statistics', function () {
        return view('natan.placeholder', ['title' => 'Statistiche', 'feature' => 'Statistiche e analytics']);
    })->name('statistics.dashboard');

    Route::get('/batch', function () {
        return view('natan.placeholder', ['title' => 'Batch Processing', 'feature' => 'Elaborazione batch']);
    })->name('batch.index');
});
