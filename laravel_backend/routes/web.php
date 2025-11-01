<?php

use App\Http\Controllers\NatanChatController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// NATAN Chat Interface
Route::get('/natan/chat', [NatanChatController::class, 'index'])
    ->middleware('auth')
    ->name('natan.chat');

// NATAN Routes (placeholder - da implementare)
Route::prefix('natan')->name('natan.')->group(function () {
    Route::get('/documents', function () {
        return view('natan.placeholder', ['title' => 'Documenti NATAN', 'feature' => 'Gestione documenti PA & Enterprises']);
    })->name('documents.index');
    
    Route::get('/scrapers', function () {
        return view('natan.placeholder', ['title' => 'Web Scrapers', 'feature' => 'Agente di scraping web']);
    })->name('scrapers.index');
    
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
