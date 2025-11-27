<?php

namespace App\Http\Controllers;

use App\Models\NatanUserMemory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * @Oracode Controller: NATAN User Memory Management
 * 🎯 Purpose: Gestire memorie personalizzate degli utenti
 * 🛡️ Privacy: Ogni utente accede solo alle proprie memorie
 * 🧱 Core Logic: Salva e recupera memorie per RAG retrieval
 * 
 * @package App\Http\Controllers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC - Memory System)
 * @date 2025-11-25
 * @purpose Gestire sistema memoria utente per contesto personalizzato
 */
class NatanMemoryController extends Controller
{
    /**
     * Store a new memory
     * 
     * POST /natan/memories
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'content' => 'required|string|max:10000',
                'type' => 'nullable|string|in:general,preference,fact,instruction,context',
                'keywords' => 'nullable|string|max:500',
            ]);

            $memory = NatanUserMemory::create([
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id,
                'memory_content' => $validated['content'],
                'memory_type' => $validated['type'] ?? 'general',
                'keywords' => $validated['keywords'] ?? null,
                'is_active' => true,
            ]);

            Log::info('[NatanMemory] Memory created', [
                'user_id' => $user->id,
                'memory_id' => $memory->id,
                'type' => $memory->memory_type,
                'content_length' => strlen($memory->memory_content),
            ]);

            return response()->json([
                'success' => true,
                'memory' => $memory,
                'message' => 'Memoria salvata con successo',
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('[NatanMemory] Store error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il salvataggio della memoria',
            ], 500);
        }
    }

    /**
     * List user memories
     * 
     * GET /natan/memories
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $query = NatanUserMemory::forUser($user->id)
                ->active()
                ->orderBy('last_used_at', 'desc')
                ->orderBy('created_at', 'desc');

            // Filter by type if provided
            if ($request->has('type')) {
                $query->ofType($request->input('type'));
            }

            $memories = $query->paginate($request->input('per_page', 20));

            return response()->json([
                'success' => true,
                'memories' => $memories,
            ]);
        } catch (\Exception $e) {
            Log::error('[NatanMemory] Index error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il recupero delle memorie',
            ], 500);
        }
    }

    /**
     * Search memories relevant to a query
     * 
     * POST /natan/memories/search
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'query' => 'required|string|max:1000',
                'limit' => 'nullable|integer|min:1|max:50',
            ]);

            $memories = NatanUserMemory::searchRelevant(
                $user->id,
                $validated['query'],
                $validated['limit'] ?? 5
            );

            // Mark memories as used
            foreach ($memories as $memory) {
                $memory->markAsUsed();
            }

            Log::info('[NatanMemory] Search executed', [
                'user_id' => $user->id,
                'query' => $validated['query'],
                'results_count' => $memories->count(),
            ]);

            return response()->json([
                'success' => true,
                'memories' => $memories,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('[NatanMemory] Search error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la ricerca delle memorie',
            ], 500);
        }
    }

    /**
     * Update a memory
     * 
     * PATCH /natan/memories/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $user = Auth::user();

            $memory = NatanUserMemory::forUser($user->id)->findOrFail($id);

            $validated = $request->validate([
                'content' => 'sometimes|required|string|max:10000',
                'type' => 'nullable|string|in:general,preference,fact,instruction,context',
                'keywords' => 'nullable|string|max:500',
                'is_active' => 'nullable|boolean',
            ]);

            $memory->update($validated);

            Log::info('[NatanMemory] Memory updated', [
                'user_id' => $user->id,
                'memory_id' => $memory->id,
            ]);

            return response()->json([
                'success' => true,
                'memory' => $memory->fresh(),
                'message' => 'Memoria aggiornata con successo',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Memoria non trovata',
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('[NatanMemory] Update error', [
                'user_id' => Auth::id(),
                'memory_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'aggiornamento della memoria',
            ], 500);
        }
    }

    /**
     * Delete a memory
     * 
     * DELETE /natan/memories/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $user = Auth::user();

            $memory = NatanUserMemory::forUser($user->id)->findOrFail($id);

            $memory->delete();

            Log::info('[NatanMemory] Memory deleted', [
                'user_id' => $user->id,
                'memory_id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Memoria eliminata con successo',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Memoria non trovata',
            ], 404);
        } catch (\Exception $e) {
            Log::error('[NatanMemory] Delete error', [
                'user_id' => Auth::id(),
                'memory_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'eliminazione della memoria',
            ], 500);
        }
    }
}
